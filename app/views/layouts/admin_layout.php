<?php requireLogin(); requireAdmin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($pageTitle ?? 'Admin') ?> — Terminal 1</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--gold:#C8860A;--gold-lt:#E8A820;--dark:#111009;--char:#1A1814;--sidebar:#141210;--cream:#F6F1E8;--text:#2D2A24;--muted:#7A7264;--success:#2da44e;--danger:#cf222e;--warn:#d29922}
    body{font-family:'DM Sans',sans-serif;background:#0F0E0B;color:#e0dbd0;display:flex;min-height:100vh}
    /* SIDEBAR */
    .sidebar{width:240px;background:var(--sidebar);border-right:1px solid rgba(200,134,10,.12);display:flex;flex-direction:column;flex-shrink:0;position:fixed;top:0;bottom:0;left:0;overflow-y:auto}
    .sidebar-logo{padding:28px 24px 20px;border-bottom:1px solid rgba(255,255,255,.06)}
    .sidebar-logo h1{font-family:'Playfair Display',serif;font-size:1.3rem;color:var(--gold-lt);letter-spacing:1px}
    .sidebar-logo small{color:rgba(255,255,255,.3);font-size:.6rem;letter-spacing:2px;display:block;margin-top:2px}
    .nav-section{padding:16px 0}
    .nav-label{font-size:.6rem;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.2);padding:8px 24px}
    .nav-item{display:flex;align-items:center;gap:12px;padding:10px 24px;color:rgba(255,255,255,.5);text-decoration:none;font-size:.85rem;transition:all .2s;border-left:2px solid transparent}
    .nav-item:hover,.nav-item.active{color:#fff;background:rgba(200,134,10,.08);border-left-color:var(--gold)}
    .nav-item .icon{font-size:1rem;width:18px;text-align:center}
    .sidebar-footer{margin-top:auto;padding:20px 24px;border-top:1px solid rgba(255,255,255,.06)}
    .user-info{display:flex;align-items:center;gap:10px}
    .user-avatar{width:34px;height:34px;border-radius:50%;background:rgba(200,134,10,.2);display:flex;align-items:center;justify-content:center;color:var(--gold);font-weight:700;font-size:.85rem}
    .user-name{font-size:.82rem;color:rgba(255,255,255,.7)}
    .user-role{font-size:.65rem;color:var(--gold);letter-spacing:1px;text-transform:uppercase}
    /* MAIN */
    .main{margin-left:240px;flex:1;display:flex;flex-direction:column}
    .topbar{background:var(--char);border-bottom:1px solid rgba(255,255,255,.06);padding:14px 28px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:10}
    .page-title{font-family:'Playfair Display',serif;font-size:1.2rem;color:#fff}
    .topbar-right{display:flex;align-items:center;gap:12px}
    .btn-logout{background:transparent;border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.5);padding:7px 16px;border-radius:4px;font-size:.78rem;cursor:pointer;text-decoration:none;transition:all .2s}
    .btn-logout:hover{border-color:var(--danger);color:var(--danger)}
    .content{padding:28px;flex:1}
    /* CARDS */
    .stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-bottom:28px}
    .stat-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:20px 22px}
    .stat-num{font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:var(--gold-lt)}
    .stat-label{font-size:.72rem;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.35);margin-top:4px}
    /* TABLE */
    .card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:8px;overflow:hidden}
    .card-header{padding:18px 22px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:space-between}
    .card-title{font-size:.92rem;font-weight:600;color:#fff;letter-spacing:.3px}
    table{width:100%;border-collapse:collapse}
    th{background:rgba(255,255,255,.04);font-size:.7rem;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.35);padding:12px 16px;text-align:left}
    td{padding:12px 16px;border-bottom:1px solid rgba(255,255,255,.04);font-size:.85rem;color:rgba(255,255,255,.7)}
    tr:last-child td{border-bottom:none}
    tr:hover td{background:rgba(255,255,255,.02)}
    /* BADGES */
    .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.68rem;letter-spacing:1px;text-transform:uppercase;font-weight:500}
    .badge-pending{background:#d29922/20;color:#d29922;background-color:rgba(210,153,34,.15)}
    .badge-confirmed{background:rgba(45,164,78,.15);color:#2da44e}
    .badge-cancelled{background:rgba(207,34,46,.15);color:#cf222e}
    .badge-completed{background:rgba(200,134,10,.15);color:var(--gold-lt)}
    .badge-admin{background:rgba(200,134,10,.15);color:var(--gold-lt)}
    .badge-user{background:rgba(255,255,255,.08);color:rgba(255,255,255,.5)}
    /* BUTTONS */
    .btn{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:4px;font-size:.78rem;letter-spacing:.5px;cursor:pointer;border:none;text-decoration:none;font-family:'DM Sans',sans-serif;transition:all .2s}
    .btn-primary{background:var(--gold);color:#fff}.btn-primary:hover{background:var(--gold-lt)}
    .btn-danger{background:rgba(207,34,46,.15);color:#cf222e;border:1px solid rgba(207,34,46,.3)}.btn-danger:hover{background:rgba(207,34,46,.3)}
    .btn-success{background:rgba(45,164,78,.15);color:#2da44e;border:1px solid rgba(45,164,78,.3)}.btn-success:hover{background:rgba(45,164,78,.3)}
    .btn-sm{padding:5px 12px;font-size:.72rem}
    /* FORMS */
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .form-group{margin-bottom:16px}
    .form-group label{display:block;font-size:.72rem;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:6px}
    .form-control{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;padding:10px 14px;border-radius:4px;font-family:'DM Sans',sans-serif;font-size:.88rem;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:var(--gold)}
    .form-control option{background:#1a1814}
    /* FLASH */
    .flash{padding:12px 18px;border-radius:4px;margin-bottom:20px;font-size:.85rem}
    .flash-success{background:rgba(45,164,78,.15);border:1px solid rgba(45,164,78,.3);color:#2da44e}
    .flash-error{background:rgba(207,34,46,.15);border:1px solid rgba(207,34,46,.3);color:#cf222e}
    /* MODAL */
    .modal{display:none;position:fixed;inset:0;z-index:100;background:rgba(0,0,0,.7);align-items:center;justify-content:center}
    .modal.open{display:flex}
    .modal-box{background:#1a1814;border:1px solid rgba(200,134,10,.2);border-radius:8px;padding:32px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto}
    .modal-title{font-family:'Playfair Display',serif;font-size:1.2rem;color:#fff;margin-bottom:20px}
    @media(max-width:768px){.sidebar{display:none}.main{margin-left:0}}
  </style>
</head>
<body>
<nav class="sidebar">
  <div class="sidebar-logo">
    <h1>Terminal 1</h1>
    <small>Admin Panel</small>
  </div>
  <div class="nav-section">
    <div class="nav-label">Overview</div>
    <a class="nav-item <?= $activePage==='dashboard'?'active':'' ?>" href="/admin"><span class="icon">⬡</span> Dashboard</a>
  </div>
  <div class="nav-section">
    <div class="nav-label">Manage</div>
    <a class="nav-item <?= $activePage==='bookings'?'active':'' ?>" href="/admin/bookings"><span class="icon">📅</span> Bookings</a>
    <a class="nav-item <?= $activePage==='menu'?'active':'' ?>" href="/admin/menu"><span class="icon">🍽</span> Menu</a>
    <a class="nav-item <?= $activePage==='users'?'active':'' ?>" href="/admin/users"><span class="icon">👥</span> Users</a>
  </div>
  <div class="nav-section">
    <div class="nav-label">Site</div>
    <a class="nav-item" href="/" target="_blank"><span class="icon">↗</span> View Website</a>
  </div>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="user-avatar"><?= strtoupper(substr(authUser()['name'],0,1)) ?></div>
      <div>
        <div class="user-name"><?= e(authUser()['name']) ?></div>
        <div class="user-role">Admin</div>
      </div>
    </div>
  </div>
</nav>
<div class="main">
  <div class="topbar">
    <div class="page-title"><?= e($pageTitle ?? 'Dashboard') ?></div>
    <div class="topbar-right">
      <a class="btn-logout" href="/auth/logout">Logout</a>
    </div>
  </div>
  <div class="content">
