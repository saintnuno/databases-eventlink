<?php require_once __DIR__ . '/../utils/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Category Summary</title>
</head>
<body>
  <h2>Category Summary Between Dates</h2>
  <form method="get" action="results/category.php">
    <label>Start Date:</label>
    <input type="date" name="start_date" required>
    <br><br>
    <label>End Date:</label>
    <input type="date" name="end_date" required>
    <br><br>
    <button type="submit">View Summary</button>
  </form>
</body>
</html>
