<?php 
requireLogin(); 
requireAdmin(); 
$adminUser = authUser();
$pendingBookingsCount = 0;
$reviewsCount = 0;
try {
    $pendingBookingsCount = Booking::countByStatus('pending');
    $reviewsStats = Review::stats();
    $reviewsCount = $reviewsStats['total'];
} catch (\Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($pageTitle ?? 'Dashboard') ?> | Terminal 1 Operations</title>
  <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
  <link rel="alternate icon" href="<?= asset('favicon.ico') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,400&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      --gold:#C8860A;
      --gold-lt:#E8A820;
      --gold-dark:#9B6305;
      --gold-glow:rgba(200,134,10,0.14);
      --bg:#0C0B08;
      --sidebar:#12100C;
      --char:#171511;
      --card:#181612;
      --card-hover:#1F1D17;
      --border:rgba(255,255,255,0.07);
      --border-gold:rgba(200,134,10,0.22);
      --text:#F3EEE3;
      --text-muted:rgba(243,238,227,0.55);
      --text-dim:rgba(243,238,227,0.35);
      --success:#3fb950;
      --success-bg:rgba(63,185,80,0.12);
      --danger:#f85149;
      --danger-bg:rgba(248,81,73,0.12);
      --warn:#d29922;
      --warn-bg:rgba(210,153,34,0.12);
      --font-display:'Playfair Display', Georgia, serif;
      --font-body:'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    }
    html, body{max-width:100%;overflow-x:clip}
    body{font-family:var(--font-body);background:var(--bg);color:var(--text);display:flex;min-height:100vh;-webkit-font-smoothing:antialiased;letter-spacing:-0.1px}
    
    /* SIDEBAR */
    .sidebar{width:260px;background:var(--sidebar);border-right:1px solid var(--border);display:flex;flex-direction:column;flex-shrink:0;position:fixed;top:0;bottom:0;left:0;overflow-y:auto;z-index:90;transition:transform .28s cubic-bezier(0.16, 1, 0.3, 1)}
    .sidebar-logo{padding:26px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px}
    .logo-crest{width:40px;height:40px;border-radius:6px;background:linear-gradient(135deg, rgba(200,134,10,0.25), rgba(200,134,10,0.05));border:1px solid var(--border-gold);display:flex;align-items:center;justify-content:center;color:var(--gold-lt);font-family:var(--font-display);font-weight:900;font-size:1.1rem;box-shadow:0 4px 12px rgba(0,0,0,0.3)}
    .logo-text h1{font-family:var(--font-display);font-size:1.15rem;color:#fff;font-weight:700;line-height:1.2}
    .logo-text small{color:var(--gold-lt);font-size:0.68rem;letter-spacing:1.5px;text-transform:uppercase;font-weight:600;display:block;margin-top:2px}
    
    .nav-section{padding:14px 12px 6px}
    .nav-label{font-size:0.68rem;letter-spacing:2px;text-transform:uppercase;color:var(--text-dim);padding:8px 14px;font-weight:700}
    .nav-item{display:flex;align-items:center;gap:12px;padding:10px 14px;color:var(--text-muted);text-decoration:none;font-size:0.88rem;font-weight:500;border-radius:6px;transition:all .18s ease;margin-bottom:3px}
    .nav-item:hover{color:#fff;background:rgba(255,255,255,0.04)}
    .nav-item.active{color:#fff;background:linear-gradient(90deg, rgba(200,134,10,0.18), rgba(200,134,10,0.06));border:1px solid var(--border-gold);font-weight:600}
    .nav-item.active .icon{color:var(--gold-lt)}
    .nav-item .icon{width:18px;height:18px;display:flex;align-items:center;justify-content:center;color:var(--text-dim);transition:color .18s}
    .nav-badge{margin-left:auto;background:var(--warn-bg);border:1px solid rgba(210,153,34,0.3);color:var(--warn);font-size:0.72rem;font-weight:700;padding:2px 7px;border-radius:12px}
    
    .sidebar-footer{margin-top:auto;padding:16px;border-top:1px solid var(--border);background:rgba(0,0,0,0.15)}
    .user-card{display:flex;align-items:center;gap:12px;background:rgba(255,255,255,0.03);border:1px solid var(--border);padding:10px 12px;border-radius:6px}
    .user-avatar{width:36px;height:36px;border-radius:6px;background:linear-gradient(135deg, var(--gold), var(--gold-dark));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.85rem;box-shadow:0 2px 8px rgba(0,0,0,0.3)}
    .user-meta{flex:1;min-width:0}
    .user-name{font-size:0.85rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .user-role{font-size:0.68rem;color:var(--gold-lt);font-weight:600;letter-spacing:0.5px;text-transform:uppercase}
    
    /* MAIN WRAPPER */
    .main{margin-left:260px;flex:1;display:flex;flex-direction:column;max-width:calc(100% - 260px);min-width:0;overflow-x:clip}
    .topbar{background:rgba(23,21,17,0.85);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid var(--border);padding:14px 32px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:20;width:100%}
    .topbar-left{display:flex;align-items:center;gap:14px}
    .btn-menu-toggle{display:none;background:none;border:1px solid var(--border);color:#fff;font-size:1.1rem;cursor:pointer;padding:6px 10px;border-radius:4px}
    .breadcrumb{font-size:0.82rem;color:var(--text-dim);display:flex;align-items:center;gap:8px}
    .breadcrumb span{color:#fff;font-weight:600}
    .topbar-right{display:flex;align-items:center;gap:14px}
    .status-pill{display:inline-flex;align-items:center;gap:7px;background:var(--success-bg);border:1px solid rgba(63,185,80,0.25);color:var(--success);font-size:0.75rem;padding:5px 12px;border-radius:20px;font-weight:600}
    .status-dot{width:7px;height:7px;border-radius:50%;background:var(--success);box-shadow:0 0 8px var(--success);animation:pulseDot 2s infinite}
    @keyframes pulseDot{0%,100%{opacity:1}50%{opacity:.3}}
    .btn-topbar{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.04);border:1px solid var(--border);color:var(--text-muted);padding:7px 14px;border-radius:4px;font-size:0.8rem;font-weight:500;text-decoration:none;transition:all .18s}
    .btn-topbar:hover{color:#fff;border-color:var(--border-gold);background:var(--gold-glow)}
    .btn-logout-luxury{background:rgba(248,81,73,0.08);border:1px solid rgba(248,81,73,0.25);color:var(--danger);padding:7px 14px;border-radius:4px;font-size:0.8rem;font-weight:600;cursor:pointer;transition:all .18s}
    .btn-logout-luxury:hover{background:var(--danger);color:#fff;border-color:var(--danger)}
    
    .content{padding:32px;flex:1;max-width:1400px;width:100%;box-sizing:border-box}

    /* EXECUTIVE KPI METRIC CARDS */
    .kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:28px}
    .kpi-card{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:22px;position:relative;overflow:hidden;transition:all .22s cubic-bezier(0.16, 1, 0.3, 1);box-shadow:0 4px 16px rgba(0,0,0,0.2)}
    .kpi-card:hover{border-color:var(--border-gold);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,0.35)}
    .kpi-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px}
    .kpi-label{font-size:0.75rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted)}
    .kpi-icon-wrap{width:38px;height:38px;border-radius:8px;background:rgba(255,255,255,0.04);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--gold-lt)}
    .kpi-value{font-family:var(--font-display);font-size:1.85rem;font-weight:700;color:#fff;line-height:1.1;margin-bottom:10px}
    .kpi-footer{display:flex;align-items:center;gap:8px;flex-wrap:wrap;font-size:0.76rem;color:var(--text-dim)}
    .pill-indicator{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:4px;font-size:0.72rem;font-weight:600}
    .pill-green{background:var(--success-bg);color:var(--success);border:1px solid rgba(63,185,80,0.25)}
    .pill-amber{background:var(--warn-bg);color:var(--warn);border:1px solid rgba(210,153,34,0.25)}
    .pill-red{background:var(--danger-bg);color:var(--danger);border:1px solid rgba(248,81,73,0.25)}
    .pill-neutral{background:rgba(255,255,255,0.06);color:var(--text-muted)}

    /* ANALYTICS 2-COLUMN GRID */
    .analytics-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px}

    /* CARD CONTAINERS */
    .card{background:var(--card);border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:24px;box-shadow:0 4px 16px rgba(0,0,0,0.2)}
    .card-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;background:rgba(255,255,255,0.01)}
    .card-title{font-size:0.95rem;font-weight:600;color:#fff;display:flex;align-items:center;gap:8px}
    .card-body{padding:22px}
    
    /* TABLE & LEDGER */
    .table-responsive{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}
    table{width:100%;border-collapse:collapse;min-width:640px}
    th{background:rgba(0,0,0,0.2);font-size:0.72rem;letter-spacing:1.2px;text-transform:uppercase;color:var(--text-dim);padding:14px 20px;text-align:left;font-weight:700;border-bottom:1px solid var(--border)}
    td{padding:14px 20px;border-bottom:1px solid var(--border);font-size:0.86rem;color:var(--text-muted);vertical-align:middle}
    tr:last-child td{border-bottom:none}
    tr:hover td{background:rgba(255,255,255,0.02);color:#fff}
    
    /* BADGES */
    .badge{display:inline-flex;align-items:center;gap:5px;padding:4px 9px;border-radius:4px;font-size:0.72rem;letter-spacing:0.5px;text-transform:uppercase;font-weight:600}
    .badge-pending{background:var(--warn-bg);color:var(--warn);border:1px solid rgba(210,153,34,0.3)}
    .badge-confirmed{background:var(--success-bg);color:var(--success);border:1px solid rgba(63,185,80,0.3)}
    .badge-cancelled{background:var(--danger-bg);color:var(--danger);border:1px solid rgba(248,81,73,0.3)}
    .badge-completed{background:var(--gold-glow);color:var(--gold-lt);border:1px solid var(--border-gold)}
    .badge-admin{background:var(--gold-glow);color:var(--gold-lt);border:1px solid var(--border-gold)}
    .badge-user{background:rgba(255,255,255,0.06);color:var(--text-muted)}
    
    /* BUTTONS */
    .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:4px;font-size:0.82rem;font-weight:600;letter-spacing:0.3px;cursor:pointer;border:none;text-decoration:none;font-family:var(--font-body);transition:all .18s}
    .btn-primary{background:var(--gold);color:#fff}.btn-primary:hover{background:var(--gold-lt);box-shadow:0 2px 10px rgba(200,134,10,0.4)}
    .btn-danger{background:var(--danger-bg);color:var(--danger);border:1px solid rgba(248,81,73,0.3)}.btn-danger:hover{background:var(--danger);color:#fff}
    .btn-success{background:var(--success-bg);color:var(--success);border:1px solid rgba(63,185,80,0.3)}.btn-success:hover{background:var(--success);color:#fff}
    .btn-sm{padding:5px 12px;font-size:0.75rem}
    
    /* FORMS */
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .form-group{margin-bottom:16px}
    .form-group label{display:block;font-size:0.72rem;letter-spacing:1px;text-transform:uppercase;color:var(--text-dim);margin-bottom:6px;font-weight:600}
    .form-control{width:100%;background:rgba(255,255,255,0.04);border:1px solid var(--border);color:#fff;padding:10px 14px;border-radius:4px;font-family:var(--font-body);font-size:0.88rem;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:var(--gold);box-shadow:0 0 0 1px var(--gold)}
    .form-control option{background:#161411;color:#fff}
    
    /* FLASH NOTIFICATIONS */
    .flash{padding:14px 20px;border-radius:6px;margin-bottom:24px;font-size:0.86rem;display:flex;align-items:center;gap:10px}
    .flash-success{background:var(--success-bg);border:1px solid rgba(63,185,80,0.35);color:var(--success)}
    .flash-error{background:var(--danger-bg);border:1px solid rgba(248,81,73,0.35);color:var(--danger)}
    
    /* MODAL */
    .modal{display:none;position:fixed;inset:0;z-index:100;background:rgba(0,0,0,0.75);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:20px}
    .modal.open{display:flex}
    .modal-box{background:var(--card);border:1px solid var(--border-gold);border-radius:8px;padding:32px;width:100%;max-width:540px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 50px rgba(0,0,0,0.6)}
    .modal-title{font-family:var(--font-display);font-size:1.3rem;color:#fff;margin-bottom:20px}
    
    .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:85}
    
    @media(max-width:1100px){
      .kpi-grid{grid-template-columns:repeat(2,1fr)}
      .analytics-grid{grid-template-columns:1fr}
    }
    @media(max-width:850px){
      .sidebar{transform:translateX(-100%)}
      .sidebar.open{transform:translateX(0)}
      .sidebar-overlay.open{display:block}
      .main{margin-left:0;max-width:100%}
      .btn-menu-toggle{display:inline-block}
      .topbar{padding:12px 18px}
      .content{padding:20px 16px}
      .kpi-grid{grid-template-columns:1fr}
      .status-pill{display:none}
    }
    @media(prefers-reduced-motion: reduce){
      *,*::before,*::after{transition:none !important;animation:none !important}
    }
  </style>
</head>
<body>
<nav class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-crest">T1</div>
    <div class="logo-text">
      <h1>Terminal 1</h1>
      <small>Operations Suite</small>
    </div>
  </div>
  <div class="nav-section">
    <div class="nav-label">Analytics &amp; Control</div>
    <a class="nav-item <?= $activePage==='dashboard'?'active':'' ?>" href="<?= url('/admin') ?>">
      <span class="icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
      </span>
      Executive Dashboard
    </a>
  </div>
  <div class="nav-section">
    <div class="nav-label">Service &amp; Operations</div>
    <a class="nav-item <?= $activePage==='bookings'?'active':'' ?>" href="<?= url('/admin/bookings') ?>">
      <span class="icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
      </span>
      Table Bookings
      <?php if ($pendingBookingsCount > 0): ?>
        <span class="nav-badge"><?= $pendingBookingsCount ?> pending</span>
      <?php endif; ?>
    </a>
    <a class="nav-item <?= $activePage==='menu'?'active':'' ?>" href="<?= url('/admin/menu') ?>">
      <span class="icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2v20"/><path d="M21 15V2v0a3 3 0 0 0-3 3v6a3 3 0 0 0 3 3v7"/><path d="M8 2v20"/><path d="M4 2v7a4 4 0 0 0 8 0V2"/></svg>
      </span>
      Kitchen Menu
    </a>
    <a class="nav-item <?= $activePage==='users'?'active':'' ?>" href="<?= url('/admin/users') ?>">
      <span class="icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </span>
      User Ledger
    </a>
    <a class="nav-item <?= $activePage==='reviews'?'active':'' ?>" href="<?= url('/admin/reviews') ?>">
      <span class="icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </span>
      Guest Reviews
      <?php if ($reviewsCount > 0): ?>
        <span class="nav-badge" style="background:rgba(200,134,10,0.18);color:var(--gold-lt);border-color:var(--border-gold);"><?= $reviewsCount ?></span>
      <?php endif; ?>
    </a>
  </div>
  <div class="nav-section">
    <div class="nav-label">Live Channels</div>
    <a class="nav-item" href="<?= url('/') ?>" target="_blank">
      <span class="icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
      </span>
      Public Storefront ↗
    </a>
  </div>
  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar"><?= strtoupper(substr($adminUser['name'] ?? 'A', 0, 1)) ?></div>
      <div class="user-meta">
        <div class="user-name"><?= e($adminUser['name'] ?? 'Administrator') ?></div>
        <div class="user-role">Lead Admin</div>
      </div>
      <form method="POST" action="<?= url('/auth/logout') ?>" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <button type="submit" title="Logout" style="background:none;border:none;color:var(--text-dim);cursor:pointer;padding:4px;display:flex;align-items:center;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
        </button>
      </form>
    </div>
  </div>
</nav>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleAdminSidebar()"></div>
<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <button class="btn-menu-toggle" onclick="toggleAdminSidebar()" aria-label="Toggle Navigation">☰</button>
      <div class="breadcrumb">
        Terminal 1 &nbsp;›&nbsp; <span><?= e($pageTitle ?? 'Dashboard') ?></span>
      </div>
    </div>
    <div class="topbar-right">
      <div class="status-pill">
        <span class="status-dot"></span> System Live
      </div>
      <a href="<?= url('/') ?>" target="_blank" class="btn-topbar">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
        Storefront
      </a>
      <form method="POST" action="<?= url('/auth/logout') ?>" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <button type="submit" class="btn-logout-luxury">Logout</button>
      </form>
    </div>
  </div>
  <div class="content">
