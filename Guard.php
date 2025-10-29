<?php
declare(strict_types=1);
require __DIR__.'/session.php';

function require_login(): void {
  if (empty($_SESSION['uid'])) {
    header('Location: auth.html?mode=signin'); exit;
  }
}
function require_role(string $role): void {
  require_login();
  if (($_SESSION['role'] ?? '') !== $role) {
    header('Location: auth.html?mode=signin'); exit;
  }
}
function require_any(array $roles): void {
  require_login();
  if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
    header('Location: auth.html?mode=signin'); exit;
  }
}
