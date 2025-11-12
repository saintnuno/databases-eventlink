import datetime as dt
import re
import collections
import pathlib
from urllib.parse import urlparse

access_rx = re.compile(
    r'^(?P<ip>\S+) \S+ \S+ '
    r'\[(?P<ts>[^\]]+)\] '
    r'"(?P<m>\S+)\s+(?P<p>[^"]*?)\s+(?P<h>HTTP/\d\.\d|\S+)" '
    r'(?P<s>\d{3}) (?P<z>\S+)'
    r'(?:\s+"(?P<r>[^"]*)"(?:\s+"(?P<a>[^"]*)")?)?\s*$'
)
err_ts_rx = re.compile(r'^\[(?P<ts>[^\]]+)\]\s+(?P<rest>.*)$')
err_client_rx = re.compile(r'\[client (?P<ip>[\d\.:%a-fA-F]+)\]')
err_level_rx = re.compile(r'\[(?P<level>[a-zA-Z]+(?::[a-zA-Z]+)?)\]')

def at(s):
    return dt.datetime.strptime(s, "%d/%b/%Y:%H:%M:%S %z")

def et(s):
    for f in ("%a %b %d %H:%M:%S %Y", "%a %b %d %H:%M:%S.%f %Y", "%a %b %d %H:%M:%S.%f %Y %z", "%a %b %d %H:%M:%S %Y %z"):
        try:
            d = dt.datetime.strptime(s, f)
            if d.tzinfo is None:
                d = d.replace(tzinfo=dt.timezone.utc).astimezone()
            return d
        except ValueError:
            pass
    return None

def hour(t):
    return t.replace(minute=0, second=0, microsecond=0).isoformat()

def path_only(s):
    try:
        p = urlparse(s)
        return (p.path or s or "/") or "/"
    except Exception:
        return s.split("?", 1)[0]

def browser(ua):
    ua = (ua or "").lower()
    if "edg/" in ua or "edge/" in ua: return "Edge"
    if "chrome" in ua and "chromium" not in ua and "edg/" not in ua: return "Chrome"
    if "firefox" in ua: return "Firefox"
    if "safari" in ua and "chrome" not in ua: return "Safari"
    if "trident" in ua or "msie" in ua or "iemobile" in ua: return "Internet Explorer"
    if "curl/" in ua: return "curl"
    if "wget/" in ua: return "wget"
    return "Other"

def grep(path, needle):
    p = pathlib.Path(path)
    if not p.exists() or not p.is_file():
        return
    with open(p, "r", errors="ignore", encoding="utf-8", newline="") as f:
        for line in f:
            if needle in line:
                yield line.rstrip("\n")

def read_access(path):
    pages = collections.Counter()
    ips = collections.defaultdict(collections.Counter)
    first, last = {}, {}
    browsers = collections.Counter()
    per_hour = collections.Counter()
    rows = []
    total = ok = 0
    for raw in grep(path, "~ntaha"):
        total += 1
        m = access_rx.match(raw)
        if not m:
            continue
        ok += 1
        d = m.groupdict()
        ip = d["ip"]
        ts = at(d["ts"])
        pth = path_only(d["p"])
        st = int(d["s"])
        ua = d.get("a") or ""
        pages[pth] += 1
        ips[pth][ip] += 1
        browsers[browser(ua)] += 1
        per_hour[hour(ts)] += 1
        if pth not in first or ts < first[pth]: first[pth] = ts
        if pth not in last or ts > last[pth]:  last[pth] = ts
        rows.append({"timestamp": ts.isoformat(), "ip": ip, "path": pth, "status": st, "user_agent": ua})
    return {"pages": pages, "ips": ips, "first": first, "last": last, "browsers": browsers, "per_hour": per_hour, "rows": rows, "total": total, "ok": ok}

def read_error(path):
    items = []
    per_hour = collections.Counter()
    total = ok = 0
    for raw in grep(path, "ntaha"):
        total += 1
        m = err_ts_rx.match(raw.strip())
        if not m:
            continue
        ok += 1
        ts_raw, rest = m.group("ts"), m.group("rest")
        ts = et(ts_raw)
        if ts is None:
            continue
        lvl = err_level_rx.search(rest)
        lvl = lvl.group("level") if lvl else "unknown"
        cli = err_client_rx.search(rest)
        cli = cli.group("ip") if cli else ""
        msg = re.sub(r"\[[^\]]*\]\s*", "", rest).strip()
        items.append({"timestamp": ts.isoformat(), "level": lvl, "client": cli, "message": msg})
        per_hour[hour(ts)] += 1
    return {"items": items, "per_hour": per_hour, "total": total, "ok": ok}
