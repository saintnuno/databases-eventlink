<?php
require_once __DIR__ . '/../../utils/db.php';

$query = "
SELECT 
  e.event_id,
  e.title,
  e.start_at,
  COUNT(w.waitlist_id) AS waitlist_size
FROM Event e
LEFT JOIN Waitlist w ON e.event_id = w.event_id
WHERE e.status = 'SOLD_OUT'
GROUP BY e.event_id, e.title, e.start_at
ORDER BY e.start_at ASC;
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Sold-Out Events - Results</title>
</head>
<body>
  <h2>Sold-Out Events</h2>
  <table border="1" cellpadding="6">
    <tr>
      <th>Event</th>
      <th>Date</th>
      <th>Waitlist Size</th>
    </tr>
    <?php foreach ($events as $event): ?>
      <tr>
        <td><a href="../details/event_sold.php?event_id=<?= $event['event_id'] ?>"><?= htmlspecialchars($event['title']) ?></a></td>
        <td><?= htmlspecialchars($event['start_at']) ?></td>
        <td><?= htmlspecialchars($event['waitlist_size']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <br>
  <a href="../event_sold.php">Back</a>
</body>
</html>
