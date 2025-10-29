<?php
// login.php (role-aware)
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/session.php';
require __DIR__ . '/helpers.php'; // expects json_ok/json_error helpers

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  json_error('Invalid method', 405);
}

$email = trim($_POST['email'] ?? '');
$pass  = trim($_POST['password'] ?? '');

if ($email === '' || $pass === '') {
  json_error('Email and password are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_error('Invalid email.');
}

try {
  // Fetch role as well
  $stmt = $pdo->prepare('SELECT id, name, password_hash, role FROM users WHERE email = ? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if (!$user || !password_verify($pass, $user['password_hash'])) {
    json_error('Invalid email or password.', 401);
  }

  // Optional rehash
  // if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) { ... }

  // Session
  $_SESSION['uid']   = (int)$user['id'];
  $_SESSION['email'] = $email;
  $_SESSION['name']  = $user['name'];
  $_SESSION['role']  = $user['role'] ?? 'patient';

  // Role-based redirect
  $to = 'dashboard.php'; // fallback
  if ($user['role'] === 'admin')  $to = 'admin.php';
  if ($user['role'] === 'doctor') $to = 'doctor.php';
  if ($user['role'] === 'patient')$to = 'patient.php';

  json_ok(['redirect' => $to]);
} catch (Throwable $e) {
  json_error('Server error. Please try again later.', 500);
}
