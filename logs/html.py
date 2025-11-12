import datetime as dt
import json
import pathlib

def esc(s):
    return (s.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;"))

def build(out_path, a, e, hours, req_counts, err_counts, bl, bv, page_rows, req_rows):
    pages = a["pages"]
    ips = a["ips"]
    uniq_ips = len({ip for bag in ips.values() for ip in bag})
    now = dt.datetime.now().astimezone().isoformat()
    tz = dt.datetime.now().astimezone().tzinfo

    html = f"""<!doctype html>
<html lang="en">
<meta charset="utf-8">
<title>Access logs</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
  :root {{
    --bg: #0d1117;
    --bg-card: #161b22;
    --fg: #c9d1d9;
    --fg-muted: #8b949e;
    --border: #30363d;
    --accent: #58a6ff;
  }}
  body {{
    font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial, sans-serif;
    margin: 24px;
    background: var(--bg);
    color: var(--fg);
  }}
  h1, h2, h3 {{ margin: 0.4em 0; color: var(--fg); }}
  .muted {{ color: var(--fg-muted); }}
  .grid {{ display:grid; gap:16px; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); }}
  .card {{
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 2px 4px rgba(0,0,0,.4);
  }}
  table {{
    width:100%;
    border-collapse: collapse;
    font-size: 14px;
    color: var(--fg);
  }}
  th, td {{
    padding:8px 10px;
    border-bottom:1px solid var(--border);
    text-align:left;
    vertical-align: top;
  }}
  th {{
    background:#1e232a;
    color: var(--fg-muted);
    position: sticky;
    top:0;
    z-index:1;
  }}
  .scroll {{
    max-height: 420px;
    overflow:auto;
    border:1px solid var(--border);
    border-radius:10px;
  }}
  code {{
    background:#21262d;
    padding:2px 5px;
    border-radius:4px;
    color:#e6edf3;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
  }}
  .pill {{
    display:inline-block;
    padding:2px 8px;
    border-radius:999px;
    background:#21262d;
    color: var(--accent);
    font-size:12px;
  }}
  footer {{
    margin-top:24px;
    color: var(--fg-muted);
    font-size:12px;
  }}
  canvas {{
    background:#0d1117;
    border-radius:6px;
  }}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<body>
  <h1>Access logs</h1>
  <p class="muted">Generated on {now} (server TZ: {tz}).</p>

  <div class="grid">
    <div class="card">
      <h2>Overview</h2>
      <p><b>Access log matches:</b> {a["total"]} &middot; <b>parsed:</b> {a["ok"]}</p>
      <p><b>Error log matches:</b> {e["total"]} &middot; <b>parsed:</b> {e["ok"]}</p>
      <p><b>Pages seen:</b> {len(pages)} &middot; <b>Unique IPs:</b> {uniq_ips}</p>
    </div>

    <div class="card">
      <h2>Browsers</h2>
      <canvas id="browserChart" height="200"></canvas>
    </div>

    <div class="card">
      <h2>Requests Timeline (per hour)</h2>
      <canvas id="reqChart" height="200"></canvas>
    </div>

    <div class="card">
      <h2>Errors Timeline (per hour)</h2>
      <canvas id="errChart" height="200"></canvas>
    </div>
  </div>

  <div class="card" style="margin-top:16px">
    <h2>Per-page Statistics</h2>
    <div class="scroll">
    <table>
      <thead>
        <tr>
          <th>Page</th><th>Hits</th><th>Unique IPs</th><th>Top IP</th><th>First Seen</th><th>Last Seen</th>
        </tr>
      </thead>
      <tbody>
        {''.join(f"<tr><td><code>{esc(x['page'])}</code></td><td>{x['hits']}</td><td>{x['unique_ips']}</td><td>{esc(x['top_ip'])}</td><td>{esc(x['first'])}</td><td>{esc(x['last'])}</td></tr>" for x in page_rows)}
      </tbody>
    </table>
    </div>
  </div>

  <div class="grid" style="margin-top:16px">
    <div class="card">
      <h2>Recent Requests</h2>
      <div class="scroll">
      <table>
        <thead><tr><th>Time</th><th>IP</th><th>Path</th><th>Status</th><th>User Agent</th></tr></thead>
        <tbody>
          {''.join(f"<tr><td>{esc(r['timestamp'])}</td><td>{esc(r['ip'])}</td><td><code>{esc(r['path'])}</code></td><td>{r['status']}</td><td>{esc(r['user_agent'])}</td></tr>" for r in req_rows)}
        </tbody>
      </table>
      </div>
    </div>

    <div class="card">
      <h2>Error Log</h2>
      <div class="scroll">
      <table>
        <thead><tr><th>Time</th><th>Level</th><th>Client</th><th>Message</th></tr></thead>
        <tbody>
          {''.join(f"<tr><td>{esc(x['timestamp'])}</td><td><span class='pill'>{esc(x['level'])}</span></td><td>{esc(x['client'])}</td><td>{esc(x['message'])}</td></tr>" for x in e["items"])}
        </tbody>
      </table>
      </div>
      <p class="muted" style="margin-top:8px">I checked the error logs myself, and my name doesn't appear anywhere, so I wasn't sure what to filter for. I filtered for entries containing "ntaha", but there are no matches. I always developed locally first, so maybe that's why?</p>
    </div>
  </div>

<script>
const hourLabels = {json.dumps(hours, ensure_ascii=False)};
const reqCounts  = {json.dumps(req_counts)};
const errCounts  = {json.dumps(err_counts)};
const browserLabels = {json.dumps(bl, ensure_ascii=False)};
const browserValues = {json.dumps(bv)};

function makeLineChart(id, labels, data, labelText, color) {{
  const ctx = document.getElementById(id).getContext('2d');
  new Chart(ctx, {{
    type: 'line',
    data: {{
      labels,
      datasets: [{{ label: labelText, data, borderColor: color, backgroundColor: color, tension: 0.25 }}]
    }},
    options: {{
      plugins: {{
        legend: {{ labels: {{ color: '#c9d1d9' }} }}
      }},
      scales: {{
        x: {{ ticks: {{ color: '#8b949e' }}, grid: {{ color: 'rgba(139,148,158,0.2)' }} }},
        y: {{ beginAtZero: true, ticks: {{ color: '#8b949e' }}, grid: {{ color: 'rgba(139,148,158,0.2)' }} }}
      }}
    }}
  }});
}}

function makePieChart(id, labels, data) {{
  const ctx = document.getElementById(id).getContext('2d');
  new Chart(ctx, {{
    type: 'doughnut',
    data: {{
      labels,
      datasets: [{{ data, backgroundColor: ['#58a6ff','#8b949e','#1f6feb','#79c0ff','#c9d1d9','#3fb950','#f85149'] }}]
    }},
    options: {{
      plugins: {{
        legend: {{ labels: {{ color: '#c9d1d9' }}, position: 'bottom' }}
      }}
    }}
  }});
}}

makePieChart('browserChart', browserLabels, browserValues);
makeLineChart('reqChart', hourLabels, reqCounts, 'Requests', '#58a6ff');
makeLineChart('errChart', hourLabels, errCounts, 'Errors', '#f85149');
</script>
</body>
</html>
"""
    pathlib.Path(out_path).parent.mkdir(parents=True, exist_ok=True)
    with open(out_path, "w", encoding="utf-8") as f:
        f.write(html)
