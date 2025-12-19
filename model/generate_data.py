import numpy as np
import pandas as pd

def generate_synthetic_irrigation_log(
    out_csv="irrigation_log.csv",
    n_rows=100000,
    seed=42,
    lat=52.5200,
    lon=13.4050
):
    rng = np.random.default_rng(seed)

    start = pd.Timestamp("2025-10-01 06:00:00", tz="Europe/Berlin")
    timestamps = pd.date_range(start=start, periods=n_rows, freq="3H")

    temp = 10 + 10*np.sin(np.linspace(0, 8*np.pi, n_rows)) + rng.normal(0, 2.0, n_rows)
    rh = 60 + 20*np.sin(np.linspace(0, 6*np.pi, n_rows) + 1.2) + rng.normal(0, 6.0, n_rows)
    rh = np.clip(rh, 20, 100)

    wind = 5 + 6*np.abs(np.sin(np.linspace(0, 10*np.pi, n_rows))) + rng.normal(0, 1.0, n_rows)
    wind = np.clip(wind, 0, 30)

    precip_prob = rng.beta(2, 6, n_rows)
    storm_idx = rng.choice(np.arange(n_rows), size=max(10, n_rows//30), replace=False)
    precip_prob[storm_idx] = np.clip(precip_prob[storm_idx] + rng.uniform(0.4, 0.8, len(storm_idx)), 0, 1)

    precip_mm = np.where(
        precip_prob > 0.6,
        rng.gamma(shape=2.0, scale=1.5, size=n_rows),
        rng.gamma(shape=0.5, scale=0.3, size=n_rows),
    )
    precip_mm = np.where(precip_prob < 0.25, precip_mm * 0.2, precip_mm)
    precip_mm = np.clip(precip_mm, 0, 20)

    moisture = np.zeros(n_rows)
    moisture[0] = rng.uniform(45, 70)

    for i in range(1, n_rows):
        evap = (
            0.25 * max(temp[i], 0) +
            0.20 * max(wind[i], 0) +
            0.15 * max(70 - rh[i], 0)
        ) / 20.0
        evap += rng.normal(0, 0.7)
        evap = max(evap, 0)

        rain_gain = precip_mm[i] * rng.uniform(0.6, 1.2)
        moisture[i] = moisture[i-1] - evap + rain_gain

        if moisture[i] < rng.uniform(25, 33) and rng.random() < 0.65:
            moisture[i] += rng.uniform(15, 30)

        moisture[i] = float(np.clip(moisture[i], 0, 100))

    df = pd.DataFrame({
        "timestamp": timestamps.astype(str),
        "manual_moisture_pct": moisture.round(1),
        "lat": lat,
        "lon": lon,
        "fc_temp_mean": temp.round(2),
        "fc_rh_mean": rh.round(1),
        "fc_wind_mean": wind.round(2),
        "fc_precip_sum": precip_mm.round(2),
        "fc_precip_prob_max": precip_prob.round(2),
        "fc_precip_prob_mean": (precip_prob * rng.uniform(0.7, 1.0, n_rows)).round(2),
    })

    df.to_csv(out_csv, index=False)
    print(f"Wrote {len(df)} rows to {out_csv}")

if __name__ == "__main__":
    generate_synthetic_irrigation_log()
