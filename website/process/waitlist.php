<?php
require_once __DIR__ . '/../utils/db.php';

$userId = (int)($_POST['user_id'] ?? 0);
$eventId = (int)($_POST['event_id'] ?? 0);
$seats = trim($_POST['seats_requested'] ?? '');
$status = trim($_POST['status'] ?? '');
$expires = trim($_POST['expires_at'] ?? '');

if ($userId <= 0 || $eventId <= 0 || $seats === '' || $status === '') {
  $ok = false;
  $message = 'User, event, seats requested, and status are required.';
  include __DIR__ . '/../feedback/index.php';
  exit;
}

try {
  $stmt = $pdo->prepare("
    INSERT INTO Waitlist (user_id, event_id, seats_requested, status, expires_at)
    VALUES (:user_id, :event_id, :seats_requested, :status, :expires_at)
  ");
  $stmt->execute([
    ':user_id'        => $userId,
    ':event_id'       => $eventId,
    ':seats_requested'=> (int)$seats,
    ':status'         => $status,
    ':expires_at'     => ($expires === '' ? null : $expires),
  ]);
  $id = $pdo->lastInsertId();
  $ok = true;
  $message = "Waitlist entry #{$id} was created successfully.";
} catch (Throwable $e) {
  $ok = false;
  $message = 'Failed to create waitlist entry. Please verify inputs and try again.';
}

include __DIR__ . '/../feedback/index.php';
