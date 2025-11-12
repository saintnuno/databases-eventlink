import csv
import datetime as dt
from pathlib import Path
from cfg import access_file, error_file, out_file, csv_dir, pages_csv, errs_csv
from parse import read_access, read_error
from html import build

if __name__ == "__main__":
    a = read_access(access_file)
    e = read_error(error_file)
    csv_dir.mkdir(parents=True, exist_ok=True)
    if a["ok"] > 0:
        with open(pages_csv, "w", newline="", encoding="utf-8") as f:
            w = csv.writer(f)
            w.writerow(["page", "hits", "unique_ips", "top_ip", "first_seen", "last_seen"])
            for page, hits in a["pages"].most_common():
                bag = a["ips"][page]
                top_ip, top_hits = ("", 0)
                if bag:
                    top_ip, top_hits = bag.most_common(1)[0]
                w.writerow([page, hits, len(bag), f"{top_ip} ({top_hits})" if top_ip else "", a["first"].get(page).isoformat() if a["first"].get(page) else "", a["last"].get(page).isoformat() if a["last"].get(page) else ""])
    if e["ok"] > 0:
        with open(errs_csv, "w", newline="", encoding="utf-8") as f:
            w = csv.writer(f)
            w.writerow(["timestamp", "level", "client", "message"])
            for x in e["items"]:
                w.writerow([x["timestamp"], x["level"], x["client"], x["message"]])
    hours = sorted(set(a["per_hour"].keys()) | set(e["per_hour"].keys()))
    req_counts = [a["per_hour"].get(h, 0) for h in hours]
    err_counts = [e["per_hour"].get(h, 0) for h in hours]
    bl = [k for k, _ in a["browsers"].most_common()]
    bv = [v for _, v in a["browsers"].most_common()]
    rows = a["rows"][:600]
    page_rows = []
    for page, hits in a["pages"].most_common():
        bag = a["ips"][page]
        top = ""
        if bag:
            t_ip, t_hits = bag.most_common(1)[0]
            top = f"{t_ip} ({t_hits})"
        page_rows.append({"page": page, "hits": hits, "unique_ips": len(bag), "top_ip": top, "first": a["first"].get(page).isoformat() if a["first"].get(page) else "", "last": a["last"].get(page).isoformat() if a["last"].get(page) else ""})
    build(out_file, a, e, hours, req_counts, err_counts, bl, bv, page_rows, rows)
    print("wrote", out_file)
    if a["ok"] > 0:
        print("wrote", pages_csv)
    if e["ok"] > 0:
        print("wrote", errs_csv)
