<?php
require_once __DIR__ . '/../utils/db.php';

$userId  = (int)($_POST['user_id'] ?? 0);
$status  = trim($_POST['status'] ?? '');
$pstatus = trim($_POST['payment_status'] ?? '');
$total   = trim($_POST['total'] ?? '');

if ($userId <= 0 || $status === '' || $pstatus === '' || $total === '') {
  $ok = false;
  $message = 'User, status, payment status, and total are required.';
  include __DIR__ . '/../feedback/index.php';
  exit;
}

try {
  $stmt = $pdo->prepare("
    INSERT INTO `Order` (user_id, status, total, payment_status)
    VALUES (:user_id, :status, :total, :payment_status)
  ");
  $stmt->execute([
    ':user_id'        => $userId,
    ':status'         => $status,
    ':total'          => (int)$total,
    ':payment_status' => $pstatus,
  ]);
  $id = $pdo->lastInsertId();
  $ok = true;
  $message = "Order #{$id} was created successfully.";
} catch (Throwable $e) {
  $ok = false;
  $message = 'Failed to create order. Please verify inputs and try again.';
}

include __DIR__ . '/../feedback/index.php';
