<?php
require_once __DIR__ . '/../../utils/db.php';

$stmt = $pdo->query("
  SELECT  e.event_id, e.title, e.start_at,
          COUNT(t.ticket_id) AS total_tickets,
          SUM(CASE WHEN t.status = 'AVAILABLE' THEN 1 ELSE 0 END) AS available_tickets,
          COALESCE(w.wl_size, 0) AS waitlist_size
  FROM Event e
  JOIN Ticket t ON t.event_id = e.event_id
  LEFT JOIN (
    SELECT event_id, COUNT(*) AS wl_size
    FROM Waitlist
    WHERE status IN ('ACTIVE','OFFERED')
    GROUP BY event_id
  ) w ON w.event_id = e.event_id
  GROUP BY e.event_id, e.title, e.start_at, w.wl_size
  HAVING available_tickets = 0
  ORDER BY e.start_at;
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EventLink — Sold-out Results</title>
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
      <h1 class="hero-title">Sold-out Events</h1>
      <p class="hero-subtitle">Result list</p>
    </div>
  </section>

  <!-- Results -->
  <section class="events-section">
    <div class="container">
      <div class="section-header">
        <div>
          <h2 class="section-title">Result List</h2>
          <p class="section-subtitle">Showing <?php echo count($rows); ?> result(s)</p>
        </div>
        <a class="btn-view-all" href="../event_sold_out.php">New Search</a>
      </div>

      <div class="events-grid">
        <?php foreach ($rows as $r): ?>
          <div class="event-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;display:flex;flex-direction:column;gap:8px;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
              <h3 style="font-weight:800;font-size:1.1rem;"><?php echo htmlspecialchars($r['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <span style="font-size:.85rem;background:#111;color:#ffd8bc;padding:4px 8px;border-radius:999px;">
                Waitlist: <?php echo (int)$r['waitlist_size']; ?>
              </span>
            </div>
            <div style="color:#374151;">
              <div><strong>Starts:</strong> <?php echo htmlspecialchars($r['start_at'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div><strong>Total tickets:</strong> <?php echo (int)$r['total_tickets']; ?></div>
              <div><strong>Available:</strong> <?php echo (int)$r['available_tickets']; ?></div>
            </div>
            <div style="margin-top:8px;">
              <a class="btn-primary" href="../details/event_sold_out.php?event_id=<?php echo (int)$r['event_id']; ?>">
                View event detail
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (count($rows) === 0): ?>
        <div style="margin-top:16px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;">
          No sold-out events found.
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
