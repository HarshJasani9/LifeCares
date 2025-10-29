<?php
// helpers.php
declare(strict_types=1);

function redirect(string $url): never {
  header("Location: {$url}");
  exit;
}

function json_ok(array $data = []): never {
  header('Content-Type: application/json');
  echo json_encode(['ok' => true] + $data);
  exit;
}

function json_error(string $message, int $code = 400): never {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode(['ok' => false, 'error' => $message]);
  exit;
}
