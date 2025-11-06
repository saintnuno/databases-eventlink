<?php
require_once __DIR__ . '/../../utils/paths.php';
require_once UTILS_DIR . '/auth.php';
require_once UTILS_DIR . '/db.php';
require_login();

$start = trim($_GET['start_at'] ?? '');
$end   = trim($_GET['end_at'] ?? '');

if ($start === '') {
  $start = '2025-01-01 00:00:00';
}
if ($end === '') {
  $end = '2025-12-31 23:59:59';
}

$stmt = $pdo->prepare("
  SELECT  e.category,
          COUNT(DISTINCT e.event_id) AS events_count,
          COUNT(t.ticket_id)         AS tickets_created,
          SUM(CASE WHEN t.status = 'TICKETED' THEN 1 ELSE 0 END) AS tickets_sold
  FROM Event e
  LEFT JOIN Ticket t ON t.event_id = e.event_id
  WHERE e.start_at BETWEEN :start_at AND :end_at
  GROUP BY e.category
  HAVING events_count > 0
  ORDER BY tickets_sold DESC, e.category
");
$stmt->execute([
  ':start_at' => $start,
  ':end_at'   => $end,
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$basePrefix = '../..';
$activeNav = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Category Summary — Results</title>
  <link rel="stylesheet" href="../../css/style.css" />
</head>
<body>
  <?php require_once LAYOUT_DIR . '/navbar.php'; ?>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <h1 class="hero-title">Category Summary</h1>
      <p class="hero-subtitle">Results for <?php echo htmlspecialchars($start, ENT_QUOTES, 'UTF-8'); ?> to <?php echo htmlspecialchars($end, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
  </section>

  <!-- Results -->
  <section class="events-section">
    <div class="container">
      <div class="section-header">
        <div>
          <h2 class="section-title">Results</h2>
          <p class="section-subtitle">Showing <?php echo count($rows); ?> category row(s)</p>
        </div>
        <a class="btn-view-all" href="../category.php">New Search</a>
      </div>

      <div class="events-grid">
        <?php foreach ($rows as $r): ?>
          <div class="event-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;display:flex;flex-direction:column;gap:8px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
              <h3 style="font-weight:800;font-size:1.1rem;"><?php echo htmlspecialchars($r['category'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?></h3>
              <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:4px 10px;font-size:.8rem;">
                Sold: <?php echo (int)$r['tickets_sold']; ?>
              </span>
            </div>
            <div style="color:#374151;">
              <div><strong>Events:</strong> <?php echo (int)$r['events_count']; ?></div>
              <div><strong>Tickets created:</strong> <?php echo (int)$r['tickets_created']; ?></div>
            </div>
            <div style="margin-top:8px;">
              <a class="btn-primary" href="../details/category.php?category=<?php echo urlencode($r['category'] ?? ''); ?>&start_at=<?php echo urlencode($start); ?>&end_at=<?php echo urlencode($end); ?>">
                View details
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (count($rows) === 0): ?>
        <div style="margin-top:16px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;">
          No categories found in this period.
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-column">
          <div class="footer-brand">
            <img class="logo-icon" src="../../img/logo_main.png" alt="EventLink Logo" />
            <span class="brand-text">EventLink</span>
          </div>
          <p class="footer-description">Connecting you with unforgettable experiences.</p>
          <div class="social-links">
            <a href="#" class="social-link"><i data-lucide="facebook"></i></a>
            <a href="#" class="social-link"><i data-lucide="twitter"></i></a>
            <a href="#" class="social-link"><i data-lucide="instagram"></i></a>
            <a href="#" class="social-link"><i data-lucide="linkedin"></i></a>
          </div>
        </div>
        <div class="footer-column">
          <h3 class="footer-heading">Explore</h3>
          <ul class="footer-links">
            <li><a href="#">Browse Events</a></li>
            <li><a href="#">Categories</a></li>
            <li><a href="#">Venues</a></li>
            <li><a href="#">Organizers</a></li>
          </ul>
        </div>
        <div class="footer-column">
          <h3 class="footer-heading">For Organizers</h3>
          <ul class="footer-links">
            <li><a href="#">Create Event</a></li>
            <li><a href="#">Pricing</a></li>
            <li><a href="#">Resources</a></li>
            <li><a href="#">Support</a></li>
          </ul>
        </div>
        <div class="footer-column">
          <h3 class="footer-heading">Company</h3>
          <ul class="footer-links">
            <li><a href="#">About Us</a></li>
            <li><a href="#">Careers</a></li>
            <li><a href="#">Press</a></li>
            <li><a href="#">Contact</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p class="footeropyright">© 2025 EventLink. All rights reserved.</p>
        <div class="footer-legal"><a href="../../imprint">Imprint</a></div>
      </div>
    </div>
  </footer>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
