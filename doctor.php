<?php
declare(strict_types=1);
require __DIR__.'/config.php';
require __DIR__.'/guard.php';
require_role('doctor');
$uid = (int)($_SESSION['uid'] ?? 0);
$doctorName = $_SESSION['name'] ?? 'Doctor';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Doctor Dashboard — Life Cares</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Doctor Dashboard - White & Sky Blue Theme */
    :root {
      --doc-bg: #f6fbff;
      --doc-surface: #ffffff;
      --doc-card: #ffffff;
      --doc-text: #0b1624;
      --doc-text-secondary: #5e6b7a;
      --doc-accent: #1E88E5;
      --doc-accent-hover: #1976D2;
      --doc-accent-light: #e3f2fd;
      --doc-success: #16a34a;
      --doc-warning: #f59e0b;
      --doc-error: #ef4444;
      --doc-border: rgba(16,43,80,.16);
      --doc-border-light: rgba(16,43,80,.08);
      --doc-shadow: 0 8px 18px rgba(16,43,80,.08);
      --doc-shadow-hover: 0 12px 28px rgba(16,43,80,.14);
    }

    body { 
      background: linear-gradient(180deg, var(--doc-bg), #ffffff 45%); 
      color: var(--doc-text); margin: 0; font-family: var(--font-family-base); 
    }
    
    .dashboard-container { display: flex; min-height: 100vh; }
    
    /* Sidebar */
    .sidebar { 
      width: 300px; background: var(--doc-surface); 
      border-right: 1px solid var(--doc-border);
      position: fixed; height: 100vh; overflow-y: auto; z-index: 100;
      box-shadow: var(--doc-shadow);
    }
    
    .sidebar-content { padding: 24px 20px; display: flex; flex-direction: column; height: 100%; }
    
    /* Doctor Profile */
    .doctor-profile { text-align: center; margin-bottom: 24px; }
    .doctor-avatar { 
      width: 80px; height: 80px; border-radius: 50%; 
      border: 3px solid var(--doc-accent); margin: 0 auto 12px;
      background: url('https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=80&h=80&fit=crop&crop=face') center/cover;
    }
    .doctor-name { font-size: 20px; font-weight: 700; margin: 0 0 4px; color: var(--doc-text); }
    .doctor-title { color: var(--doc-accent); font-size: 14px; margin: 0 0 4px; font-weight: 600; }
    .doctor-specialty { color: var(--doc-text-secondary); font-size: 14px; margin: 0; }
    
    /* Profile Completion */
    .profile-completion { 
      background: var(--doc-accent-light); 
      border: 1px solid rgba(30, 136, 229, 0.2);
      padding: 16px; border-radius: 14px; margin-bottom: 20px;
    }
    .completion-header { 
      display: flex; justify-content: space-between; align-items: center; 
      margin-bottom: 8px; font-size: 12px; font-weight: 600;
    }
    .completion-percentage { color: var(--doc-accent); }
    .progress-bar { 
      height: 8px; background: rgba(30, 136, 229, 0.1); border-radius: 4px; overflow: hidden;
    }
    .progress-fill { 
      height: 100%; background: var(--doc-accent); width: 92%; border-radius: 4px;
      transition: width 0.3s ease;
    }
    
    /* Quick Stats */
    .quick-stats { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
    .stat-item { 
      display: flex; align-items: center; gap: 12px; padding: 14px;
      background: var(--doc-surface); border: 1px solid var(--doc-border-light);
      border-radius: 14px; box-shadow: var(--doc-shadow);
      transition: box-shadow 0.2s ease;
    }
    .stat-item:hover { box-shadow: var(--doc-shadow-hover); }
    .stat-icon { 
      width: 40px; height: 40px; background: var(--doc-accent); 
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
      color: white; font-size: 16px; font-weight: 700;
    }
    .stat-info { display: flex; flex-direction: column; }
    .stat-value { font-size: 18px; font-weight: 700; line-height: 1.2; color: var(--doc-text); }
    .stat-label { font-size: 11px; color: var(--doc-text-secondary); }
    
    /* Quick Actions */
    .quick-actions { display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px; }
    .action-btn { 
      background: var(--doc-accent); color: white; border: none; border-radius: 10px;
      padding: 12px 16px; font-weight: 600; cursor: pointer; 
      transition: background 0.2s; font-size: 14px;
    }
    .action-btn:hover { background: var(--doc-accent-hover); }
    .action-btn.secondary { 
      background: var(--doc-surface); color: var(--doc-text); 
      border: 1px solid var(--doc-border);
    }
    .action-btn.secondary:hover { background: var(--doc-accent-light); }
    
    /* Sidebar Navigation */
    .sidebar-nav { flex: 1; }
    .nav-section { margin-bottom: 24px; }
    .nav-section h4 { 
      font-size: 11px; color: var(--doc-text-secondary); text-transform: uppercase;
      letter-spacing: 0.5px; margin: 0 0 12px; font-weight: 600;
    }
    .nav-section ul { list-style: none; margin: 0; padding: 0; }
    .nav-section li { margin-bottom: 4px; }
    .nav-section a { 
      display: flex; align-items: center; gap: 12px; padding: 10px 12px;
      color: var(--doc-text-secondary); text-decoration: none; border-radius: 10px;
      font-size: 14px; font-weight: 500; transition: all 0.2s;
    }
    .nav-section a:hover { 
      background: var(--doc-accent-light); color: var(--doc-text); 
    }
    .nav-section a.active { background: var(--doc-accent); color: white; }
    
    /* Main Content */
    .main-content { 
      margin-left: 300px; flex: 1; padding: 24px; overflow-y: auto;
      max-height: 100vh; width: calc(100% - 300px);
    }
    
    /* Content Header */
    .content-header { 
      display: flex; align-items: center; margin-bottom: 24px;
      border-bottom: 2px solid var(--doc-border); padding-bottom: 16px;
    }
    .content-header h1 { 
      color: var(--doc-text); margin: 0; font-size: 28px; font-weight: 700;
      display: flex; align-items: center; gap: 12px;
    }
    .content-header .icon { color: var(--doc-accent); font-size: 28px; }
    
    /* Section Grid */
    .section-grid { 
      display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); 
      gap: 20px; margin-bottom: 32px;
    }
    .section-grid.full { grid-template-columns: 1fr; }
    .section-grid.three-col { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
    
    /* Cards */
    .card { 
      background: var(--doc-surface); border: 1px solid var(--doc-border);
      border-radius: 16px; box-shadow: var(--doc-shadow);
      transition: box-shadow 0.2s ease;
    }
    .card:hover { box-shadow: var(--doc-shadow-hover); }
    .card__header { 
      display: flex; justify-content: space-between; align-items: center;
      padding: 16px 20px; border-bottom: 1px solid var(--doc-border-light);
    }
    .card__header h3 { margin: 0; font-size: 16px; font-weight: 700; color: var(--doc-text); }
    .card__body { padding: 20px; }
    
    /* Weekly Schedule */
    .schedule-grid { 
      display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
      gap: 14px;
    }
    .schedule-day { 
      background: var(--doc-surface); border: 1px solid var(--doc-border-light);
      border-radius: 12px; padding: 16px; box-shadow: var(--doc-shadow);
      transition: box-shadow 0.2s ease;
    }
    .schedule-day:hover { box-shadow: var(--doc-shadow-hover); }
    .schedule-day.closed { opacity: 0.6; background: #f8f9fa; }
    .schedule-day h4 { 
      margin: 0 0 12px; color: var(--doc-text); font-size: 16px; font-weight: 700;
      text-align: center; border-bottom: 1px solid var(--doc-border-light);
      padding-bottom: 8px;
    }
    .day-time { 
      font-weight: 600; color: var(--doc-text); margin-bottom: 8px; text-align: center;
    }
    .day-lunch { 
      font-size: 12px; color: var(--doc-text-secondary); text-align: center; margin-bottom: 12px;
    }
    .appointment-types { 
      display: flex; flex-wrap: wrap; gap: 6px; justify-content: center;
    }
    .type-tag { 
      background: var(--doc-accent); color: white; padding: 4px 8px;
      border-radius: 6px; font-size: 11px; font-weight: 600;
    }
    .type-tag.follow-up { background: var(--doc-success); }
    .type-tag.physical { background: #9333ea; }
    .type-tag.procedure { background: var(--doc-warning); }
    .type-tag.emergency { background: var(--doc-error); }
    .type-tag.urgent { background: #f97316; }
    .closed-text { 
      text-align: center; color: var(--doc-text-secondary); font-style: italic;
      font-size: 16px; padding: 20px 0;
    }
    
    /* Contact Items */
    .contact-grid { 
      display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
      gap: 16px;
    }
    .contact-item { 
      display: flex; align-items: center; gap: 12px; padding: 14px;
      background: var(--doc-accent-light); border: 1px solid rgba(30, 136, 229, 0.2);
      border-radius: 12px; transition: background 0.2s ease;
    }
    .contact-item:hover { background: rgba(30, 136, 229, 0.1); }
    .contact-icon { 
      width: 44px; height: 44px; background: var(--doc-accent); 
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
      color: white; font-size: 18px;
    }
    .contact-details { flex: 1; }
    .contact-value { 
      font-weight: 600; color: var(--doc-text); margin-bottom: 2px; font-size: 14px;
    }
    .contact-label { 
      font-size: 11px; color: var(--doc-text-secondary); text-transform: uppercase;
      letter-spacing: 0.5px; font-weight: 600;
    }
    
    /* Emergency Contact */
    .emergency-contact { 
      text-align: center; padding: 20px; background: var(--doc-accent-light);
      border: 1px solid rgba(30, 136, 229, 0.2); border-radius: 12px;
    }
    .emergency-name { 
      font-size: 18px; font-weight: 700; margin: 0 0 4px; color: var(--doc-text);
    }
    .emergency-title { 
      color: var(--doc-text-secondary); margin: 0 0 12px; font-size: 14px;
    }
    .emergency-phone { 
      color: var(--doc-accent); font-weight: 600; font-size: 16px;
    }
    
    /* Info Grid */
    .info-grid { 
      display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
      gap: 16px;
    }
    .info-item { display: flex; flex-direction: column; gap: 4px; }
    .info-item label { 
      font-size: 11px; color: var(--doc-text-secondary); text-transform: uppercase;
      letter-spacing: 0.5px; font-weight: 600;
    }
    .info-item span { color: var(--doc-text); font-weight: 600; }
    
    /* Credentials List */
    .credentials-list { display: flex; flex-direction: column; gap: 16px; }
    .credential-item { 
      padding: 16px; background: var(--doc-surface); border: 1px solid var(--doc-border-light);
      border-radius: 12px; border-left: 4px solid var(--doc-border);
      box-shadow: var(--doc-shadow);
    }
    .credential-item.verified { border-left-color: var(--doc-success); }
    .credential-item.expires-soon { border-left-color: var(--doc-warning); }
    .credential-item.current { border-left-color: var(--doc-accent); }
    
    .credential-header { 
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 8px;
    }
    .credential-title { margin: 0; font-size: 14px; font-weight: 700; color: var(--doc-text); }
    .credential-details { 
      display: flex; flex-direction: column; gap: 4px;
      color: var(--doc-text-secondary); font-size: 13px;
    }
    
    /* Status Badges */
    .status-badge { 
      padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;
      text-transform: uppercase;
    }
    .status-badge.verified { background: var(--doc-success); color: white; }
    .status-badge.expires-soon { background: var(--doc-warning); color: white; }
    .status-badge.current { background: var(--doc-accent); color: white; }
    
    /* Affiliations */
    .affiliations-list { display: flex; flex-direction: column; gap: 12px; }
    .affiliation-item { 
      display: flex; align-items: center; gap: 12px; padding: 12px;
      background: var(--doc-accent-light); border: 1px solid rgba(30, 136, 229, 0.2);
      border-radius: 10px;
    }
    .affiliation-icon { color: var(--doc-accent); font-size: 18px; }
    
    /* CME Progress */
    .cme-progress { text-align: center; }
    .cme-header { 
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 12px; font-weight: 600;
    }
    .completion-rate { color: var(--doc-accent); font-size: 18px; }
    .cme-bar { 
      height: 12px; background: var(--doc-accent-light); border-radius: 6px; 
      overflow: hidden; margin-bottom: 12px;
    }
    .cme-fill { 
      height: 100%; background: var(--doc-accent); width: 76%; 
      border-radius: 6px; transition: width 0.3s;
    }
    .cme-deadline { color: var(--doc-text-secondary); font-size: 13px; }

    /* Edit Buttons */
    .edit-btn { 
      background: var(--doc-surface); color: var(--doc-text); 
      border: 1px solid var(--doc-border);
      border-radius: 8px; padding: 6px 12px; font-size: 12px; cursor: pointer;
      transition: all 0.2s; font-weight: 600;
    }
    .edit-btn:hover { background: var(--doc-accent); color: white; }
    
    /* Add Buttons */
    .add-btn { 
      background: var(--doc-accent); color: white; border: none;
      border-radius: 8px; padding: 6px 12px; font-size: 12px; cursor: pointer;
      font-weight: 600; transition: background 0.2s;
    }
    .add-btn:hover { background: var(--doc-accent-hover); }
    
    /* Appointment Settings */
    .settings-list { display: flex; flex-direction: column; gap: 14px; }
    .setting-item { 
      display: flex; justify-content: space-between; align-items: center;
      padding: 14px 0; border-bottom: 1px solid var(--doc-border-light);
    }
    .setting-label { font-weight: 600; color: var(--doc-text); }
    .setting-value { color: var(--doc-text-secondary); font-weight: 600; }
    
    @media (max-width: 1024px) {
      .sidebar { transform: translateX(-100%); }
      .main-content { margin-left: 0; width: 100%; }
      .section-grid { grid-template-columns: 1fr; }
      .schedule-grid { grid-template-columns: 1fr; }
      .contact-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<div class="dashboard-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-content">
      <!-- Doctor Profile -->
      <div class="doctor-profile">
        <div class="doctor-avatar"></div>
        <div class="doctor-name">Dr. <?php echo htmlspecialchars($doctorName); ?></div>
        <div class="doctor-title">MD, FACP</div>
        <div class="doctor-specialty">Internal Medicine</div>
      </div>
      
      <!-- Profile Completion -->
      <div class="profile-completion">
        <div class="completion-header">
          <span>Profile Completion</span>
          <span class="completion-percentage">92%</span>
        </div>
        <div class="progress-bar">
          <div class="progress-fill"></div>
        </div>
      </div>
      
      <!-- Quick Stats -->
      <div class="quick-stats">
        <div class="stat-item">
          <div class="stat-icon">8</div>
          <div class="stat-info">
            <div class="stat-value">8</div>
            <div class="stat-label">Today's Appointments</div>
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-icon">342</div>
          <div class="stat-info">
            <div class="stat-value">342</div>
            <div class="stat-label">Total Patients</div>
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-icon">$</div>
          <div class="stat-info">
            <div class="stat-value">$45.6K</div>
            <div class="stat-label">Monthly Earnings</div>
          </div>
        </div>
      </div>
      
      <!-- Quick Actions -->
      <div class="quick-actions">
        <button class="action-btn">+ Schedule Appointment</button>
        <button class="action-btn secondary">+ Add Patient</button>
      </div>
      
      <!-- Navigation -->
      <nav class="sidebar-nav">
        <div class="nav-section">
          <h4>Profile Management</h4>
          <ul>
            <li><a href="#professional" class="active">👨‍⚕️ Professional Info</a></li>
            <li><a href="#schedule">📅 Working Hours</a></li>
            <li><a href="#contact">📞 Contact Info</a></li>
            <li><a href="#credentials">🏆 Credentials</a></li>
          </ul>
        </div>
        <div class="nav-section">
          <h4>Patient Management</h4>
          <ul>
            <li><a href="#patients">👥 Patients</a></li>
            <li><a href="#appointments">📋 Appointments</a></li>
            <li><a href="#messages">💬 Messages</a></li>
          </ul>
        </div>
      </nav>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <header class="content-header">
      <h1><span class="icon">👨‍⚕️</span> Professional Information & Qualifications</h1>
    </header>

    <!-- Professional Details -->
    <section class="section-grid">
      <div class="card">
        <div class="card__header">
          <h3>Professional Details</h3>
          <button class="edit-btn">📝 Edit</button>
        </div>
        <div class="card__body">
          <div class="info-grid">
            <div class="info-item">
              <label>Primary Specialization</label>
              <span>Internal Medicine</span>
            </div>
            <div class="info-item">
              <label>Subspecializations</label>
              <span>Cardiology, Geriatric Medicine</span>
            </div>
            <div class="info-item">
              <label>Years of Experience</label>
              <span>12 years</span>
            </div>
            <div class="info-item">
              <label>DEA Number</label>
              <span>BJ1234567</span>
            </div>
            <div class="info-item">
              <label>NPI Number</label>
              <span>1234567890</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Medical Degrees -->
      <div class="card">
        <div class="card__header">
          <h3>Medical Degrees</h3>
          <button class="add-btn">+ Add Degree</button>
        </div>
        <div class="card__body">
          <div class="credentials-list">
            <div class="credential-item verified">
              <div class="credential-header">
                <h4 class="credential-title">Doctor of Medicine (MD)</h4>
                <span class="status-badge verified">Verified</span>
              </div>
              <div class="credential-details">
                <div><strong>Institution:</strong> Harvard Medical School</div>
                <div><strong>Year:</strong> 2012</div>
              </div>
            </div>
            <div class="credential-item verified">
              <div class="credential-header">
                <h4 class="credential-title">Bachelor of Science in Biology</h4>
                <span class="status-badge verified">Verified</span>
              </div>
              <div class="credential-details">
                <div><strong>Institution:</strong> Stanford University</div>
                <div><strong>Year:</strong> 2008</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Board Certifications -->
      <div class="card">
        <div class="card__header">
          <h3>Board Certifications</h3>
          <button class="add-btn">+ Add Certification</button>
        </div>
        <div class="card__body">
          <div class="credentials-list">
            <div class="credential-item expires-soon">
              <div class="credential-header">
                <h4 class="credential-title">American Board of Internal Medicine</h4>
                <span class="status-badge expires-soon">Expires Soon</span>
              </div>
              <div class="credential-details">
                <div><strong>Certification Number:</strong> ABIM-12345</div>
                <div><strong>Expiration:</strong> June 15, 2025</div>
                <div style="color: var(--doc-warning);">285 days remaining</div>
              </div>
            </div>
            <div class="credential-item current">
              <div class="credential-header">
                <h4 class="credential-title">American Board of Cardiovascular Disease</h4>
                <span class="status-badge current">Current</span>
              </div>
              <div class="credential-details">
                <div><strong>Certification Number:</strong> ABCVD-67890</div>
                <div><strong>Expiration:</strong> August 20, 2027</div>
                <div style="color: var(--doc-success);">1016 days remaining</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Medical Licenses -->
    <section class="section-grid">
      <div class="card">
        <div class="card__header">
          <h3>Medical Licenses</h3>
          <button class="add-btn">+ Add License</button>
        </div>
        <div class="card__body">
          <div class="credentials-list">
            <div class="credential-item expires-soon">
              <div class="credential-header">
                <h4 class="credential-title">Massachusetts Medical License</h4>
                <span class="status-badge expires-soon">Renewal Due</span>
              </div>
              <div class="credential-details">
                <div><strong>License Number:</strong> MA-123456</div>
                <div><strong>Expiration:</strong> December 31, 2025</div>
                <div style="color: var(--doc-warning);">119 days remaining</div>
              </div>
            </div>
            <div class="credential-item current">
              <div class="credential-header">
                <h4 class="credential-title">New York Medical License</h4>
                <span class="status-badge current">Active</span>
              </div>
              <div class="credential-details">
                <div><strong>License Number:</strong> NY-789012</div>
                <div><strong>Expiration:</strong> March 31, 2026</div>
                <div style="color: var(--doc-success);">209 days remaining</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Hospital Affiliations -->
      <div class="card">
        <div class="card__header">
          <h3>Hospital Affiliations</h3>
          <button class="add-btn">+ Add Affiliation</button>
        </div>
        <div class="card__body">
          <div class="affiliations-list">
            <div class="affiliation-item">
              <span class="affiliation-icon">🏥</span>
              <span>Massachusetts General Hospital</span>
            </div>
            <div class="affiliation-item">
              <span class="affiliation-icon">🏥</span>
              <span>Brigham and Women's Hospital</span>
            </div>
            <div class="affiliation-item">
              <span class="affiliation-icon">🏥</span>
              <span>Boston Medical Center</span>
            </div>
          </div>
        </div>
      </div>

      <!-- CME Credits -->
      <div class="card">
        <div class="card__header">
          <h3>CME Credits Tracking</h3>
          <span style="color: var(--doc-text-secondary); font-size: 13px;">2025 Cycle</span>
        </div>
        <div class="card__body">
          <div class="cme-progress">
            <div class="cme-header">
              <span>38 of 50 Credits Completed</span>
              <span class="completion-rate">76%</span>
            </div>
            <div class="cme-bar">
              <div class="cme-fill"></div>
            </div>
            <div class="cme-deadline">Deadline: December 31, 2025</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Weekly Schedule -->
    <section class="section-grid full">
      <div class="card">
        <div class="card__header">
          <h3>Weekly Schedule</h3>
          <button class="edit-btn">📝 Edit Schedule</button>
        </div>
        <div class="card__body">
          <div class="schedule-grid">
            <div class="schedule-day">
              <h4>Monday</h4>
              <div class="day-time">8:00 AM - 5:00 PM</div>
              <div class="day-lunch">Lunch: 12:00 - 1:00 PM</div>
              <div class="appointment-types">
                <span class="type-tag">Consultation</span>
                <span class="type-tag follow-up">Follow-up</span>
                <span class="type-tag physical">Physical</span>
              </div>
            </div>
            <div class="schedule-day">
              <h4>Tuesday</h4>
              <div class="day-time">8:00 AM - 5:00 PM</div>
              <div class="day-lunch">Lunch: 12:00 - 1:00 PM</div>
              <div class="appointment-types">
                <span class="type-tag">Consultation</span>
                <span class="type-tag follow-up">Follow-up</span>
                <span class="type-tag procedure">Procedure</span>
              </div>
            </div>
            <div class="schedule-day">
              <h4>Wednesday</h4>
              <div class="day-time">8:00 AM - 5:00 PM</div>
              <div class="day-lunch">Lunch: 12:00 - 1:00 PM</div>
              <div class="appointment-types">
                <span class="type-tag">Consultation</span>
                <span class="type-tag follow-up">Follow-up</span>
                <span class="type-tag physical">Physical</span>
              </div>
            </div>
            <div class="schedule-day">
              <h4>Thursday</h4>
              <div class="day-time">8:00 AM - 5:00 PM</div>
              <div class="day-lunch">Lunch: 12:00 - 1:00 PM</div>
              <div class="appointment-types">
                <span class="type-tag">Consultation</span>
                <span class="type-tag follow-up">Follow-up</span>
                <span class="type-tag procedure">Procedure</span>
              </div>
            </div>
            <div class="schedule-day">
              <h4>Friday</h4>
              <div class="day-time">8:00 AM - 4:00 PM</div>
              <div class="day-lunch">Lunch: 12:00 - 1:00 PM</div>
              <div class="appointment-types">
                <span class="type-tag">Consultation</span>
                <span class="type-tag follow-up">Follow-up</span>
              </div>
            </div>
            <div class="schedule-day">
              <h4>Saturday</h4>
              <div class="day-time">9:00 AM - 1:00 PM</div>
              <div class="appointment-types">
                <span class="type-tag emergency">Emergency</span>
                <span class="type-tag urgent">Urgent Care</span>
              </div>
            </div>
            <div class="schedule-day closed">
              <h4>Sunday</h4>
              <div class="closed-text">Closed</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Appointment Settings -->
    <section class="section-grid">
      <div class="card">
        <div class="card__header">
          <h3>Appointment Settings</h3>
          <button class="edit-btn">📝 Edit</button>
        </div>
        <div class="card__body">
          <div class="settings-list">
            <div class="setting-item">
              <span class="setting-label">Default Duration</span>
              <span class="setting-value">30 minutes</span>
            </div>
            <div class="setting-item">
              <span class="setting-label">Buffer Time</span>
              <span class="setting-value">10 minutes</span>
            </div>
            <div class="setting-item">
              <span class="setting-label">Max Consecutive</span>
              <span class="setting-value">6 appointments</span>
            </div>
          </div>
          
          <h4 style="margin: 20px 0 12px; color: var(--doc-text);">Duration by Type</h4>
          <div class="settings-list">
            <div class="setting-item">
              <span class="setting-label">Consultation</span>
              <span class="setting-value">45 min</span>
            </div>
            <div class="setting-item">
              <span class="setting-label">Follow-up</span>
              <span class="setting-value">20 min</span>
            </div>
            <div class="setting-item">
              <span class="setting-label">Physical</span>
              <span class="setting-value">60 min</span>
            </div>
            <div class="setting-item">
              <span class="setting-label">Procedure</span>
              <span class="setting-value">90 min</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Information -->
    <header class="content-header" style="margin-top: 32px;">
      <h1><span class="icon">📞</span> Contact Information Management</h1>
    </header>

    <section class="section-grid three-col">
      <!-- Office Contact -->
      <div class="card">
        <div class="card__header">
          <h3>Office Contact</h3>
          <button class="edit-btn">📝 Edit</button>
        </div>
        <div class="card__body">
          <div class="contact-grid">
            <div class="contact-item">
              <div class="contact-icon">📞</div>
              <div class="contact-details">
                <div class="contact-value">(617) 555-0123</div>
                <div class="contact-label">Phone</div>
              </div>
            </div>
            <div class="contact-item">
              <div class="contact-icon">📞</div>
              <div class="contact-details">
                <div class="contact-value">Ext. 4567</div>
                <div class="contact-label">Extension</div>
              </div>
            </div>
            <div class="contact-item">
              <div class="contact-icon">📠</div>
              <div class="contact-details">
                <div class="contact-value">(617) 555-0124</div>
                <div class="contact-label">Fax</div>
              </div>
            </div>
            <div class="contact-item" style="grid-column: 1 / -1;">
              <div class="contact-icon">📍</div>
              <div class="contact-details">
                <div class="contact-value">55 Fruit Street, Suite 520<br>Boston, MA 02114</div>
                <div class="contact-label">Office Address</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Personal Contact -->
      <div class="card">
        <div class="card__header">
          <h3>Personal Contact</h3>
          <button class="edit-btn">📝 Edit</button>
        </div>
        <div class="card__body">
          <div class="contact-grid">
            <div class="contact-item">
              <div class="contact-icon">📱</div>
              <div class="contact-details">
                <div class="contact-value">(617) 555-0199</div>
                <div class="contact-label">Mobile</div>
              </div>
            </div>
            <div class="contact-item">
              <div class="contact-icon">📟</div>
              <div class="contact-details">
                <div class="contact-value">(617) 555-PAGE</div>
                <div class="contact-label">Pager</div>
              </div>
            </div>
            <div class="contact-item" style="grid-column: 1 / -1;">
              <div class="contact-icon">📧</div>
              <div class="contact-details">
                <div class="contact-value">sarah.johnson@mgh.harvard.edu</div>
                <div class="contact-label">Primary Email</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Emergency Contact -->
      <div class="card">
        <div class="card__header">
          <h3>Emergency Contact</h3>
          <button class="edit-btn">📝 Edit</button>
        </div>
        <div class="card__body">
          <div class="emergency-contact">
            <h4 class="emergency-name">Dr. Michael Chen</h4>
            <p class="emergency-title">Department Head</p>
            <div class="emergency-phone">📞 (617) 555-0150</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Professional Credentials -->
    <header class="content-header" style="margin-top: 32px;">
      <h1><span class="icon">🏆</span> Professional Credentials</h1>
    </header>

    <section class="section-grid">
      <div class="card">
        <div class="card__header">
          <h3>Verification Status</h3>
          <span class="status-badge verified">Verified</span>
        </div>
        <div class="card__body">
          <div class="settings-list">
            <div class="setting-item">
              <span class="setting-label">Last Verified</span>
              <span class="setting-value">December 15, 2024</span>
            </div>
            <div class="setting-item">
              <span class="setting-label">Next Verification</span>
              <span class="setting-value">June 15, 2025</span>
            </div>
            <div class="setting-item">
              <span class="setting-label">Status</span>
              <span class="setting-value">All Current</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card__header">
          <h3>Malpractice Insurance</h3>
          <span class="status-badge current">Current</span>
        </div>
        <div class="card__body">
          <div class="settings-list">
            <div class="setting-item">
              <span class="setting-label">Provider</span>
              <span class="setting-value">The Doctors Company</span>
            </div>
            <div class="setting-item">
              <span class="setting-label">Policy Number</span>
              <span class="setting-value">MP-789456123</span>
            </div>
            <div class="setting-item">
              <span class="setting-label">Coverage</span>
              <span class="setting-value">$2,000,000</span>
            </div>
            <div class="setting-item">
              <span class="setting-label">Expiration</span>
              <span class="setting-value">October 31, 2025</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
</div>

</body>
</html>
