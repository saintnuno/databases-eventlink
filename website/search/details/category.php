<?php
require_once __DIR__ . '/../../utils/db.php';

$category = $_GET['category'] ?? null;
$start = $_GET['start'] ?? '2025-01-01';
$end   = $_GET['end'] ?? '2025-12-31';

if (!$category) {
  echo "No category selected.";
  exit;
}

$query = "
SELECT e.title, e.start_at, COUNT(t.ticket_id) AS total_tickets
FROM Event e
LEFT JOIN Ticket t ON e.event_id = t.event_id
WHERE e.category = :category AND e.start_at BETWEEN :start AND :end
GROUP BY e.event_id, e.title, e.start_at
ORDER BY e.start_at ASC;
";

$stmt = $pdo->prepare($query);
$stmt->execute(['category' => $category, 'start' => $start, 'end' => $end]);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Category Details</title>
</head>
<body>
  <h2>Category: <?= htmlspecialchars($category) ?></h2>
  <table border="1" cellpadding="6">
    <tr>
      <th>Event</th>
      <th>Date</th>
      <th>Total Tickets</th>
    </tr>
    <?php foreach ($events as $e): ?>
      <tr>
        <td><?= htmlspecialchars($e['title']) ?></td>
        <td><?= htmlspecialchars($e['start_at']) ?></td>
        <td><?= htmlspecialchars($e['total_tickets']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <br>
  <a href="../results/category.php?start=<?= $start ?>&end=<?= $end ?>">Back</a>
</body>
</html>
