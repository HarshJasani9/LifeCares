<?php
// session.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',     // localhost
    'secure'   => false,  // set true if serving over HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  session_start();
}
