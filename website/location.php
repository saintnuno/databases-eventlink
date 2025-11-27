<?php
require_once __DIR__ . '/utils/paths.php';
require_once UTILS_DIR . '/auth.php';
require_login();

$basePrefix = '.';
$activeNav = 'location';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Your Location - EventLink</title>
  <link rel="stylesheet" href="./css/style.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    #map {
      height: 500px;
      width: 100%;
      border-radius: 12px;
      border: 2px solid #e5e7eb;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .location-info {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      padding: 20px;
      margin-top: 20px;
    }
    .location-info h3 {
      margin: 0 0 12px 0;
      color: #1f2937;
      font-size: 18px;
      font-weight: 700;
    }
    .location-info p {
      margin: 8px 0;
      color: #4b5563;
      font-size: 14px;
    }
    .location-info strong {
      color: #1f2937;
      font-weight: 600;
    }
    .loading-spinner {
      text-align: center;
      padding: 40px;
      color: #6b7280;
    }
    .error-message {
      background: #fee;
      color: #b91c1c;
      padding: 16px;
      border-radius: 8px;
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <?php require_once LAYOUT_DIR . '/navbar.php'; ?>

  <section class="hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
      <h1 class="hero-title">Your Location</h1>
      <p class="hero-subtitle">See where you're accessing EventLink from</p>
    </div>
  </section>

  <section class="events-section">
    <div class="container">
      <div class="section-header">
        <div>
          <h2 class="section-title">Geo-Location Lookup</h2>
          <p class="section-subtitle">Based on your IP address</p>
        </div>
      </div>

      <div id="loading" class="loading-spinner">
        <p style="font-size: 18px; font-weight: 600;">🌍 Detecting your location...</p>
      </div>

      <div id="error" class="error-message" style="display: none;"></div>

      <div id="map-container" style="display: none;">
        <div id="map"></div>
        
        <div class="location-info" id="location-info">
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
  
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      fetch('https://ipinfo.io/json')
        .then(response => response.json())
        .then(data => {
          document.getElementById('loading').style.display = 'none';
          
          if (data.loc) {
            document.getElementById('map-container').style.display = 'block';
            
            var coords = data.loc.split(',');
            var lat = parseFloat(coords[0]);
            var lon = parseFloat(coords[1]);
            
            var map = L.map('map').setView([lat, lon], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
              maxZoom: 19
            }).addTo(map);
            
            var marker = L.marker([lat, lon]).addTo(map);
            
            marker.bindPopup('<div style="text-align: center;"><strong>Your IP Address</strong><br>' + data.ip + '</div>').openPopup();
            
            document.getElementById('location-info').innerHTML = `
              <h3>Location Details</h3>
              <p><strong>IP Address:</strong> ${data.ip}</p>
              <p><strong>City:</strong> ${data.city || 'Unknown'}</p>
              <p><strong>Region:</strong> ${data.region || 'Unknown'}</p>
              <p><strong>Country:</strong> ${data.country || 'Unknown'}</p>
              <p><strong>Coordinates:</strong> ${lat.toFixed(6)}, ${lon.toFixed(6)}</p>
              <p><strong>Organization:</strong> ${data.org || 'Unknown'}</p>
            `;
          } else {
            document.getElementById('error').style.display = 'block';
            document.getElementById('error').innerHTML = '<strong>Error:</strong> Location data not available';
          }
        })
        .catch(error => {
          document.getElementById('loading').style.display = 'none';
          document.getElementById('error').style.display = 'block';
          document.getElementById('error').innerHTML = '<strong>Error:</strong> Failed to fetch location data. ' + error.message;
        });
    });
  </script>
</body>
</html>
