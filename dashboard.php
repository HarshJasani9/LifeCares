<?php
// dashboard.php
declare(strict_types=1);
require __DIR__ . '/session.php';

if (empty($_SESSION['uid'])) {
  header('Location: auth.html?mode=signin');
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard — Life Cares</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <header class="site-header">
    <nav class="nav container" aria-label="Primary">
      <a class="brand" href="index.html"><span class="brand-dot"></span>Life Cares</a>
      <ul class="menu">
        <li><a href="index.html#home">Home</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </nav>
  </header>
  <main class="section">
    <div class="container">
      <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
      <p class="section-sub">Session is active and your account is connected to the database.</p>
    </div>
  </main>
</body>
</html>
