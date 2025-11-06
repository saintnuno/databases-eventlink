<?php
require_once __DIR__ . '/../utils/paths.php';
require_once UTILS_DIR . '/db.php';
require_once UTILS_DIR . '/auth.php';
require_login();

$venueId = (int)($_POST['venue_id'] ?? 0);
$section = trim($_POST['section'] ?? '');
$row     = trim($_POST['row_label'] ?? '');
$num     = trim($_POST['seat_number'] ?? '');
$label   = trim($_POST['seat_label'] ?? '');

if ($venueId <= 0 || $label === '') {
  $ok = false;
  $message = 'Venue and seat label are required.';
  include __DIR__ . '/../feedback/index.php';
  exit;
}

try {
  $stmt = $pdo->prepare("
    INSERT INTO Seat (venue_id, section, row_label, seat_number, seat_label)
    VALUES (:venue_id, :section, :row_label, :seat_number, :seat_label)
  ");
  $stmt->execute([
    ':venue_id'    => $venueId,
    ':section'     => ($section === '' ? null : $section),
    ':row_label'   => ($row === '' ? null : $row),
    ':seat_number' => ($num === '' ? null : (int)$num),
    ':seat_label'  => $label,
  ]);
  $id = $pdo->lastInsertId();
  $ok = true;
  $message = "Seat #{$id} (“{$label}”) was created successfully.";
} catch (Throwable $e) {
  $ok = false;
  $message = 'Failed to create seat. Check unique (venue, seat_label).';
}

include __DIR__ . '/../feedback/index.php';

