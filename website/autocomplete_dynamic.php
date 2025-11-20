<?php
require_once __DIR__ . '/utils/paths.php';
require_once UTILS_DIR . '/auth.php';
require_login();

$basePrefix = '.';
$activeNav = 'search';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Autocomplete Dynamic Demo (Bonus)</title>
  <link rel="stylesheet" href="./css/style.css" />
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/smoothness/jquery-ui.css">
</head>
<body>
  <?php require_once LAYOUT_DIR . '/navbar.php'; ?>

  <section class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <h1 class="hero-title">Dynamic Autocomplete</h1>
      <p class="hero-subtitle">Sends request after each keystroke for real-time server-side filtering.</p>
    </div>
  </section>

  <section class="events-section">
    <div class="container">
      <div class="section-header">
        <div>
          <h2 class="section-title">Search Demo</h2>
          <p class="section-subtitle">Type to see filtered suggestions from server in real-time</p>
        </div>
      </div>

      <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;">
        <div style="display:grid;grid-template-columns:1fr;gap:16px;">
          <label>
            <span style="display:block;font-weight:700;margin-bottom:6px;">Search Events </span>
            <input type="text" id="event-search" placeholder="e.g., rock, concert, sports" class="input" style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
          </label>
          
          <label>
            <span style="display:block;font-weight:700;margin-bottom:6px;">Search Categories </span>
            <input type="text" id="category-search" placeholder="e.g., music, theatre" class="input" style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
          </label>

          <label>
            <span style="display:block;font-weight:700;margin-bottom:6px;">Search All (Events, Categories, Venues)</span>
            <input type="text" id="all-search" placeholder="Type anything..." class="input" style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:10px;">
          </label>

          <div id="result" style="margin-top:16px;padding:12px;background:#f9fafb;border-radius:8px;min-height:50px;">
            <p style="color:#6b7280;">Selected value will appear here</p>
          </div>
          
          <div id="status" style="padding:8px;background:#eff6ff;border-radius:6px;font-size:14px;color:#1e40af;">
            <strong>Status:</strong> Ready to search
          </div>
        </div>

        <div style="margin-top:16px;display:flex;gap:12px;">
          <a href="./autocomplete_demo.php" class="btn-ghost">Static Demo</a>
          <a href="./autocomplete_server.php" class="btn-ghost">Server Demo</a>
          <a href="./search/" class="btn-ghost">Go to Search</a>
        </div>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-column">
          <div class="footer-brand">
            <img class="logo-icon" src="./img/logo_main.png" alt="EventLink Logo" />
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
        <div class="footer-legal"><a href="./imprint">Imprint</a></div>
      </div>
    </div>
  </footer>

  <script src="https://unpkg.com/lucide@latest"></script>
  <script>lucide.createIcons();</script>
  
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
  
  <script>
    $(document).ready(function() {
      $("#event-search").autocomplete({
        source: function(request, response) {
          $("#status").html("<strong>Status:</strong> Searching for '" + request.term + "'...");
          
          $.ajax({
            url: "./utils/autocomplete_api.php",
            dataType: "json",
            data: {
              term: request.term,
              type: "events"
            },
            success: function(data) {
              $("#status").html("<strong>Status:</strong> Found " + data.length + " results");
              response(data);
            },
            error: function() {
              $("#status").html("<strong>Status:</strong> Error fetching results");
              response([]);
            }
          });
        },
        minLength: 2,
        delay: 300,
        select: function(event, ui) {
          $("#result").html("<p style='color:#059669;font-weight:600;'>You selected: " + ui.item.value + " (Type: " + ui.item.type + ")</p>");
        }
      });
      
      $("#category-search").autocomplete({
        source: function(request, response) {
          $("#status").html("<strong>Status:</strong> Searching categories for '" + request.term + "'...");
          
          $.ajax({
            url: "./utils/autocomplete_api.php",
            dataType: "json",
            data: {
              term: request.term,
              type: "categories"
            },
            success: function(data) {
              $("#status").html("<strong>Status:</strong> Found " + data.length + " categories");
              response(data);
            },
            error: function() {
              $("#status").html("<strong>Status:</strong> Error fetching results");
              response([]);
            }
          });
        },
        minLength: 1,
        delay: 300,
        select: function(event, ui) {
          $("#result").html("<p style='color:#059669;font-weight:600;'>You selected category: " + ui.item.value + "</p>");
        }
      });
      
      $("#all-search").autocomplete({
        source: function(request, response) {
          $("#status").html("<strong>Status:</strong> Searching all for '" + request.term + "'...");
          
          $.ajax({
            url: "./utils/autocomplete_api.php",
            dataType: "json",
            data: {
              term: request.term,
              type: "all"
            },
            success: function(data) {
              $("#status").html("<strong>Status:</strong> Found " + data.length + " results across all types");
              response(data);
            },
            error: function() {
              $("#status").html("<strong>Status:</strong> Error fetching results");
              response([]);
            }
          });
        },
        minLength: 2,
        delay: 300,
        select: function(event, ui) {
          $("#result").html("<p style='color:#059669;font-weight:600;'>You selected: " + ui.item.value + " (Type: " + ui.item.type + ")</p>");
        }
      });
    });
  </script>
</body>
</html>
