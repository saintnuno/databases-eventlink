<?php
require_once __DIR__ . '/../utils/paths.php';
require_once UTILS_DIR . '/db.php';
require_once UTILS_DIR . '/auth.php';
require_login();

$users = $pdo->query("SELECT user_id, name, email FROM User ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$basePrefix = '..';
$activeNav = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Search Orders by User</title>
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
      <h1 class="hero-title">Orders by User</h1>
      <p class="hero-subtitle">Select a user to see their orders with ticket counts and venues</p>
    </div>
  </section>

  <!-- Form -->
  <section class="events-section">
    <div class="container">
      <div class="section-header">
        <div>
          <h2 class="section-title">Search Criteria</h2>
          <p class="section-subtitle">Choose a user</p>
        </div>
      </div>

      <form action="results/orders.php" method="get" style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
        <div style="display:grid;grid-template-columns:1fr;gap:16px;">
          <label>
            <span style="display:block;font-weight:700;margin-bottom:6px;">User *</span>
            <select name="user_id" required class="input" style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
              <option value="">Select a user…</option>
              <?php foreach ($users as $u): ?>
                <option value="<?php echo (int)$u['user_id']; ?>">
                  <?php echo htmlspecialchars($u['name'].' — '.$u['email'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>
        </div>

        <div style="margin-top:16px;display:flex;gap:12px;">
          <button type="submit" class="btn-primary">Search</button>
          <a href="../" class="btn-ghost">Back to Home</a>
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

  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>
