<?php require_once __DIR__ . '/../utils/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sold-Out Events</title>
</head>
<body>
  <h2>Sold-Out Events with Waitlist Size</h2>
  <form method="get" action="results/event_sold.php">
    <label>Show all sold-out events that have people on the waitlist:</label><br><br>
    <button type="submit">View Results</button>
  </form>
</body>
</html>
