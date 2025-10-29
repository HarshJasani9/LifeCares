<?php
declare(strict_types=1);
require __DIR__.'/config.php';
require __DIR__.'/guard.php';
require_role('patient');
$uid = (int)$_SESSION['uid'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $doctor = (int)($_POST['doctor_id'] ?? 0);
  $date   = $_POST['date'] ?? '';
  $time   = $_POST['time'] ?? '';
  if ($doctor && $date && $time) {
    $pdo->prepare("INSERT INTO appointments (patient_id,doctor_id,appt_date,appt_time,status) VALUES (?,?,?,?, 'requested')")
        ->execute([$uid,$doctor,$date,$time]);
    header('Location: patient.php'); exit;
  }
}
header('Location: patient.php');
