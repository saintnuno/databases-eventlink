<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if ($eventId <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid event_id']);
  exit;
}

try {
  $stmt = $pdo->prepare("
    SELECT
      s.seat_id,
      s.seat_label,
      s.section,
      s.row_label,
      v.name AS venue
    FROM Event e
    JOIN Venue v ON v.venue_id = e.venue_id
    JOIN Seat s  ON s.venue_id = v.venue_id
    LEFT JOIN Ticket t
      ON t.event_id = e.event_id
     AND t.seat_id  = s.seat_id
    WHERE e.event_id = :event_id
      AND t.ticket_id IS NULL
    ORDER BY s.seat_label ASC
  ");
  $stmt->execute([':event_id' => $eventId]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Server error']);
}
