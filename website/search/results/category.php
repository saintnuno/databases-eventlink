<?php
require_once __DIR__ . '/../../utils/db.php';

$start = $_GET['start_date'] ?? '2025-01-01';
$end   = $_GET['end_date'] ?? '2025-12-31';

$query = "
SELECT 
  e.category,
  COUNT(DISTINCT e.event_id) AS total_events,
  COUNT(t.ticket_id) AS total_tickets,
  SUM(CASE WHEN t.status = 'SOLD' THEN 1 ELSE 0 END) AS tickets_sold
FROM Event e
LEFT JOIN Ticket t ON e.event_id = t.event_id
WHERE e.start_at BETWEEN :start AND :end
GROUP BY e.category
ORDER BY total_events DESC;
";

$stmt = $pdo->prepare($query);
$stmt->execute(['start' => $start, 'end' => $end]);
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Category Summary - Results</title>
</head>
<body>
  <h2>Category Summary</h2>
  <table border="1" cellpadding="6">
    <tr>
      <th>Category</th>
      <th>Total Events</th>
      <th>Total Tickets</th>
      <th>Tickets Sold</th>
    </tr>
    <?php foreach ($categories as $cat): ?>
      <tr>
        <td><a href="../details/category.php?category=<?= urlencode($cat['category']) ?>&start=<?= $start ?>&end=<?= $end ?>">
          <?= htmlspecialchars($cat['category']) ?>
        </a></td>
        <td><?= htmlspecialchars($cat['total_events']) ?></td>
        <td><?= htmlspecialchars($cat['total_tickets']) ?></td>
        <td><?= htmlspecialchars($cat['tickets_sold']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <br>
  <a href="../category.php">Back</a>
</body>
</html>
