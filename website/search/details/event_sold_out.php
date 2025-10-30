<?php
require_once __DIR__ . '/../../utils/db.php';

$eventId = (int)($_GET['event_id'] ?? 0);
if ($eventId <= 0) {
  http_response_code(400);
  die('Invalid event id');
}

$eventStmt = $pdo->prepare("
  SELECT e.event_id, e.title, e.start_at, e.description, e.status,
         v.name AS venue_name, v.address
  FROM Event e
  JOIN Venue v ON v.venue_id = e.venue_id
  WHERE e.event_id = :id
");
$eventStmt->execute([':id' => $eventId]);
$event = $eventStmt->fetch(PDO::FETCH_ASSOC);

$wlStmt = $pdo->prepare("
  SELECT wl.entry_id, wl.created_at, wl.seats_requested, wl.status,
         u.user_id, u.name, u.email
  FROM Waitlist wl
  JOIN User u ON u.user_id = wl.user_id
  WHERE wl.event_id = :id
    AND wl.status IN ('ACTIVE','OFFERED')
  ORDER BY wl.created_at ASC
");
$wlStmt->execute([':id' => $eventId]);
$waitlist = $wlStmt->fetchAll(PDO::FETCH_ASSOC);

$aggStmt = $pdo->prepare("
  SELECT COUNT(t.ticket_id) AS total_tickets,
         SUM(CASE WHEN t.status='AVAILABLE' THEN 1 ELSE 0 END) AS available_tickets,
         SUM(CASE WHEN t.status='TICKETED' THEN 1 ELSE 0 END) AS sold_tickets
  FROM Ticket t
  WHERE t.event_id = :id
");
$aggStmt->execute([':id' => $eventId]);
$stats = $aggStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sold-Out Event Detail</title>
  <link rel="stylesheet" href="../../css/style.css" />
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="container navbar-content">
      <div class="navbar-brand">
        <img class="logo-icon" src="../../img/logo_main.png" alt="EventLink Logo" />
        <span class="brand-text">EventLink</span>
      </div>
      <div class="navbar-links">
        <a href="../../" class="nav-link">Browse Events</a>
        <a href="../../maintenance" class="nav-link">Maintenance</a>
        <a href="../../search/" class="nav-link">Search</a>
      </div>
      <div class="navbar-actions">
        <button class="btn-ghost"><i data-lucide="user"></i>Sign In</button>
        <button class="btn-primary">Sign Up</button>
        <button class="menu-toggle"><i data-lucide="menu"></i></button>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <h1 class="hero-title">Event Detail</h1>
      <p class="hero-subtitle">Sold-out event with waitlist</p>
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
          <p class="section-subtitle" style="margin-top:-8px;"><?php echo htmlspecialchars($event['start_at'], ENT_QUOTES, 'UTF-8'); ?></p>
          <div><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue_name'], ENT_QUOTES, 'UTF-8'); ?><?php if ($event['address']) echo ' — '.htmlspecialchars($event['address'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div><strong>Status:</strong> <?php echo htmlspecialchars($event['status'], ENT_QUOTES, 'UTF-8'); ?></div>
          <?php if (!empty($event['description'])): ?>
            <div><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')); ?></div>
          <?php endif; ?>
          <?php if ($stats): ?>
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
              <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:6px 10px;">Total: <?php echo (int)$stats['total_tickets']; ?></span>
              <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:6px 10px;">Available: <?php echo (int)$stats['available_tickets']; ?></span>
              <span style="background:#111;color:#ffd8bc;border-radius:999px;padding:6px 10px;">Sold: <?php echo (int)$stats['sold_tickets']; ?></span>
            </div>
          <?php endif; ?>
        </div>

        <div style="height:16px;"></div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
          <h3 class="section-title" style="font-size:1.15rem;">Active Waitlist</h3>
          <?php if (empty($waitlist)): ?>
            <p style="color:#374151;">No active waitlist entries.</p>
          <?php else: ?>
            <div style="overflow:auto;">
              <table style="width:100%;border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Entry</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">User</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Email</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Seats</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Created</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($waitlist as $wl): ?>
                    <tr>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo (int)$wl['entry_id']; ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo htmlspecialchars($wl['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo htmlspecialchars($wl['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo (int)$wl['seats_requested']; ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo htmlspecialchars($wl['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo htmlspecialchars($wl['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <div style="margin-top:12px;">
          <a href="../../search/event_sold_out.php" class="btn-ghost">Back to Search</a>
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
