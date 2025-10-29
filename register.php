<?php
declare(strict_types=1);

/* ================= Debug mode =================
   Set to true ONLY while fixing errors, then false. */
const DEBUG = true;

if (DEBUG) {
  ini_set('display_errors', '1');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '0');
  error_reporting(E_ALL);
}

/* ================ Includes (must be silent) ================ */
require __DIR__ . '/config.php';   // defines $pdo (PDO)
require __DIR__ . '/session.php';  // starts session

/* ================ JSON helpers ================ */
function send_json(array $payload, int $code = 200): never {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}
function ok(array $data = [], int $code = 200): never {
  send_json(['ok' => true] + $data, $code);
}
function fail(string $message, int $code = 400, array $extra = []): never {
  send_json(['ok' => false, 'error' => $message] + $extra, $code);
}

/* ================ Method guard ================ */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  fail('Invalid method', 405);
}

/* ================ Inputs ================ */
$name   = trim($_POST['name'] ?? '');
$email  = trim($_POST['email'] ?? '');
$pass   = trim($_POST['password'] ?? '');
$roleIn = trim($_POST['role'] ?? 'patient'); // new: role from form

/* ================ Validation ================ */
if ($name === '' || $email === '' || $pass === '') {
  fail('All fields are required.', 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  fail('Invalid email format.', 400);
}
if (strlen($pass) < 8) {
  fail('Password must be at least 8 characters.', 400);
}

/* Normalize and restrict role: only patient/doctor allowed via self-signup */
$allowedSignupRoles = ['patient','doctor'];
$role = in_array(strtolower($roleIn), $allowedSignupRoles, true) ? strtolower($roleIn) : 'patient';

/* ================ Insert ================ */
try {
  // 1) Email uniqueness
  $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
  $check->execute([$email]);
  if ($check->fetch()) {
    fail('Email already registered.', 409);
  }

  // 2) Hash + insert user with role
  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $ins  = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
  $ins->execute([$name, $email, $hash, $role]);

  $userId = (int)$pdo->lastInsertId();

  // 3) Create role profile row (optional but recommended)
  if ($role === 'doctor') {
    $pdo->prepare('INSERT INTO doctor_profiles (user_id) VALUES (?)')->execute([$userId]);
  } else { // patient
    $pdo->prepare('INSERT INTO patient_profiles (user_id) VALUES (?)')->execute([$userId]);
  }

  // 4) Session
  $_SESSION['uid']   = $userId;
  $_SESSION['email'] = $email;
  $_SESSION['name']  = $name;
  $_SESSION['role']  = $role;

  // 5) Success redirect by role
  $target = 'dashboard.php';               // fallback
  if ($role === 'admin')  $target = 'admin.php';   // not reachable via self-signup
  if ($role === 'doctor') $target = 'doctor.php';
  if ($role === 'patient')$target = 'patient.php';

  ok(['redirect' => $target], 201);

} catch (Throwable $e) {
  if (DEBUG) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo $e->getMessage();
    exit;
  }
  fail('Server error. Please try again later.', 500);
}
