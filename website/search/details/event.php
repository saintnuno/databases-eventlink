<?php
require_once __DIR__ . '/../../utils/paths.php';
require_once UTILS_DIR . '/auth.php';
require_once UTILS_DIR . '/db.php';
require_login();

$eventId = (int)($_GET['event_id'] ?? 0);
if ($eventId <= 0) {
  http_response_code(400);
  die('Invalid event id');
}

$eStmt = $pdo->prepare("
  SELECT e.event_id, e.title, e.category, e.description, e.start_at, e.status,
         v.name AS venue_name, v.address
  FROM Event e
  JOIN Venue v ON v.venue_id = e.venue_id
  WHERE e.event_id = :id
");
$eStmt->execute([':id' => $eventId]);
$event = $eStmt->fetch(PDO::FETCH_ASSOC);

$aggStmt = $pdo->prepare("
  SELECT COUNT(*) AS total_tickets,
         SUM(CASE WHEN status='AVAILABLE' THEN 1 ELSE 0 END) AS available_tickets,
         SUM(CASE WHEN status='TICKETED' THEN 1 ELSE 0 END) AS sold_tickets,
         SUM(CASE WHEN status='HELD' THEN 1 ELSE 0 END) AS held_tickets,
         SUM(CASE WHEN status='BLOCKED' THEN 1 ELSE 0 END) AS blocked_tickets
  FROM Ticket
  WHERE event_id = :id
");
$aggStmt->execute([':id' => $eventId]);
$stats = $aggStmt->fetch(PDO::FETCH_ASSOC);

$listStmt = $pdo->prepare("
  SELECT s.seat_label, t.status, t.price
  FROM Ticket t
  JOIN Seat s ON s.seat_id = t.seat_id
  WHERE t.event_id = :id
  ORDER BY s.seat_label
  LIMIT 20
");
$listStmt->execute([':id' => $eventId]);
$sample = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$basePrefix = '../..';
$activeNav = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Details Event Availability</title>
  <link rel="stylesheet" href="../../css/style.css" />
</head>
<body>
  <?php require_once LAYOUT_DIR . '/navbar.php'; ?>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <h1 class="hero-title">Event Details</h1>
    </div>
  </section>

  <section class="events-section">
    <div class="container">
      <?php if (!$event): ?>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
          Event not found.
        </div>
      <?php else: ?>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;display:grid;gap:10px;">
          <h2 class="section-title" style="margin-bottom:0;"><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
          <p class="section-subtitle" style="margin-top:-8px;">Starts: <?php echo htmlspecialchars($event['start_at'], ENT_QUOTES, 'UTF-8'); ?></p>
          <div><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue_name'], ENT_QUOTES, 'UTF-8'); ?><?php if ($event['address']) echo ' - '.htmlspecialchars($event['address'], ENT_QUOTES, 'UTF-8'); ?></div>
          <?php if ($event['category']): ?><div><strong>Category:</strong> <?php echo htmlspecialchars($event['category'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
          <div><strong>Status:</strong> <?php echo htmlspecialchars($event['status'], ENT_QUOTES, 'UTF-8'); ?></div>
          <?php if ($event['description']): ?>
            <div><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')); ?></div>
          <?php endif; ?>

          <?php if ($stats): ?>
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
              <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:6px 10px;">Total: <?php echo (int)$stats['total_tickets']; ?></span>
              <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:6px 10px;">Available: <?php echo (int)$stats['available_tickets']; ?></span>
              <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:6px 10px;">Sold: <?php echo (int)$stats['sold_tickets']; ?></span>
              <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:6px 10px;">Held: <?php echo (int)$stats['held_tickets']; ?></span>
              <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:6px 10px;">Blocked: <?php echo (int)$stats['blocked_tickets']; ?></span>
            </div>
          <?php endif; ?>
        </div>

        <div style="height:16px;"></div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
          <h3 class="section-title" style="font-size:1.15rem;">First 20 seats</h3>
          <?php if (empty($sample)): ?>
            <p style="color:#374151;">No seats/tickets found for this event.</p>
          <?php else: ?>
            <div style="overflow:auto;">
              <table style="width:100%;border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Seat</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Status</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Price</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($sample as $row): ?>
                    <tr>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo htmlspecialchars($row['seat_label'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo isset($row['price']) ? (int)$row['price'] : ''; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <div style="margin-top:12px;">
          <a href="../event.php" class="btn-ghost">Back to Search</a>
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
