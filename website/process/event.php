<?php
require_once __DIR__ . '/../utils/paths.php';
require_once UTILS_DIR . '/db.php';
require_once UTILS_DIR . '/auth.php';
require_login();

$title   = trim($_POST['title'] ?? '');
$cat     = trim($_POST['category'] ?? '');
$venueId = (int)($_POST['venue_id'] ?? 0);
$start   = trim($_POST['start_at'] ?? '');
$desc    = trim($_POST['description'] ?? '');
$img     = trim($_POST['img_url'] ?? '');
$status  = trim($_POST['status'] ?? '');

if ($title === '' || $start === '' || $status === '' || $venueId <= 0) {
  $ok = false;
  $message = 'Title, start time, status, and venue are required.';
  include __DIR__ . '/../feedback/index.php';
  exit;
}

try {
  $stmt = $pdo->prepare("
    INSERT INTO Event (title, category, description, venue_id, img_url, start_at, status)
    VALUES (:title, :category, :description, :venue_id, :img_url, :start_at, :status)
  ");
  $stmt->execute([
    ':title'       => $title,
    ':category'    => ($cat === '' ? null : $cat),
    ':description' => ($desc === '' ? null : $desc),
    ':venue_id'    => $venueId,
    ':img_url'     => ($img === '' ? null : $img),
    ':start_at'    => $start,
    ':status'      => $status,
  ]);
  $id = $pdo->lastInsertId();
  $ok = true;
  $message = "Event #{$id} (“{$title}”) was created successfully.";
} catch (Throwable $e) {
  $ok = false;
  $message = 'Failed to create event. Please verify inputs and try again.';
}

include __DIR__ . '/../feedback/index.php';
