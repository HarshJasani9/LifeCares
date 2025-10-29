<?php
declare(strict_types=1);
require __DIR__.'/config.php';
require __DIR__.'/guard.php';
require_role('admin');

/* Stats */
$totPatients = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='patient'")->fetchColumn();
$totDoctors  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='doctor'")->fetchColumn();
$totAppts    = (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();

$recentAppts = $pdo->query("
  SELECT a.id,a.appt_date,a.appt_time,a.status, p.name AS patient_name, d.name AS doctor_name
  FROM appointments a
  JOIN users p ON p.id=a.patient_id
  JOIN users d ON d.id=a.doctor_id
  ORDER BY a.created_at DESC LIMIT 10
")->fetchAll();

$recentUsers = $pdo->query("
  SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC LIMIT 10
")->fetchAll();
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — Life Cares</title>
<link rel="stylesheet" href="styles.css">
</head><body>
<header class="site-header">
  <nav class="nav container"><a class="brand" href="index.html"><span class="brand-dot"></span>Life Cares</a>
    <ul class="menu">
      <li><a href="admin.php" class="active">Dashboard</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>
</header>
<main class="section"><div class="container">
  <h1>Admin control</h1>
  <div class="grid3">
    <div class="card"><h3>Patients</h3><p><?php echo $totPatients; ?></p></div>
    <div class="card"><h3>Doctors</h3><p><?php echo $totDoctors; ?></p></div>
    <div class="card"><h3>Appointments</h3><p><?php echo $totAppts; ?></p></div>
  </div>

  <h2 class="section-title">Recent Appointments</h2>
  <div class="cards">
    <?php foreach ($recentAppts as $a): ?>
    <article class="card">
      <h3>#<?php echo (int)$a['id']; ?> — <?php echo htmlspecialchars($a['status']); ?></h3>
      <p><?php echo htmlspecialchars($a['appt_date'].' '.$a['appt_time']); ?></p>
      <p><?php echo htmlspecialchars($a['patient_name']); ?> → <?php echo htmlspecialchars($a['doctor_name']); ?></p>
    </article>
    <?php endforeach; if (!$recentAppts) echo '<p class="section-sub">No activity yet.</p>'; ?>
  </div>

  <h2 class="section-title">New Users</h2>
  <div class="cards">
    <?php foreach ($recentUsers as $u): ?>
      <article class="card">
        <h3><?php echo htmlspecialchars($u['name']); ?> (<?php echo htmlspecialchars($u['role']); ?>)</h3>
        <p><?php echo htmlspecialchars($u['email']); ?></p>
        <p><?php echo htmlspecialchars($u['created_at']); ?></p>
      </article>
    <?php endforeach; ?>
  </div>
</div></main>
</body></html>
