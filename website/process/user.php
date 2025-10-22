<?php
require_once __DIR__ . '/../utils/db.php';

$name   = trim($_POST['name'] ?? '');
$email  = trim($_POST['email'] ?? '');
$role   = trim($_POST['role'] ?? 'CUSTOMER');
$status = trim($_POST['status'] ?? 'ACTIVE');
$pass   = trim($_POST['password'] ?? '');

if ($name === '' || $email === '' || $role === '' || $status === '') {
  $ok = false;
  $message = 'All required fields must be provided.';
  include __DIR__ . '/../feedback/index.php';
  exit;
}

try {
  $hashed = password_hash($pass !== '' ? $pass : bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("
    INSERT INTO User (email, password, name, role, status)
    VALUES (:email, :password, :name, :role, :status)
  ");
  $stmt->execute([
    ':email'    => $email,
    ':password' => $hashed,
    ':name'     => $name,
    ':role'     => $role,
    ':status'   => $status,
  ]);
  $newId = $pdo->lastInsertId();
  $ok = true;
  $message = "User #{$newId} (“{$name}”) was created successfully.";
} catch (Throwable $e) {
  $ok = false;
  $message = 'Failed to create user. Please check your inputs. The email you provided might already be in use.';
}

include __DIR__ . '/../feedback/index.php';
