<?php
require_once __DIR__ . '/../utils/paths.php';
require_once UTILS_DIR . '/db.php';
require_once UTILS_DIR . '/auth.php';
require_login();

$name  = trim($_POST['name'] ?? '');
$addr  = trim($_POST['address'] ?? '');
$scheme = trim($_POST['seating_scheme'] ?? '');

if ($name === '') {
  $ok = false;
  $message = "Venue name is required.";
  include __DIR__ . '/../feedback/index.php';
  exit;
}

try {
  $stmt = $pdo->prepare("
    INSERT INTO Venue (name, address, seating_scheme)
    VALUES (:name, :address, :seating_scheme)
  ");
  $stmt->execute([
    ':name' => $name,
    ':address' => ($addr === '' ? null : $addr),
    ':seating_scheme' => ($scheme === '' ? null : $scheme),
  ]);
  $newId = $pdo->lastInsertId();

  $ok = true;
  $message = "Venue #{$newId} (“" . $name . "”) was created successfully.";
} catch (Throwable $e) {
  $ok = false;

  $message = "Failed to create venue. Please check inputs and try again.";
}

include __DIR__ . '/../feedback/index.php';
