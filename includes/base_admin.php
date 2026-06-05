<?php
function render_admin_header(string $active = '', int $unread = 0, string $title = 'DisBasura Admin'): void {
    $nav = [
        'dashboard'     => ['Dashboard',      '/disbasura/admin/dashboard.php',
            '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
        'schedules'     => ['Schedules',      '/disbasura/admin/schedules.php',
            '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
        'requests'      => ['Requests',       '/disbasura/admin/requests.php',
            '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>'],
        'collectors'    => ['Collectors',     '/disbasura/admin/collectors.php',
            '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
        'performance'   => ['Performance',    '/disbasura/admin/performance.php',
            '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>'],
        'residents'     => ['Residents',      '/disbasura/admin/residents.php',
            '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>'],
        'sitios'        => ['Sitios',         '/disbasura/admin/sitios.php',
            '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>'],
        'announcements' => ['Announcements',  '/disbasura/admin/announcements.php',
            '<path d="M22 17H2a3 3 0 000 6h20"/><path d="M2 17l10-9 10 9"/>'],
        'history'       => ['History',        '/disbasura/admin/history.php',
            '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
        'disputes'      => ['Disputes',       '/disbasura/admin/disputes.php',
            '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/>'],
        'tracker'       => ['Truck Tracker',  '/disbasura/admin/tracker.php',
            '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/>'],
        'activity'      => ['Activity Log',   '/disbasura/admin/activity-log.php',
            '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'],
        'reports'       => ['Reports',        '/disbasura/admin/reports.php',
            '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'],
        'notifications' => ['Notifications',  '/disbasura/admin/notifications.php',
            '<path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>'],
    ];

    $full_name = e($_SESSION['admin_name'] ?? 'Admin');
    $initial   = strtoupper(substr($full_name, 0, 1));
    $ud        = $unread > 0 ? 'flex' : 'none';

    // Dark mode check
    $prefs    = function_exists('get_preferences') ? get_preferences($_SESSION['admin_id'] ?? 0) : ['dark_mode'=>0];
    $dark     = $prefs['dark_mode'] ? 'data-dark="1"' : '';

    echo '<!DOCTYPE html>
<html lang="en" '.($prefs['dark_mode']?'class="dark"':'').'>
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>'.e($title).'</title>
  <link rel="stylesheet" href="/disbasura/assets/css/base.css"/>
  <link rel="stylesheet" href="/disbasura/assets/css/dashboard.css"/>
  <style>
    .mob-toggle{display:none;flex-direction:column;gap:5px;width:36px;height:36px;padding:6px;background:transparent;border:1.5px solid var(--border);border-radius:8px;cursor:pointer}
    .mob-toggle span{display:block;width:100%;height:2px;background:var(--text-mid);border-radius:2px;transition:.18s}
    .mob-toggle:hover{border-color:var(--green-main)}
    .mob-toggle:hover span{background:var(--green-main)}
    @media(max-width:768px){.mob-toggle{display:flex}.sidebar{position:fixed!important;left:-260px;top:0;height:100vh;z-index:200;transition:left .28s}.sidebar.open{left:0;box-shadow:0 0 0 9999px rgba(0,0,0,.4)}.main{width:100%}}
    .nav-badge{background:var(--red);color:#fff;border-radius:20px;padding:.1rem .45rem;font-size:.65rem;font-weight:800;margin-left:auto;min-width:18px;text-align:center}
    @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.4)}}
    /* Dark Mode */
    html.dark{--bg-page:#0f1a14;--bg-card:#1a2e22;--text-dark:#e8f5ee;--text-mid:#8baa96;--text-light:#5a7a65;--border:#2d4a36;--border-light:#243d2c;--shadow-sm:0 2px 8px rgba(0,0,0,.4)}
    html.dark .sidebar{background:linear-gradient(180deg,#0a1f13 0%,#0f2d1e 100%)}
    html.dark .main{background:var(--bg-page)}
    html.dark .panel,html.dark .stat-card,html.dark .auth-card{background:var(--bg-card);border-color:var(--border)}
    html.dark input,html.dark select,html.dark textarea{background:#1a2e22;border-color:var(--border);color:var(--text-dark)}
    html.dark .topbar{background:var(--bg-card);border-color:var(--border)}
    html.dark table{background:var(--bg-card)}
    html.dark tr{border-color:var(--border) !important}
    html.dark .sched-card{background:var(--bg-card) !important;border-color:var(--border) !important}
    html.dark .modal,html.dark [style*="background:#fff"]{background:var(--bg-card) !important;color:var(--text-dark) !important}
    /* Dark mode toggle button */
    .dark-toggle{display:flex;align-items:center;gap:.5rem;padding:.45rem .85rem;border-radius:8px;border:1.5px solid var(--border);background:transparent;cursor:pointer;font-size:.78rem;font-weight:600;color:var(--text-mid);font-family:inherit;transition:all .18s;margin-top:.5rem;width:100%}
    .dark-toggle:hover{border-color:var(--green-main);color:var(--green-main)}
  </style>
</head>
<body>
<div class="app active" id="appRoot">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <svg viewBox="0 0 260 220" fill="none" width="34" height="30">
        <path d="M108 30 Q130 18 152 30" stroke="#3cb371" stroke-width="5" fill="none" stroke-linecap="round"/>
        <polygon points="152,24 162,30 152,36" fill="#3cb371"/>
        <path d="M200 78 Q212 110 200 142" stroke="#3cb371" stroke-width="5" fill="none" stroke-linecap="round"/>
        <polygon points="194,142 200,154 206,142" fill="#3cb371"/>
        <path d="M152 188 Q130 200 108 188" stroke="#3cb371" stroke-width="5" fill="none" stroke-linecap="round"/>
        <polygon points="108,194 98,188 108,182" fill="#3cb371"/>
        <path d="M60 142 Q48 110 60 78" stroke="#3cb371" stroke-width="5" fill="none" stroke-linecap="round"/>
        <polygon points="66,78 60,66 54,78" fill="#3cb371"/>
        <rect x="120" y="62" width="20" height="10" rx="4" fill="none" stroke="#3cb371" stroke-width="3"/>
        <rect x="98"  y="72" width="64" height="10" rx="4" fill="none" stroke="#3cb371" stroke-width="3"/>
        <rect x="104" y="84" width="52" height="52" rx="4" fill="none" stroke="#3cb371" stroke-width="3"/>
        <line x1="116" y1="92" x2="116" y2="128" stroke="#3cb371" stroke-width="2.5" stroke-linecap="round"/>
        <line x1="126" y1="92" x2="126" y2="128" stroke="#3cb371" stroke-width="2.5" stroke-linecap="round"/>
        <line x1="136" y1="92" x2="136" y2="128" stroke="#3cb371" stroke-width="2.5" stroke-linecap="round"/>
        <line x1="146" y1="92" x2="146" y2="128" stroke="#3cb371" stroke-width="2.5" stroke-linecap="round"/>
      </svg>
      <div class="sidebar-brand-text"><h2>DisBasura</h2><span>Admin Panel</span></div>
    </div>
    <nav class="sidebar-nav">';

    foreach ($nav as $key => [$label, $url, $icon]) {
        $cls   = $key === $active ? ' active' : '';
        $badge = ($key === 'notifications' && $unread > 0) ? "<span class='nav-badge'>$unread</span>" : '';
        echo "<a class='nav-item$cls' href='$url'><svg fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24' width='18' height='18'>$icon</svg><span class='nav-label'>$label</span>$badge</a>\n";
    }

    $dark_icon  = $prefs['dark_mode'] ? '☀️' : '🌙';
    $dark_label = $prefs['dark_mode'] ? 'Light Mode' : 'Dark Mode';

    echo '    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar">'.$initial.'</div>
        <div class="sidebar-user-info"><strong>'.$full_name.'</strong><span>Administrator</span></div>
      </div>
      <button class="dark-toggle" onclick="toggleDark()" id="darkBtn">'.$dark_icon.' '.$dark_label.'</button>
      <a href="/disbasura/logout.php" class="signout-btn" style="margin-top:.4rem">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span class="nav-label">Sign Out</span>
      </a>
    </div>
  </aside>
  <main class="main" id="mainArea">
    <div class="topbar">
      <button class="mob-toggle" id="mobToggle" onclick="document.getElementById(\'sidebar\').classList.toggle(\'open\')">
        <span></span><span></span><span></span>
      </button>
      <div style="margin-left:auto;position:relative;display:inline-flex">
        <a class="notif-btn" href="/disbasura/admin/notifications.php" title="Notifications" style="color:var(--text-mid)">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20" height="20">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 01-3.46 0"/>
          </svg>
        </a>
        <span id="topBadge" style="display:'.$ud.';position:absolute;top:-5px;right:-5px;background:var(--red);color:#fff;border-radius:50%;width:18px;height:18px;font-size:.62rem;font-weight:800;align-items:center;justify-content:center;border:2px solid var(--bg-page)">'.$unread.'</span>
      </div>
    </div>
    <div class="page-content active">';
}

function render_admin_footer(): void {
    echo '    </div>
    <footer class="site-footer">
      <div class="footer-logo">
        <svg viewBox="0 0 260 220" fill="none" width="18" height="15">
          <rect x="104" y="84" width="52" height="52" rx="4" fill="none" stroke="var(--green-main)" stroke-width="4"/>
          <rect x="98"  y="72" width="64" height="10" rx="4" fill="none" stroke="var(--green-main)" stroke-width="4"/>
          <rect x="120" y="62" width="20" height="10" rx="4" fill="none" stroke="var(--green-main)" stroke-width="4"/>
        </svg>
        DisBasura
      </div>
      <div style="margin-top:.25rem">
        Smart Sitio-Based Garbage Collection System &nbsp;·&nbsp; <strong>UC Main — Cebu City</strong><br>
        Developed by <strong>DisBasura Capstone Project</strong> &nbsp;·&nbsp; 2026
      </div>
    </footer>
  </main>
</div>
<script>
// Mobile sidebar
document.addEventListener("click",function(e){
  const sb=document.getElementById("sidebar"),tg=document.getElementById("mobToggle");
  if(sb&&!sb.contains(e.target)&&tg&&!tg.contains(e.target))sb.classList.remove("open");
});
// Dark mode
function toggleDark(){
  fetch("/disbasura/api/preferences.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({toggle_dark:1})})
    .then(()=>location.reload());
}
// Notification poll
(function poll(){
  setTimeout(function(){
    fetch("/disbasura/api/notifications.php").then(r=>r.json()).then(d=>{
      const b=document.getElementById("topBadge");
      if(b){b.textContent=d.count;b.style.display=d.count>0?"flex":"none";}
    }).catch(()=>{});
    poll();
  },30000);
})();
// Auto-flag check every 5 minutes
(function flagCheck(){
  setTimeout(function(){
    fetch("/disbasura/api/auto-flag.php").catch(()=>{});
    flagCheck();
  },300000);
})();
</script>
</body>
</html>';
}
