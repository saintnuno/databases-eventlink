<?php
require_once __DIR__ . '/../../utils/db.php';

$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
  echo "No event selected.";
  exit;
}

$query = "
SELECT e.title, e.start_at, w.user_name
FROM Event e
LEFT JOIN Waitlist w ON e.event_id = w.event_id
WHERE e.event_id = :event_id;
";

$stmt = $pdo->prepare($query);
$stmt->execute(['event_id' => $event_id]);
$details = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Event Details</title>
</head>
<body>
  <h2>Event Details</h2>
  <?php if ($details): ?>
    <h3><?= htmlspecialchars($details[0]['title']) ?></h3>
    <p><strong>Date:</strong> <?= htmlspecialchars($details[0]['start_at']) ?></p>
    <h4>Waitlist:</h4>
    <ul>
      <?php foreach ($details as $d): ?>
        <?php if ($d['user_name']): ?>
          <li><?= htmlspecialchars($d['user_name']) ?></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No details found.</p>
  <?php endif; ?>
  <a href="../results/event_sold.php">Back</a>
</body>
</html>
