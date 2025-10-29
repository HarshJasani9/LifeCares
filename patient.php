<?php
declare(strict_types=1);
require __DIR__.'/config.php';
require __DIR__.'/guard.php';
require_role('patient');
$uid = (int)$_SESSION['uid'];

/* Fetch core data */
$appts = $pdo->prepare("
  SELECT a.*, u.name AS doctor_name 
  FROM appointments a 
  JOIN users u ON u.id=a.doctor_id 
  WHERE a.patient_id=? 
  ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT 10");
$appts->execute([$uid]);
$appts = $appts->fetchAll();

$meds = $pdo->prepare("
  SELECT m.*, u.name AS doctor_name 
  FROM medications m 
  LEFT JOIN users u ON u.id=m.doctor_id 
  WHERE m.patient_id=? 
  ORDER BY m.start_date DESC LIMIT 10");
$meds->execute([$uid]);
$meds = $meds->fetchAll();

$recs = $pdo->prepare("
  SELECT r.*, u.name AS doctor_name 
  FROM medical_records r 
  LEFT JOIN users u ON u.id=r.doctor_id 
  WHERE r.patient_id=? 
  ORDER BY r.record_date DESC LIMIT 10");
$recs->execute([$uid]);
$recs = $recs->fetchAll();

/* Optional tables (render if available) */
$lab=$rx=$msgs=[]; $profile=[];
try{ $q=$pdo->prepare("SELECT id,test_name,report_date,status,file_path FROM lab_reports WHERE patient_id=? ORDER BY report_date DESC LIMIT 10"); $q->execute([$uid]); $lab=$q->fetchAll(); }catch(Throwable $e){}
try{ $q=$pdo->prepare("SELECT id,drug_name,dosage,frequency,refills_left,last_filled_at FROM prescriptions WHERE patient_id=? ORDER BY last_filled_at DESC NULLS LAST, id DESC LIMIT 10"); $q->execute([$uid]); $rx=$q->fetchAll(); }catch(Throwable $e){}
try{ $q=$pdo->prepare("SELECT m.id, m.subject, m.created_at, u.name AS from_name FROM messages m JOIN users u ON u.id=m.from_user_id WHERE m.to_user_id=? ORDER BY m.created_at DESC LIMIT 10"); $q->execute([$uid]); $msgs=$q->fetchAll(); }catch(Throwable $e){}
try{ $q=$pdo->prepare("SELECT p.*, u.email, u.name FROM patient_profiles p JOIN users u ON u.id=p.user_id WHERE p.user_id=? LIMIT 1"); $q->execute([$uid]); $profile=$q->fetch() ?: []; }catch(Throwable $e){}

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$name = $profile['name'] ?? $_SESSION['name'];
$email = $profile['email'] ?? ($_SESSION['email'] ?? '');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Patient Profile — Life Cares</title>
<link rel="stylesheet" href="styles.css">
<style>
/* Page-scoped theme (white + light sky blue) */
:root{
  --sky:#eaf4ff;
  --sky-strong:#d9ecff;
  --ink:#0b1624;
  --sub:#5e6b7a;
  --brand:#1E88E5;
  --ok:#16a34a;
  --warn:#f59e0b;
  --card:#ffffff;
  --border:rgba(16,43,80,.12);
  --shadow:0 10px 22px rgba(16,43,80,.10);
}
body{ background: linear-gradient(180deg, var(--sky), #f6fbff 45%, #ffffff 100%); }

/* Layout */
.pp-container{ max-width:1160px; margin:28px auto; padding:0 16px; }
.pp-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
.pp-title{ font-size:28px; font-weight:900; letter-spacing:.2px; color:var(--ink); }
.pp-actions{ display:flex; gap:8px; }
.pp-btn{ padding:10px 14px; border-radius:10px; border:1px solid #cfe0f5; background:#f7fbff; color:#0a1522; font-weight:700; font-size:12.5px; text-decoration:none; }
.pp-btn.solid{ background:var(--brand); color:#fff; border-color:transparent; }
.pp-grid{ display:grid; gap:16px; grid-template-columns: 320px 1fr; }

/* Left profile card */
.card{ background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow); }
.card-pad{ padding:16px; }
.profile-card .avatar{
  width:108px; height:108px; border-radius:999px; display:grid; place-items:center;
  background:linear-gradient(180deg, #e3f0ff,#d9ecff); color:#1b4b84; font-size:36px; font-weight:900;
  border:1px solid #cfe0f5; margin:8px auto 10px;
}
.profile-card .pname{ text-align:center; font-weight:900; font-size:18px; }
.profile-card .pid{ text-align:center; color:var(--sub); font-size:13px; margin-bottom:10px; }
.kv{ display:grid; grid-template-columns:94px 1fr; gap:6px 10px; font-size:13.5px; color:var(--ink); }
.kv .k{ color:var(--sub); }
.sep{ height:1px; background:var(--border); margin:12px 0; }
.emg{ background:linear-gradient(180deg,#fff,#f7fbff); border:1px solid #e3eefb; border-radius:12px; padding:10px 12px; color:#9a1f2f; font-weight:800; }

/* Right column sections */
.sec{ background:var(--card); border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow); }
.sec h3{ margin:0 0 10px; font-size:16px; font-weight:900; color:var(--ink); }
.sec-pad{ padding:14px; }

/* Medical summary tiles */
.msum{ display:grid; gap:10px; grid-template-columns: repeat(3,minmax(180px,1fr)); }
.tile{ border:1px solid var(--border); border-radius:12px; padding:12px; }
.tile .tt{ font-weight:900; font-size:12px; letter-spacing:.25px; text-transform:uppercase; margin-bottom:6px; color:#1b4b84; }
.tile.yellow{ background:#fff9e8; border-color:#fde6b1; }
.tile.purple{ background:#efe8ff; border-color:#dfcffc; }
.tile.red{ background:#ffe9ea; border-color:#ffcfd3; }

/* Appointment */
.appt{ display:flex; gap:12px; align-items:center; border:1px solid var(--border); background:linear-gradient(180deg,#fff,#f8fbff); border-radius:12px; padding:12px; }
.appt .cal{ width:38px; height:38px; border-radius:10px; background:#eaf3ff; border:1px solid #d7e6fb; display:grid; place-items:center; font-weight:900; color:#1b4b84; }
.badge{ display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; border:1px solid #cfe0f5; background:#eef6ff; color:#1b4b84; }

/* Medications table */
.table{ width:100%; border-collapse:separate; border-spacing:0; overflow:hidden; border-radius:12px; border:1px solid var(--border); }
.table th, .table td{ padding:10px 12px; font-size:13.5px; }
.table thead th{ background:#f1f7ff; color:#1b4b84; text-align:left; font-weight:900; }
.table tbody tr{ background:#fff; }
.table tbody tr+tr td{ border-top:1px solid #eef3fb; }

/* Simple list rows */
.list{ display:grid; gap:10px; }
.row{ display:flex; justify-content:space-between; align-items:center; border:1px solid var(--border); border-radius:12px; padding:10px 12px; background:#fff; }
.meta{ color:var(--sub); font-size:13px; }
.link{ color:var(--brand); text-decoration:none; }

/* Services rail (matches earlier creative tiles) */
.svc-rail{ display:grid; gap:14px; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); }
.svc{ display:flex; flex-direction:column; gap:8px; padding:16px 14px; background:#fff; border:1px solid var(--border); border-radius:16px; box-shadow:var(--shadow); text-decoration:none; color:var(--ink); }
.svc .t{ font-weight:900; }
.svc .sub{ color:var(--sub); font-size:12.5px; }

/* Responsive */
@media (max-width: 980px){ .pp-grid{ grid-template-columns:1fr; } }
/* SVG image tile styling (rounded cards with thin borders) */
.svc-grid{
  display:grid;
  gap:14px;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
}

.svc-card{
  display:flex; flex-direction:column; align-items:center; justify-content:space-between;
  height:160px; padding:16px 12px;
  border-radius:18px;
  background:#fff;
  border:1px solid rgba(16,43,80,.16);
  box-shadow:0 6px 16px rgba(16,43,80,.08);
  text-decoration:none; color:#173b5c;
  transition: transform .16s ease, box-shadow .2s ease, border-color .2s ease, background .2s ease;
}
.svc-card:hover{
  transform: translateY(-3px);
  border-color: rgba(16,43,80,.22);
  box-shadow:0 12px 28px rgba(16,43,80,.14);
}
.svc-card:focus-visible{ outline:2px solid #1E88E5; outline-offset:2px; }

.svc-ico{
  width:64px; height:64px;
  border-radius:16px;
  background:#f6fbff;
  border:1px solid #d8e6f6;
  box-shadow: inset 0 1px 0 rgba(255,255,255,.9);
  display:grid; place-items:center;
}
.svc-ico img{
  max-width:36px; max-height:36px;
  display:block;
}

.svc-title{
  text-align:center; font-weight:800; letter-spacing:.15px; line-height:1.25;
  min-height:38px; display:flex; align-items:center; justify-content:center;
  color:#173b5c;
}

/* Darker text on hover for subtle emphasis */
.svc-card:hover .svc-title{ color:#102f4a; }

/* Mobile tweaks */
@media (max-width:520px){
  .svc-card{ height:148px; }
  .svc-ico{ width:58px; height:58px; }
  .svc-ico img{ max-width:32px; max-height:32px; }
}


</style>
</head>
<body>

<header class="site-header">
  <nav class="nav container">
    <a class="brand" href="index.html"><span class="brand-dot"></span>Life Cares</a>
    <ul class="menu">
      <li><a href="patient.php" class="active">Dashboard</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>
</header>

<main class="pp-container">
  <div class="pp-header">
    <div class="pp-title">Patient Profile</div>
    <div class="pp-actions">
      <a class="pp-btn" href="print_summary.php" target="_blank" rel="noopener">Print Summary</a>
      <a class="pp-btn solid" href="edit_profile.php">Edit Profile</a>
    </div>
  </div>

  <div class="pp-grid">
    <!-- Left: Profile card -->
    <aside class="card profile-card card-pad">
      <div class="avatar"><?php
        $initials = '';
        $parts = preg_split('/\s+/', trim($name));
        if ($parts) { $initials = strtoupper(mb_substr($parts[0],0,1).(isset($parts[1])?mb_substr($parts[1],0,1):'')); }
        echo e($initials ?: 'P');
      ?></div>
      <div class="pname"><?php echo e($name); ?></div>
      <div class="pid">Patient ID: P-<?php echo (int)$uid; ?></div>

      <div class="sep"></div>
      <div class="kv">
        <div class="k">DoB</div><div><?php echo e($profile['dob'] ?? '—'); ?></div>
        <div class="k">Phone</div><div><?php echo e($profile['phone'] ?? '—'); ?></div>
        <div class="k">Email</div><div><?php echo e($email ?: '—'); ?></div>
        <div class="k">Address</div><div><?php echo e($profile['address'] ?? '—'); ?></div>
        <?php if (!empty($profile['insurance_no'])): ?>
          <div class="k">Insurance</div><div><?php echo e($profile['insurance_no']); ?></div>
        <?php endif; ?>
      </div>

      <div class="sep"></div>
      <div class="emg">
        <div style="font-weight:900;margin-bottom:4px;">Emergency Contact</div>
        <div><?php echo e($profile['emergency_name'] ?? '—'); ?><?php
          $rel = $profile['emergency_relation'] ?? '';
          if ($rel) echo ' — '.e($rel);
        ?></div>
        <div><?php echo e($profile['emergency_contact'] ?? ''); ?></div>
      </div>
    </aside>

    <!-- Right: main sections -->
    <section class="col">
      <!-- Medical Summary -->
      <div class="sec sec-pad">
        <h3>Medical Summary</h3>
        <div class="msum">
          <div class="tile yellow">
            <div class="tt">Allergies</div>
            <div><?php echo e($profile['allergies'] ?? 'None recorded'); ?></div>
          </div>
          <div class="tile purple">
            <div class="tt">Conditions</div>
            <div><?php echo e($profile['conditions'] ?? 'None recorded'); ?></div>
          </div>
          <div class="tile red">
            <div class="tt">Blood Type</div>
            <div><?php echo e($profile['blood_type'] ?? '—'); ?></div>
          </div>
        </div>
      </div>

      <!-- Upcoming Appointment (first upcoming if exists) -->
      <div class="sec sec-pad" style="margin-top:14px;">
        <h3>Upcoming Appointments</h3>
        <?php if ($appts): 
          $a = $appts[0]; ?>
          <div class="appt">
            <div class="cal"><?php echo e(date('d', strtotime($a['appt_date']))); ?></div>
            <div>
              <div style="font-weight:900"><?php echo e($a['reason'] ?? 'Consultation'); ?></div>
              <div class="meta">Dr. <?php echo e($a['doctor_name']); ?> • <?php echo e($a['appt_date'].' '.$a['appt_time']); ?></div>
            </div>
            <div style="margin-left:auto"><span class="badge"><?php echo e($a['status']); ?></span></div>
          </div>
          <?php if (count($appts)>1): ?>
            <div class="list" style="margin-top:10px">
              <?php foreach(array_slice($appts,1) as $r): ?>
              <div class="row">
                <div><strong><?php echo e($r['doctor_name']); ?></strong>
                  <div class="meta"><?php echo e($r['appt_date'].' '.$r['appt_time']); ?></div>
                </div>
                <span class="badge"><?php echo e($r['status']); ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <p class="meta">No appointments yet. Use Home Care Services to book a visit.</p>
        <?php endif; ?>
      </div>

      <!-- Current Medications -->
      <div class="sec sec-pad" style="margin-top:14px;">
        <h3>Current Medications</h3>
        <?php if ($meds): ?>
          <table class="table">
            <thead><tr><th>Medication</th><th>Dosage</th><th>Frequency</th><th>Prescriber</th></tr></thead>
            <tbody>
              <?php foreach($meds as $m): ?>
              <tr>
                <td><?php echo e($m['drug_name']); ?></td>
                <td><?php echo e($m['dosage']); ?></td>
                <td><?php echo e($m['frequency']); ?></td>
                <td><?php echo e($m['doctor_name'] ?? '—'); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="meta">No medications found.</p>
        <?php endif; ?>
      </div>

      <!-- Lab Reports -->
      <div class="sec sec-pad" style="margin-top:14px;">
        <h3>Lab Reports</h3>
        <?php if ($lab): ?>
        <div class="list">
          <?php foreach($lab as $lr): ?>
            <div class="row">
              <div>
                <strong><?php echo e($lr['test_name']); ?></strong>
                <div class="meta"><?php echo e($lr['report_date']); ?> • <span class="badge"><?php echo e($lr['status']); ?></span></div>
              </div>
              <div>
                <?php if (!empty($lr['file_path'])): ?>
                  <a class="link" href="<?php echo e($lr['file_path']); ?>" target="_blank" rel="noopener">Download</a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <p class="meta">No lab reports yet.</p>
        <?php endif; ?>
      </div>

      <!-- Prescriptions & Refills -->
      <div class="sec sec-pad" style="margin-top:14px;">
        <h3>Prescriptions & Refills</h3>
        <?php if ($rx): ?>
        <div class="list">
          <?php foreach($rx as $r): ?>
            <div class="row">
              <div>
                <strong><?php echo e($r['drug_name']); ?></strong>
                <div class="meta">
                  <?php echo e($r['dosage'].' • '.$r['frequency']); ?>
                  <?php if($r['refills_left']!==null): ?> • Refills left: <?php echo (int)$r['refills_left']; ?><?php endif; ?>
                  <?php if($r['last_filled_at']): ?> • Last filled: <?php echo e($r['last_filled_at']); ?><?php endif; ?>
                </div>
              </div>
              <form method="post" action="request_refill.php" style="margin:0">
                <input type="hidden" name="rx_id" value="<?php echo (int)$r['id']; ?>">
                <button class="pp-btn" type="submit">Request refill</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <p class="meta">No prescriptions listed.</p>
        <?php endif; ?>
      </div>

      <!-- Medical Records -->
      <div class="sec sec-pad" style="margin-top:14px;">
        <h3>Medical Records</h3>
        <?php if ($recs): ?>
          <div class="list">
            <?php foreach($recs as $rec): ?>
            <div class="row">
              <div>
                <strong><?php echo e($rec['record_date']); ?></strong>
                <div class="meta">By: <?php echo e($rec['doctor_name'] ?? 'Self'); ?></div>
                <div class="meta" style="margin-top:4px;"><?php echo nl2br(e($rec['diagnosis'] ?? '')); ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="meta">No records yet.</p>
        <?php endif; ?>
      </div>

      <!-- Messages -->
      <div class="sec sec-pad" style="margin-top:14px;">
        <h3>Messages</h3>
        <?php if ($msgs): ?>
          <div class="list">
            <?php foreach($msgs as $m): ?>
            <div class="row">
              <div>
                <strong><?php echo e($m['subject']); ?></strong>
                <div class="meta">From: <?php echo e($m['from_name']); ?> • <?php echo e($m['created_at']); ?></div>
              </div>
              <a class="link" href="message_view.php?id=<?php echo (int)$m['id']; ?>">Open</a>
            </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="meta">No messages yet.</p>
        <?php endif; ?>
      </div>

      <!-- Home Care Services -->
 <div class="sec sec-pad" style="margin-top:14px;">
  <h3>Home Care Services</h3>
  <div class="svc-grid">
    <a class="svc-card" href="service_request.php?type=lab">
      <div class="svc-ico"><img src="lab_tests.svg" alt="Lab Tests"></div>
      <div class="svc-title">Lab Tests</div>
    </a>

    <a class="svc-card" href="upload_rx.php">
      <div class="svc-ico"><img src="medicine_delivery.svg" alt="Medicine Delivery"></div>
      <div class="svc-title">Medicine Delivery</div>
    </a>

    <a class="svc-card" href="service_request.php?type=physio">
      <div class="svc-ico"><img src="physiotherapy.svg" alt="Physiotherapy"></div>
      <div class="svc-title">Physiotherapy</div>
    </a>

    <a class="svc-card" href="service_request.php?type=critical">
      <div class="svc-ico"><img src="critical_care_icu_services.svg" alt="Critical Care & ICU Services"></div>
      <div class="svc-title">Critical Care & ICU Services</div>
    </a>

    <a class="svc-card" href="service_request.php?type=nursing">
      <div class="svc-ico"><img src="nursing_care.svg" alt="Nursing Care"></div>
      <div class="svc-title">Nursing Care</div>
    </a>

    <a class="svc-card" href="service_request.php?type=attendant">
      <div class="svc-ico"><img src="healthcare_attendants.svg" alt="Healthcare Attendants"></div>
      <div class="svc-title">Healthcare Attendants</div>
    </a>

    <a class="svc-card" href="service_request.php?type=procedure">
      <div class="svc-ico"><img src="nursing_procedure.svg" alt="Nursing Procedure"></div>
      <div class="svc-title">Nursing Procedure</div>
    </a>

    <a class="svc-card" href="book_appointment.php">
      <div class="svc-ico"><img src="doctor_visit.svg" alt="Doctor Visit"></div>
      <div class="svc-title">Doctor Visit</div>
    </a>

    <a class="svc-card" href="service_request.php?type=equipment">
      <div class="svc-ico"><img src="medical_equipment.svg" alt="Medical Equipment"></div>
      <div class="svc-title">Medical Equipment</div>
    </a>
  </div>
</div>


    </section>
  </div>
</main>
</body>
</html>
