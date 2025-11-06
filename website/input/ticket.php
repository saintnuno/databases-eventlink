<?php
require_once __DIR__ . '/../utils/paths.php';
require_once UTILS_DIR . '/db.php';
require_once UTILS_DIR . '/auth.php';
require_login();

$events = $pdo->query("
  SELECT e.event_id, e.title, e.start_at, v.name AS venue
  FROM Event e
  JOIN Venue v ON v.venue_id = e.venue_id
  ORDER BY e.start_at ASC
")->fetchAll();

$basePrefix = '..';
$activeNav = 'maintenance';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tickets</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <?php require_once LAYOUT_DIR . '/navbar.php'; ?>
  
  <!-- Hero -->
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <h1 class="hero-title">Tickets</h1>
      <p class="hero-subtitle">Add a new ticket</p>
    </div>
  </section>

  <!-- Form -->
  <section class="events-section">
    <div class="container">
      <div class="section-header">
        <div>
          <h2 class="section-title">Ticket Details</h2>
          <p class="section-subtitle">Please fill out all required fields</p>
        </div>
      </div>

      <form action="../process/ticket.php" method="post" style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
        <div style="display:grid;grid-template-columns:1fr;gap:16px;">

          <!-- Event -->
          <label>
            <span style="display:block;font-weight:700;margin-bottom:6px;">Event *</span>
            <select id="eventSelect" name="event_id" required
                    class="input"
                    style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
              <option value="">Select an event…</option>
              <?php foreach ($events as $e): ?>
                <option value="<?php echo (int)$e['event_id']; ?>">
                  <?php echo htmlspecialchars($e['title'].' — '.$e['venue'].' — '.$e['start_at'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            <span style="display:block;font-weight:700;margin-bottom:6px;">Seat *</span>
            <select id="seatSelect" name="seat_id" required disabled
                    class="input"
                    style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
              <option value="">Select an event first…</option>
            </select>
          </label>

          <label>
            <span style="display:block;font-weight:700;margin-bottom:6px;">Price *</span>
            <input type="number" step="1" min="0" name="price" required placeholder="50"
                   class="input"
                   style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
          </label>

          <label>
            <span style="display:block;font-weight:700;margin-bottom:6px;">Status *</span>
            <select name="status" required
                    class="input"
                    style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
              <option value="AVAILABLE">AVAILABLE</option>
              <option value="HELD">HELD</option>
              <option value="TICKETED">TICKETED</option>
              <option value="BLOCKED">BLOCKED</option>
            </select>
          </label>

          <label>
            <span style="display:block;font-weight:700;margin-bottom:6px;">QR Code</span>
            <input type="text" name="qr_code" placeholder="QR1_123"
                   class="input"
                   style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
          </label>

        </div>

        <div style="margin-top:16px;display:flex;gap:12px;">
          <button type="submit" class="btn-primary">Save Ticket</button>
          <a href="../maintenance" class="btn-ghost">Back to Maintenance</a>
        </div>
      </form>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-column">
          <div class="footer-brand">
            <img class="logo-icon" src="../img/logo_main.png" alt="EventLink Logo" />
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
        <div class="footer-legal"><a href="../imprint">Imprint</a></div>
      </div>
    </div>
  </footer>

  <script src="../js/filterSeat.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
