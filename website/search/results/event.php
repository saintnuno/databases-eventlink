<?php
require_once __DIR__ . '/../../utils/paths.php';
require_once UTILS_DIR . '/db.php';
require_once UTILS_DIR . '/auth.php';
require_login();

$kw        = trim($_GET['kw'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate   = trim($_GET['end_date'] ?? '');
$K         = (int)($_GET['K'] ?? 0);

$errors = [];
if ($K <= 0) $errors[] = 'K must be a positive integer.';

$today = new DateTimeImmutable('today');
$oneYear = $today->modify('+1 year');

$startAt = null;
$endAt   = null;

if ($startDate !== '' && $endDate !== '') {
  $startAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $startDate . ' 00:00:00');
  $endAt   = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $endDate   . ' 23:59:59');
} elseif ($startDate !== '' && $endDate === '') {
  $startAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $startDate . ' 00:00:00');
  $endAt   = $startAt->modify('+90 days')->setTime(23,59,59);
} elseif ($startDate === '' && $endDate !== '') {
  $startAt = $today->setTime(0,0,0);
  $endAt   = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $endDate . ' 23:59:59');
} else {
  $startAt = $today->setTime(0,0,0);
  $endAt   = $oneYear->setTime(23,59,59);
}

if ($startAt > $endAt) {
  $errors[] = 'Start date must be before or equal to end date.';
}

$rows = [];
if (!$errors) {
  $kwLike = '%'.$kw.'%';
  $stmt = $pdo->prepare("
    SELECT  e.event_id,
            e.title,
            v.name AS venue_name,
            e.start_at,
            COUNT(t.ticket_id) AS total_tickets,
            SUM(CASE WHEN t.status = 'AVAILABLE' THEN 1 ELSE 0 END) AS available_tickets
    FROM Event e
    JOIN Venue v  ON v.venue_id = e.venue_id
    JOIN Ticket t ON t.event_id = e.event_id
    WHERE e.start_at BETWEEN :start_at AND :end_at
      AND e.title LIKE :kw
    GROUP BY e.event_id, e.title, v.name, e.start_at
    HAVING available_tickets >= :K
    ORDER BY e.start_at ASC
  ");
  $stmt->execute([
    ':start_at' => $startAt->format('Y-m-d H:i:s'),
    ':end_at'   => $endAt->format('Y-m-d H:i:s'),
    ':kw'       => $kwLike,
    ':K'        => $K,
  ]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$basePrefix = '../..';
$activeNav = 'search';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Search Results Event Availibility</title>
  <link rel="stylesheet" href="../../css/style.css" />
</head>
<body>
  <?php require_once LAYOUT_DIR . '/navbar.php'; ?>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <h1 class="hero-title">Event Availability Results</h1>
      <p class="hero-subtitle">Your search results are below</p>
    </div>
  </section>

  <!-- Results -->
  <section class="events-section">
    <div class="container">
      <div class="section-header">
        <div>
          <h2 class="section-title">Result List</h2>
          <p class="section-subtitle">
            <?php if ($errors): ?>
              Please correct the errors below.
            <?php else: ?>
              Showing <?php echo count($rows); ?> result(s)
            <?php endif; ?>
          </p>
        </div>
        <a class="btn-view-all" href="../event.php">New Search</a>
      </div>

      <?php if ($errors): ?>
        <div style="background:#fff;border:1px solid #fecaca;border-radius:12px;padding:16px;margin-bottom:16px;">
          <ul style="margin-left:18px;">
            <?php foreach ($errors as $e): ?>
              <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="events-grid">
        <?php foreach ($rows as $r): ?>
          <div class="event-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;display:flex;flex-direction:column;gap:8px;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
              <h3 style="font-weight:800;font-size:1.1rem;"><?php echo htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <span style="font-size:.85rem;background:#111;color:#ffd8bc;padding:4px 8px;border-radius:999px;">
                <?php echo (int)$r['available_tickets']; ?> available
              </span>
            </div>
            <div style="color:#374151;">
              <div><strong>Venue:</strong> <?php echo htmlspecialchars($r['venue_name'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div><strong>Starts:</strong> <?php echo htmlspecialchars($r['start_at'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div><strong>Total tickets:</strong> <?php echo (int)$r['total_tickets']; ?></div>
            </div>
            <div style="margin-top:8px;">
              <a class="btn-primary" href="../details/event.php?event_id=<?php echo (int)$r['event_id']; ?>">
                View details
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (!$errors && count($rows) === 0): ?>
        <div style="margin-top:16px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;">
          No events matched your filters. Try adjusting your keyword, date range, or K.
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
