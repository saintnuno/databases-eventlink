from pathlib import Path

access_file = "/var/log/apache2/access_log"
error_file = "/var/log/apache2/error_log"

out_file = "../public_html/report/index.html"
csv_dir = Path("csvs")
pages_csv = csv_dir / "matches.csv"
errs_csv = csv_dir / "errors.csv"
