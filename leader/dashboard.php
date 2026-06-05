<?php
require_once __DIR__ . '/../middleware/resident_auth.php';
require_once __DIR__ . '/../includes/helpers.php';
if ($_SESSION['role'] !== 'leader') { header('Location: /disbasura/resident/dashboard.php'); exit; }
$db = get_db(); $sitio = $_SESSION['sitio']??''; $uid=$_SESSION['user_id'];
$residents     = $db->prepare("SELECT * FROM users WHERE sitio=? AND role IN ('resident','leader') ORDER BY full_name"); $residents->execute([$sitio]); $residents=$residents->fetchAll();
$sitio_requests= $db->prepare("SELECT r.*,u.full_name,c.full_name as collector_name FROM requests r JOIN users u ON r.resident_id=u.id LEFT JOIN collectors c ON r.collector_id=c.id WHERE r.sitio=? ORDER BY r.created_at DESC"); $sitio_requests->execute([$sitio]); $sitio_requests=$sitio_requests->fetchAll();
$schedules     = $db->prepare("SELECT * FROM schedules WHERE sitio=? AND DATE(scheduled_at)>=CURDATE() ORDER BY scheduled_at ASC LIMIT 5"); $schedules->execute([$sitio]); $schedules=$schedules->fetchAll();
$unread        = get_unread_count($uid);
$pending_count = $db->prepare("SELECT COUNT(*) FROM requests WHERE sitio=? AND status='pending'"); $pending_count->execute([$sitio]); $pending_count=(int)$pending_count->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Leader Dashboard — DisBasura</title>
<link rel="stylesheet" href="/disbasura/assets/css/base.css"/>
<link rel="stylesheet" href="/disbasura/assets/css/dashboard.css"/>
</head>
<body>
<?php
// Inline leader sidebar
$full_name = htmlspecialchars($_SESSION['full_name']);
$initial   = strtoupper(substr($_SESSION['full_name'],0,1));
?>
<div class="app active" id="appRoot">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <svg viewBox="0 0 260 220" fill="none" width="34" height="30"><rect x="120" y="62" width="20" height="10" rx="4" fill="none" stroke="#3cb371" stroke-width="3"/><rect x="98" y="72" width="64" height="10" rx="4" fill="none" stroke="#3cb371" stroke-width="3"/><rect x="104" y="84" width="52" height="52" rx="4" fill="none" stroke="#3cb371" stroke-width="3"/></svg>
      <div class="sidebar-brand-text"><h2>DisBasura</h2><span>Sitio Leader</span></div>
    </div>
    <nav class="sidebar-nav">
      <a class="nav-item active" href="/disbasura/leader/dashboard.php" data-tip="Dashboard"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg><span class="nav-label">Dashboard</span></a>
      <a class="nav-item" href="/disbasura/leader/requests.php" data-tip="Sitio Requests"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg><span class="nav-label">Sitio Requests</span></a>
      <a class="nav-item" href="/disbasura/leader/submit-request.php" data-tip="Submit for Resident"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/></svg><span class="nav-label">Submit for Resident</span></a>
      <a class="nav-item" href="/disbasura/leader/notifications.php" data-tip="Notifications"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg><span class="nav-label">Notifications</span></a>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar" style="background:var(--orange)"><?= $initial ?></div>
        <div class="sidebar-user-info"><strong><?= $full_name ?></strong><span><?= htmlspecialchars($sitio) ?></span></div>
      </div>
      <a href="/disbasura/logout.php" class="signout-btn"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span class="nav-label">Sign Out</span></a>
      <button onclick="toggleSidebar()" style="margin-top:.5rem;width:100%;border:1px solid rgba(255,255,255,.12);background:transparent;color:rgba(255,255,255,.4);border-radius:6px;padding:.4rem;cursor:pointer;font-size:.72rem">‹ Collapse</button>
    </div>
  </aside>
  <main class="main">
    <div class="topbar">
      <button class="sidebar-toggle" onclick="toggleSidebar()"><span class="toggle-bar"></span><span class="toggle-bar"></span><span class="toggle-bar"></span></button>
      <span style="background:#fff3e0;color:var(--orange);border:1px solid #ffe0b2;border-radius:20px;padding:.25rem .85rem;font-size:.78rem;font-weight:600;margin-left:.5rem">⭐ Sitio Leader — <?= htmlspecialchars($sitio) ?></span>
      <a class="notif-btn" href="/disbasura/leader/notifications.php" style="margin-left:auto"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg></a>
    </div>
    <div class="page-content active">
      <div class="page-header"><h1>Sitio Leader Dashboard</h1><p><?= htmlspecialchars($sitio) ?> — Managing your community</p></div>
      <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon green"></div><div><div class="stat-val"><?= count($residents) ?></div><div class="stat-label">Residents in Sitio</div></div></div>
        <div class="stat-card"><div class="stat-icon orange"></div><div><div class="stat-val"><?= $pending_count ?></div><div class="stat-label">Pending Requests</div></div></div>
        <div class="stat-card"><div class="stat-icon blue"></div><div><div class="stat-val"><?= count($schedules) ?></div><div class="stat-label">Upcoming Schedules</div></div></div>
        <div class="stat-card"><div class="stat-icon teal"></div><div><div class="stat-val"><?= count($sitio_requests) ?></div><div class="stat-label">Total Requests</div></div></div>
      </div>
      <div class="two-col">
        <div class="panel">
          <div class="panel-header"><h3>Requests in <?= htmlspecialchars($sitio) ?></h3><a class="manage-link" href="/disbasura/leader/requests.php">View All</a></div>
          <?php if($sitio_requests): foreach(array_slice($sitio_requests,0,5) as $r): ?>
          <div class="request-item"><div class="request-item-info"><strong><?= htmlspecialchars($r['full_name']) ?></strong><span><?= htmlspecialchars($r['waste_type']) ?> · <?= fmt_date($r['preferred_date']) ?></span></div><span class="badge <?= $r['status'] ?>"><?= $r['status'] ?></span></div>
          <?php endforeach; else: ?><div class="empty-state"><p>No requests yet.</p></div><?php endif; ?>
        </div>
        <div class="panel">
          <div class="panel-header"><h3>Upcoming Schedules</h3></div>
          <?php if($schedules): foreach($schedules as $s): ?>
          <div class="request-item"><div class="request-item-info"><strong><?= htmlspecialchars($s['waste_type']) ?></strong><span><?= fmt_date($s['scheduled_at']) ?></span></div><span class="badge scheduled">scheduled</span></div>
          <?php endforeach; else: ?><div class="empty-state"><p>No upcoming schedules.</p></div><?php endif; ?>
        </div>
      </div>
    </div>
    <footer class="site-footer">
      <div class="footer-logo">DisBasura</div><br>
      Smart Sitio-Based Garbage Collection System · <strong>UC</strong><br>
      Developed by <strong>DisBasura Capstone Project</strong> · 2026
    </footer>
  </main>
</div>
<script>
function toggleSidebar(){ const a=document.getElementById('appRoot'); const c=a.classList.toggle('sidebar-collapsed'); localStorage.setItem('leader_sb',c?'1':'0'); }
(function(){ if(localStorage.getItem('leader_sb')==='1') document.getElementById('appRoot').classList.add('sidebar-collapsed'); })();
</script>
</body>
</html>
