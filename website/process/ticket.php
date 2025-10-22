<?php
require_once __DIR__ . '/../utils/db.php';

$eventId = (int)($_POST['event_id'] ?? 0);
$seatId  = (int)($_POST['seat_id'] ?? 0);
$price   = trim($_POST['price'] ?? '');
$status  = trim($_POST['status'] ?? '');
$qr      = trim($_POST['qr_code'] ?? '');

if ($eventId <= 0 || $seatId <= 0 || $price === '' || $status === '') {
  $ok = false;
  $message = 'Event, seat, price, and status are required.';
  include __DIR__ . '/../feedback/index.php';
  exit;
}

try {
  $stmt = $pdo->prepare("
    INSERT INTO Ticket (event_id, seat_id, price, status, qr_code, order_id, hold_expires_at)
    VALUES (:event_id, :seat_id, :price, :status, :qr_code, NULL, NULL)
  ");
  $stmt->execute([
    ':event_id' => $eventId,
    ':seat_id'  => $seatId,
    ':price'    => (int)$price,
    ':status'   => $status,
    ':qr_code'  => ($qr === '' ? null : $qr),
  ]);
  $id = $pdo->lastInsertId();
  $ok = true;
  $message = "Ticket #{$id} was created successfully.";
} catch (Throwable $e) {
  $ok = false;
  $message = 'Failed to create ticket. The seat for the event you chose, or the QR Code might not be unique.';
}

include __DIR__ . '/../feedback/index.php';
