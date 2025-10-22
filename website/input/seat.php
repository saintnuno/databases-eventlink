<?php
require_once __DIR__ . '/../utils/db.php';
$venues = $pdo->query("SELECT venue_id, name FROM Venue ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Seats</title>
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
                <a href="../" class="nav-link">Browse Events</a>
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
            <h1 class="hero-title">Seats</h1>
            <p class="hero-subtitle">Add a new seat</p>
        </div>
    </section>

    <!-- Form -->
    <section class="events-section">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Seat Details</h2>
                    <p class="section-subtitle">Please fill out all required fields</p>
                </div>
            </div>

            <form action="../process/seat.php" method="post"
                style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
                <div style="display:grid;grid-template-columns:1fr;gap:16px;">

                    <label>
                        <span style="display:block;font-weight:700;margin-bottom:6px;">Venue *</span>
                        <select name="venue_id" required class="input"
                            style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
                            <option value="">Select a venue…</option>
                            <?php foreach ($venues as $v): ?>
                                <option value="<?php echo (int) $v['venue_id']; ?>">
                                    <?php echo htmlspecialchars($v['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span style="display:block;font-weight:700;margin-bottom:6px;">Section</span>
                        <input type="text" name="section" placeholder="A / B / C…" class="input"
                            style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
                    </label>

                    <label>
                        <span style="display:block;font-weight:700;margin-bottom:6px;">Row label</span>
                        <input type="text" name="row_label" placeholder="Enter row label..." class="input"
                            style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
                    </label>

                    <label>
                        <span style="display:block;font-weight:700;margin-bottom:6px;">Seat number</span>
                        <input type="number" name="seat_number" min="1" placeholder="1" class="input"
                            style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
                    </label>

                    <label>
                        <span style="display:block;font-weight:700;margin-bottom:6px;">Seat label *</span>
                        <input type="text" name="seat_label" required placeholder="A10" class="input"
                            style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
                    </label>

                </div>

                <div style="margin-top:16px;display:flex;gap:12px;">
                    <button type="submit" class="btn-primary">Save Seat</button>
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

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>

</html>
