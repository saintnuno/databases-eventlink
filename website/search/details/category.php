<?php
require_once __DIR__ . '/../../utils/paths.php';
require_once UTILS_DIR . '/auth.php';
require_once UTILS_DIR . '/db.php';
require_login();

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$start = isset($_GET['start_at']) ? trim($_GET['start_at']) : '';
$end   = isset($_GET['end_at']) ? trim($_GET['end_at']) : '';

if ($start === '') $start = '2025-01-01 00:00:00';
if ($end === '')   $end   = '2025-12-31 23:59:59';

if ($category === '' || strtolower($category) === 'uncategorized') {
  $sumStmt = $pdo->prepare("
    SELECT COUNT(DISTINCT e.event_id) AS events_count,
           COUNT(t.ticket_id) AS tickets_created,
           SUM(CASE WHEN t.status='TICKETED' THEN 1 ELSE 0 END) AS tickets_sold
    FROM Event e
    LEFT JOIN Ticket t ON t.event_id = e.event_id
    WHERE e.start_at BETWEEN :start_at AND :end_at
      AND (e.category IS NULL OR e.category = '')
  ");
  $sumStmt->execute([':start_at' => $start, ':end_at' => $end]);
  $summary = $sumStmt->fetch(PDO::FETCH_ASSOC);

  $listStmt = $pdo->prepare("
    SELECT e.event_id, e.title, e.start_at, v.name AS venue_name
    FROM Event e
    JOIN Venue v ON v.venue_id = e.venue_id
    WHERE e.start_at BETWEEN :start_at AND :end_at
      AND (e.category IS NULL OR e.category = '')
    ORDER BY e.start_at ASC
  ");
  $listStmt->execute([':start_at' => $start, ':end_at' => $end]);
} else {
  $sumStmt = $pdo->prepare("
    SELECT COUNT(DISTINCT e.event_id) AS events_count,
           COUNT(t.ticket_id) AS tickets_created,
           SUM(CASE WHEN t.status='TICKETED' THEN 1 ELSE 0 END) AS tickets_sold
    FROM Event e
    LEFT JOIN Ticket t ON t.event_id = e.event_id
    WHERE e.start_at BETWEEN :start_at AND :end_at
      AND e.category = :cat
  ");
  $sumStmt->execute([':start_at' => $start, ':end_at' => $end, ':cat' => $category]);
  $summary = $sumStmt->fetch(PDO::FETCH_ASSOC);

  $listStmt = $pdo->prepare("
    SELECT e.event_id, e.title, e.start_at, v.name AS venue_name
    FROM Event e
    JOIN Venue v ON v.venue_id = e.venue_id
    WHERE e.start_at BETWEEN :start_at AND :end_at
      AND e.category = :cat
    ORDER BY e.start_at ASC
  ");
  $listStmt->execute([':start_at' => $start, ':end_at' => $end, ':cat' => $category]);
}

$events = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$basePrefix = '../..';
$activeNav = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Category Detail</title>
  <link rel="stylesheet" href="../../css/style.css" />
</head>
<body>
  <?php require_once LAYOUT_DIR . '/navbar.php'; ?>
        <a href="../../login/"><button class="btn-ghost"><i data-lucide="user"></i>Sign In</button></a>
        <button class="btn-primary">Sign Up</button>
        <button class="menu-toggle"><i data-lucide="menu"></i></button>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <h1 class="hero-title">Category Detail</h1>
      <p class="hero-subtitle">
        <?php echo $category === '' ? 'Uncategorized' : htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>
      </p>
    </div>
  </section>

  <section class="events-section">
    <div class="container">
      <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;display:grid;gap:10px;">
        <h2 class="section-title" style="margin-bottom:0;">
          <?php echo $category === '' ? 'Uncategorized' : htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>
        </h2>
        <p class="section-subtitle" style="margin-top:-8px;">
          <?php echo htmlspecialchars($start, ENT_QUOTES, 'UTF-8'); ?> to <?php echo htmlspecialchars($end, ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <?php if ($summary): ?>
          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
            <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:6px 10px;">
              Events: <?php echo (int)$summary['events_count']; ?>
            </span>
            <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:6px 10px;">
              Tickets created: <?php echo (int)$summary['tickets_created']; ?>
            </span>
            <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:6px 10px;">
              Sold: <?php echo (int)$summary['tickets_sold']; ?>
            </span>
          </div>
        <?php endif; ?>
      </div>

      <div style="height:16px;"></div>

      <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
        <h3 class="section-title" style="font-size:1.15rem;">Events</h3>
        <?php if (empty($events)): ?>
          <p style="color:#374151;">No events found.</p>
        <?php else: ?>
          <div class="events-grid">
            <?php foreach ($events as $e): ?>
              <div class="event-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                  <h3 style="font-weight:800;font-size:1.1rem;"><?php echo htmlspecialchars($e['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                </div>
                <div style="color:#374151;">
                  <div><strong>Starts:</strong> <?php echo htmlspecialchars($e['start_at'], ENT_QUOTES, 'UTF-8'); ?></div>
                  <div><strong>Venue:</strong> <?php echo htmlspecialchars($e['venue_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <div style="margin-top:8px;">
                  <a class="btn-primary" href="../../search/details/event.php?event_id=<?php echo (int)$e['event_id']; ?>">View event</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div style="margin-top:12px;">
          <a href="../../search/category.php" class="btn-ghost">Back to Search</a>
        </div>
      </div>
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
