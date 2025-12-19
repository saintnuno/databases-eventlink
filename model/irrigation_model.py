from __future__ import annotations
import os
import json
import numpy as np
import pandas as pd
import requests
from joblib import dump, load
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report

DATA_CSV = "irrigation_log.csv"
MODEL_PATH = "rf_irrigation.joblib"

DEFAULT_LAT = 53.07516
DEFAULT_LON = 8.80777

def fetch_open_meteo_hourly(lat: float, lon: float) -> pd.DataFrame:
    url = "https://api.open-meteo.com/v1/forecast"
    params = {
        "latitude": lat,
        "longitude": lon,
        "hourly": "temperature_2m,relative_humidity_2m,precipitation,precipitation_probability,wind_speed_10m",
        "forecast_days": 2,
        "timezone": "auto",
    }
    r = requests.get(url, params=params, timeout=20)
    r.raise_for_status()
    p = r.json()["hourly"]
    df = pd.DataFrame(
        {
            "time": pd.to_datetime(p["time"]),
            "temp_c": p["temperature_2m"],
            "rh_pct": p["relative_humidity_2m"],
            "precip_mm": p["precipitation"],
            "precip_prob": np.array(p.get("precipitation_probability", [np.nan] * len(p["time"]))) / 100.0,
            "wind_kph": p["wind_speed_10m"],
        }
    ).set_index("time").sort_index()
    return df

def weather_features_next_hours(df_hourly: pd.DataFrame, now_ts: pd.Timestamp, hours: int) -> dict:
    window = df_hourly.loc[now_ts : now_ts + pd.Timedelta(hours=hours)]
    if window.empty:
        window = df_hourly.iloc[: hours + 1].copy()
    return {
        "fc_temp_mean": float(np.nanmean(window["temp_c"])),
        "fc_rh_mean": float(np.nanmean(window["rh_pct"])),
        "fc_wind_mean": float(np.nanmean(window["wind_kph"])),
        "fc_precip_sum": float(np.nansum(window["precip_mm"])),
        "fc_precip_prob_max": float(np.nanmax(window["precip_prob"])),
        "fc_precip_prob_mean": float(np.nanmean(window["precip_prob"])),
    }

def append_event(timestamp: pd.Timestamp, manual_moisture_pct: float, lat: float, lon: float, feats: dict) -> None:
    row = {
        "timestamp": str(timestamp),
        "manual_moisture_pct": float(manual_moisture_pct),
        "lat": float(lat),
        "lon": float(lon),
        **feats,
    }
    df = pd.DataFrame([row])
    if not os.path.exists(DATA_CSV):
        df.to_csv(DATA_CSV, index=False)
    else:
        df.to_csv(DATA_CSV, mode="a", header=False, index=False)

FEATURE_COLS = [
    "manual_moisture_pct",
    "fc_temp_mean",
    "fc_rh_mean",
    "fc_wind_mean",
    "fc_precip_sum",
    "fc_precip_prob_max",
    "fc_precip_prob_mean",
    "hour",
    "dayofweek",
]

def build_training_table(target_min_moisture: float) -> pd.DataFrame:
    df = pd.read_csv(DATA_CSV)
    df["timestamp"] = pd.to_datetime(df["timestamp"], utc=True)
    df = df.sort_values("timestamp").reset_index(drop=True)
    df["next_manual_moisture_pct"] = df["manual_moisture_pct"].shift(-1)
    df = df.dropna(subset=["next_manual_moisture_pct"]).copy()
    df["y_water_now"] = (df["next_manual_moisture_pct"] < target_min_moisture).astype(int)
    df["hour"] = df["timestamp"].dt.hour
    df["dayofweek"] = df["timestamp"].dt.dayofweek
    return df

def train_model(target_min_moisture: float) -> None:
    if not os.path.exists(DATA_CSV):
        raise FileNotFoundError(f"{DATA_CSV} not found. Log data first.")
    df = build_training_table(target_min_moisture)
    if len(df) < 30:
        raise ValueError(f"Not enough samples ({len(df)}). Aim for 30–100+ logs.")
    X = df[FEATURE_COLS]
    y = df["y_water_now"]
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.25, random_state=42, stratify=y
    )
    model = RandomForestClassifier(
        n_estimators=300, random_state=42, class_weight="balanced", min_samples_leaf=2
    )
    model.fit(X_train, y_train)
    pred = model.predict(X_test)
    print(classification_report(y_test, pred))
    importances = pd.Series(model.feature_importances_, index=FEATURE_COLS).sort_values(ascending=False)
    print(importances.to_string())
    dump(model, MODEL_PATH)
    print(f"Saved: {MODEL_PATH}")

def recommend(
    manual_moisture_pct: float,
    horizon_hours: int,
    target_min_moisture: float,
    rain_skip_mm: float,
    rain_skip_prob: float,
    override_confidence: float,
    lat: float = DEFAULT_LAT,
    lon: float = DEFAULT_LON,
) -> dict:
    if not os.path.exists(MODEL_PATH):
        raise FileNotFoundError(f"{MODEL_PATH} not found. Train the model first.")
    model = load(MODEL_PATH)
    hourly = fetch_open_meteo_hourly(lat, lon)
    now_local = pd.Timestamp.now(tz=hourly.index.tz)
    feats = weather_features_next_hours(hourly, now_local, horizon_hours)
    x = {
        "manual_moisture_pct": float(manual_moisture_pct),
        **feats,
        "hour": int(now_local.hour),
        "dayofweek": int(now_local.dayofweek),
    }
    X = pd.DataFrame([x])[FEATURE_COLS]
    proba = float(model.predict_proba(X)[0, 1])
    action = "WATER_NOW" if proba >= 0.5 else "WAIT"
    rain_soon = (x["fc_precip_sum"] >= rain_skip_mm) or (x["fc_precip_prob_max"] >= rain_skip_prob)
    rationale = [f"RF p(WATER_NOW)={proba:.2f} (horizon={horizon_hours}h, target_min={target_min_moisture}%)"]
    if rain_soon and proba < override_confidence:
        action = "WAIT"
        rationale.append(
            f"Override WAIT (rain soon): precip_sum={x['fc_precip_sum']:.1f}mm, precip_prob_max={x['fc_precip_prob_max']:.2f}"
        )
    return {"timestamp": str(now_local), "action": action, "prob_water_now": proba, "inputs": x, "rationale": rationale}

def prompt_float(name: str, default: float | None = None) -> float:
    while True:
        s = input(f"{name}" + (f" [{default}]" if default is not None else "") + ": ").strip()
        if not s and default is not None:
            return float(default)
        try:
            return float(s)
        except ValueError:
            print("Enter a number.")

def prompt_int(name: str, default: int | None = None) -> int:
    while True:
        s = input(f"{name}" + (f" [{default}]" if default is not None else "") + ": ").strip()
        if not s and default is not None:
            return int(default)
        try:
            return int(s)
        except ValueError:
            print("Enter an integer.")

def main() -> None:
    print("Default location: Bremen")
    print("Choose mode: (1) log  (2) train  (3) recommend")
    mode = input("mode [1/2/3]: ").strip()

    if mode == "1":
        manual = prompt_float("manual_moisture_pct (0-100)")
        horizon = prompt_int("horizon_hours", 12)
        hourly = fetch_open_meteo_hourly(DEFAULT_LAT, DEFAULT_LON)
        now_local = pd.Timestamp.now(tz=hourly.index.tz)
        feats = weather_features_next_hours(hourly, now_local, horizon)
        append_event(now_local, manual, DEFAULT_LAT, DEFAULT_LON, feats)
        print(f"Logged to {DATA_CSV}")

    elif mode == "2":
        target_min = prompt_float("target_min_moisture_pct", 35.0)
        train_model(target_min)

    elif mode == "3":
        manual = prompt_float("manual_moisture_pct (0-100)")
        horizon = prompt_int("horizon_hours", 12)
        target_min = prompt_float("target_min_moisture_pct", 35.0)
        rain_mm = prompt_float("rain_skip_mm", 2.0)
        rain_prob = prompt_float("rain_skip_prob (0-1)", 0.60)
        override_conf = prompt_float("override_confidence (0-1)", 0.60)
        out = recommend(manual, horizon, target_min, rain_mm, rain_prob, override_conf)
        print(json.dumps(out, indent=2))

    else:
        print("Invalid mode.")

if __name__ == "__main__":
    main()
