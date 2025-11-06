<?php
require_once __DIR__ . '/../../utils/paths.php';
require_once UTILS_DIR . '/auth.php';
require_once UTILS_DIR . '/db.php';
require_login();

$userId = (int)($_GET['user_id'] ?? 0);
$errors = [];
if ($userId <= 0) $errors[] = 'Please select a valid user.';

$user = null;
$rows = [];

if (!$errors) {
  $u = $pdo->prepare("SELECT user_id, name, email FROM User WHERE user_id = :id");
  $u->execute([':id' => $userId]);
  $user = $u->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    $errors[] = 'User not found.';
  } else {
    $stmt = $pdo->prepare("
      SELECT  o.order_id,
              o.created_at,
              o.status,
              o.payment_status,
              COUNT(t.ticket_id) AS ticket_count,
              GROUP_CONCAT(DISTINCT v.name ORDER BY v.name SEPARATOR ', ') AS venues
      FROM `Order` o
      JOIN Ticket t ON t.order_id = o.order_id
      JOIN Event e  ON e.event_id = t.event_id
      JOIN Venue v  ON v.venue_id = e.venue_id
      WHERE o.user_id = :user_id
      GROUP BY o.order_id, o.created_at, o.status, o.payment_status
      ORDER BY o.created_at DESC
    ");
    $stmt->execute([':user_id' => $userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

$basePrefix = '../..';
$activeNav = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Results Orders by User</title>
  <link rel="stylesheet" href="../../css/style.css" />
</head>
<body>
  <?php require_once LAYOUT_DIR . '/navbar.php'; ?>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <h1 class="hero-title">Orders for <?php echo $user ? htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') : '—'; ?></h1>
      <p class="hero-subtitle"><?php echo $user ? htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') : ''; ?></p>
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
              Showing <?php echo count($rows); ?> order(s)
            <?php endif; ?>
          </p>
        </div>
        <a class="btn-view-all" href="../orders.php">New Search</a>
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
              <h3 style="font-weight:800;font-size:1.1rem;">Order #<?php echo (int)$r['order_id']; ?></h3>
              <span style="font-size:.85rem;background:#111;color:#ffd8bc;padding:4px 8px;border-radius:999px;">
                <?php echo (int)$r['ticket_count']; ?> ticket(s)
              </span>
            </div>
            <div style="color:#374151;">
              <div><strong>Created:</strong> <?php echo htmlspecialchars($r['created_at'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div><strong>Status:</strong> <?php echo htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div><strong>Payment:</strong> <?php echo htmlspecialchars($r['payment_status'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div><strong>Venues:</strong> <?php echo htmlspecialchars($r['venues'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <div style="margin-top:8px;">
              <a class="btn-primary" href="../details/orders.php?order_id=<?php echo (int)$r['order_id']; ?>">
                View order detail
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (!$errors && count($rows) === 0): ?>
        <div style="margin-top:16px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;">
          No orders found for this user.
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
