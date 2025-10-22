<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Feedback</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="container navbar-content">
      <div class="navbar-brand">
        <img class="logo-icon" src="../img/logo_main.png" alt="EventLink Logo" />
        <span class="brand-text">EventLink</span>
      </div>
      <div class="navbar-links">
        <a href="/" class="nav-link">Browse Events</a>
        <a href="../maintenance" class="nav-link">Maintenance</a>
        <a href="#" class="nav-link">Help</a>
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
      <h1 class="hero-title">Submission Feedback</h1>
      <p class="hero-subtitle">Status of your last action.</p>
    </div>
  </section>

  <section class="events-section">
    <div class="container">
      <div class="section-header">
        <div>
          <h2 class="section-title"><?php echo $ok ? "Success" : "Error"; ?></h2>
        </div>
      </div>

      <div class="card-like" style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
        <p style="margin:0; font-weight:700; color:<?php echo $ok ? '#16a34a' : '#ef4444'; ?>">
          <?php echo htmlspecialchars($message ?? '', ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <div style="margin-top:16px; display:flex; gap:12px;">
          <a class="btn-primary" href="../maintenance">Back to Maintenance</a>
          <a class="btn-ghost" href="javascript:history.back()">Go Back</a>
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
        <p class="footer-copyright">© 2025 EventLink. All rights reserved.</p>
        <div class="footer-legal"><a href="../imprint">Imprint</a></div>
      </div>
    </div>
  </footer>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
</body>
</html>

