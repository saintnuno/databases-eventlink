<?php
require_once __DIR__ . '/../../utils/db.php';

$orderId = (int)($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
  http_response_code(400);
  die('Invalid order id');
}

$hdr = $pdo->prepare("
  SELECT o.order_id, o.created_at, o.status, o.payment_status,
         u.user_id, u.name AS user_name, u.email AS user_email
  FROM `Order` o
  JOIN User u ON u.user_id = o.user_id
  WHERE o.order_id = :id
");
$hdr->execute([':id' => $orderId]);
$order = $hdr->fetch(PDO::FETCH_ASSOC);

$lines = $pdo->prepare("
  SELECT t.ticket_id, t.price, s.seat_label, e.title, e.start_at, v.name AS venue_name
  FROM Ticket t
  JOIN `Order` o ON o.order_id = t.order_id
  JOIN Event e   ON e.event_id = t.event_id
  JOIN Seat s    ON s.seat_id = t.seat_id
  JOIN Venue v   ON v.venue_id = e.venue_id
  WHERE o.order_id = :id
  ORDER BY e.start_at, s.seat_label
");
$lines->execute([':id' => $orderId]);
$items = $lines->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Details Orders by User</title>
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
        <a href="../" class="nav-link">Search</a>
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
      <h1 class="hero-title">Order Detail</h1>
    </div>
  </section>

  <section class="events-section">
    <div class="container">
      <?php if (!$order): ?>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
          Order not found.
        </div>
      <?php else: ?>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;display:grid;gap:10px;">
          <h2 class="section-title" style="margin-bottom:0;">Order #<?php echo (int)$order['order_id']; ?></h2>
          <p class="section-subtitle" style="margin-top:-8px;">Created: <?php echo htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
          <div><strong>Customer:</strong> <?php echo htmlspecialchars($order['user_name'].' - '.$order['user_email'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div><strong>Status:</strong> <?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?></div>
          <div><strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_status'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <div style="height:16px;"></div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
          <h3 class="section-title" style="font-size:1.15rem;">Tickets</h3>
          <?php if (empty($items)): ?>
            <p style="color:#374151;">No tickets on this order.</p>
          <?php else: ?>
            <div style="overflow:auto;">
              <table style="width:100%;border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Ticket #</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Seat</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Event</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Start</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Venue</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Price</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($items as $it): ?>
                    <tr>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo (int)$it['ticket_id']; ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo htmlspecialchars($it['seat_label'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo htmlspecialchars($it['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo htmlspecialchars($it['start_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo htmlspecialchars($it['venue_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?php echo (int)$it['price']; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <div style="margin-top:12px;">
          <a href="../orders.php" class="btn-ghost">Back to Search</a>
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
