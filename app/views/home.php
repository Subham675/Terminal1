<?php
// Load menu from DB grouped by category
$menuGrouped = [];
try {
    $menuGrouped = MenuItem::byCategory();
} catch(Exception $e) {
    // DB not yet connected — will show empty menu gracefully
}

$user = authUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Terminal 1 — The Startup Canteen</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500&family=Cormorant+Garamond:ital,wght@1,400;1,600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --gold:#C8860A; --gold-lt:#E8A820; --dark:#111009;
      --charcoal:#1E1C18; --cream:#F6F1E8; --warm:#EDE5D4;
      --text:#2D2A24; --muted:#7A7264;
    }
    html { scroll-behavior: smooth; }
    body { font-family:'DM Sans',sans-serif; background:var(--cream); color:var(--text); overflow-x:hidden; }

    /* ─── NAV ─── */
    nav { position:fixed; top:0; left:0; right:0; z-index:999; display:flex; align-items:center; justify-content:space-between; padding:18px 5%; background:rgba(17,16,9,.92); backdrop-filter:blur(10px); border-bottom:1px solid rgba(200,134,10,.2); transition:padding .3s; }
    .nav-logo { font-family:'Playfair Display',serif; font-size:1.4rem; font-weight:900; color:var(--gold-lt); letter-spacing:2px; text-decoration:none; }
    .nav-logo span { color:#fff; font-weight:400; font-style:italic; font-size:.75rem; display:block; letter-spacing:3px; }
    .nav-links { display:flex; gap:32px; list-style:none; }
    .nav-links a { color:rgba(255,255,255,.7); text-decoration:none; font-size:.85rem; letter-spacing:1.5px; text-transform:uppercase; transition:color .2s; }
    .nav-links a:hover { color:var(--gold-lt); }
    .nav-right { display:flex; align-items:center; gap:10px; }
    .nav-user { color:rgba(255,255,255,.6); font-size:.8rem; }
    .nav-cta { background:var(--gold); color:#fff; padding:9px 22px; border-radius:2px; font-size:.8rem; letter-spacing:1px; text-transform:uppercase; text-decoration:none; transition:background .2s; }
    .nav-cta:hover { background:var(--gold-lt); }
    .nav-link-ghost { color:rgba(255,255,255,.6); font-size:.8rem; letter-spacing:1px; text-transform:uppercase; text-decoration:none; padding:9px 16px; border:1px solid rgba(255,255,255,.15); border-radius:2px; transition:all .2s; }
    .nav-link-ghost:hover { border-color:var(--gold); color:var(--gold); }
    .hamburger { display:none; flex-direction:column; gap:5px; cursor:pointer; }
    .hamburger span { width:24px; height:2px; background:#fff; display:block; }

    /* ─── TOAST ─── */
    #toast { position:fixed; bottom:28px; left:50%; transform:translateX(-50%) translateY(80px); z-index:9999; padding:14px 28px; border-radius:4px; font-size:.88rem; letter-spacing:.5px; opacity:0; transition:all .4s; pointer-events:none; white-space:nowrap; }
    #toast.show { transform:translateX(-50%) translateY(0); opacity:1; }
    #toast.success { background:#1a3a22; border:1px solid rgba(45,164,78,.4); color:#4caf70; }
    #toast.error   { background:#3a1a1a; border:1px solid rgba(207,34,46,.4); color:#e05464; }

    /* ─── HERO ─── */
    .hero { min-height:100vh; background:var(--dark); display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden; text-align:center; padding:120px 5% 80px; }
    .hero-bg { position:absolute; inset:0; background:radial-gradient(ellipse 60% 50% at 50% 60%,rgba(200,134,10,.18) 0%,transparent 70%),radial-gradient(ellipse 80% 40% at 20% 20%,rgba(212,98,42,.08) 0%,transparent 60%); }
    .hero-grain { position:absolute; inset:0; opacity:.04; background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); background-size:200px; }
    .hero-line { position:absolute; left:50%; top:0; bottom:0; width:1px; background:linear-gradient(to bottom,transparent,rgba(200,134,10,.4),transparent); }
    .hero-content { position:relative; z-index:2; max-width:760px; }
    .hero-eyebrow { display:inline-block; font-size:.72rem; letter-spacing:4px; text-transform:uppercase; color:var(--gold); border:1px solid rgba(200,134,10,.4); padding:6px 18px; border-radius:50px; margin-bottom:28px; }
    .hero h1 { font-family:'Playfair Display',serif; font-size:clamp(3rem,9vw,7rem); font-weight:900; line-height:.95; color:#fff; letter-spacing:-1px; margin-bottom:10px; }
    .hero h1 em { font-style:italic; font-weight:400; color:var(--gold-lt); display:block; font-size:.55em; }
    .hero-sub { font-family:'Cormorant Garamond',serif; font-style:italic; font-size:1.25rem; color:rgba(255,255,255,.55); margin:20px 0 40px; letter-spacing:.5px; line-height:1.6; }
    .hero-btns { display:flex; gap:14px; justify-content:center; flex-wrap:wrap; }
    .btn-primary { background:var(--gold); color:#fff; padding:14px 34px; font-size:.85rem; letter-spacing:1.5px; text-transform:uppercase; text-decoration:none; border-radius:2px; transition:all .25s; }
    .btn-primary:hover { background:var(--gold-lt); transform:translateY(-2px); }
    .btn-ghost { border:1px solid rgba(255,255,255,.3); color:rgba(255,255,255,.8); padding:14px 34px; font-size:.85rem; letter-spacing:1.5px; text-transform:uppercase; text-decoration:none; border-radius:2px; transition:all .25s; }
    .btn-ghost:hover { border-color:var(--gold); color:var(--gold); }
    .hero-scroll { position:absolute; bottom:30px; left:50%; transform:translateX(-50%); display:flex; flex-direction:column; align-items:center; gap:8px; color:rgba(255,255,255,.3); font-size:.65rem; letter-spacing:2px; text-transform:uppercase; }
    .scroll-dot { width:6px; height:24px; border:1px solid rgba(255,255,255,.2); border-radius:10px; position:relative; overflow:hidden; }
    .scroll-dot::after { content:''; position:absolute; top:3px; left:50%; transform:translateX(-50%); width:2px; height:6px; background:var(--gold); border-radius:2px; animation:scrollAnim 2s infinite; }
    @keyframes scrollAnim { 0%{top:3px;opacity:1} 100%{top:14px;opacity:0} }

    /* ─── SECTIONS ─── */
    section { padding:100px 5%; }
    .section-tag { font-size:.72rem; letter-spacing:4px; text-transform:uppercase; color:var(--gold); display:flex; align-items:center; gap:12px; margin-bottom:16px; }
    .section-tag::before { content:''; display:block; width:32px; height:1px; background:var(--gold); }
    .section-title { font-family:'Playfair Display',serif; font-size:clamp(2rem,4vw,3.2rem); font-weight:700; line-height:1.15; margin-bottom:20px; }
    .divider { width:48px; height:2px; background:linear-gradient(to right,var(--gold),transparent); margin:20px 0 36px; }

    /* ─── ABOUT ─── */
    #about { background:var(--charcoal); }
    .about-grid { display:grid; grid-template-columns:1fr 1fr; gap:80px; align-items:center; max-width:1100px; margin:0 auto; }
    .about-text .section-title { color:#fff; }
    .about-text p { color:rgba(255,255,255,.6); line-height:1.85; font-size:.95rem; margin-bottom:18px; }
    .about-stats { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-top:40px; }
    .stat-card { border:1px solid rgba(200,134,10,.25); padding:24px; border-radius:4px; background:rgba(200,134,10,.05); }
    .stat-num { font-family:'Playfair Display',serif; font-size:2.4rem; font-weight:900; color:var(--gold-lt); line-height:1; }
    .stat-label { font-size:.75rem; letter-spacing:2px; text-transform:uppercase; color:rgba(255,255,255,.4); margin-top:6px; }
    .about-visual { display:grid; grid-template-columns:1fr 1fr; grid-template-rows:220px 220px; gap:12px; }
    .av-1 { grid-column:1/3; }
    .photo-card { border-radius:4px; overflow:hidden; background:var(--charcoal); position:relative; }
    .photo-card img { width:100%; height:100%; object-fit:cover; transition:transform .6s; display:block; }
    .photo-card:hover img { transform:scale(1.05); }
    .photo-card::after { content:''; position:absolute; inset:0; background:linear-gradient(to top,rgba(0,0,0,.4),transparent); }

    /* ─── GALLERY ─── */
    #gallery { background:var(--cream); }
    .gallery-header { text-align:center; margin-bottom:60px; }
    .gallery-grid { display:grid; grid-template-columns:repeat(4,1fr); grid-template-rows:260px 260px; gap:12px; max-width:1200px; margin:0 auto; }
    .gallery-grid .g1 { grid-column:1/3; } .gallery-grid .g3 { grid-column:4/5; grid-row:1/3; } .gallery-grid .g5 { grid-column:2/4; }
    .g-item { border-radius:4px; overflow:hidden; position:relative; cursor:pointer; }
    .g-item img { width:100%; height:100%; object-fit:cover; transition:transform .6s ease; display:block; }
    .g-item:hover img { transform:scale(1.08); }
    .g-overlay { position:absolute; inset:0; background:rgba(17,16,9,.5); opacity:0; transition:opacity .3s; display:flex; align-items:center; justify-content:center; }
    .g-item:hover .g-overlay { opacity:1; }
    .g-overlay span { color:#fff; font-size:.75rem; letter-spacing:3px; text-transform:uppercase; border:1px solid rgba(255,255,255,.5); padding:8px 18px; }

    /* ─── MENU ─── */
    #menu { background:var(--dark); }
    .menu-header { text-align:center; max-width:600px; margin:0 auto 60px; }
    .menu-header .section-title { color:#fff; }
    .menu-header p { color:rgba(255,255,255,.5); line-height:1.7; }
    .menu-tabs { display:flex; gap:4px; justify-content:center; flex-wrap:wrap; margin-bottom:50px; }
    .tab-btn { background:transparent; border:1px solid rgba(200,134,10,.3); color:rgba(255,255,255,.5); padding:10px 24px; font-family:'DM Sans',sans-serif; font-size:.78rem; letter-spacing:2px; text-transform:uppercase; cursor:pointer; border-radius:2px; transition:all .2s; }
    .tab-btn.active,.tab-btn:hover { background:var(--gold); border-color:var(--gold); color:#fff; }
    .menu-panel { display:none; max-width:860px; margin:0 auto; }
    .menu-panel.active { display:block; }
    .menu-item { display:flex; align-items:baseline; justify-content:space-between; padding:18px 0; border-bottom:1px solid rgba(255,255,255,.07); gap:20px; }
    .menu-item:last-child { border-bottom:none; }
    .mi-left { flex:1; }
    .mi-name { font-family:'Playfair Display',serif; font-size:1.05rem; color:#fff; margin-bottom:4px; }
    .mi-desc { font-size:.8rem; color:rgba(255,255,255,.35); line-height:1.5; }
    .mi-badge { font-size:.6rem; letter-spacing:1.5px; text-transform:uppercase; background:rgba(200,134,10,.2); color:var(--gold); padding:2px 8px; border-radius:20px; margin-left:8px; vertical-align:middle; }
    .mi-veg { display:inline-block; width:12px; height:12px; border:1.5px solid #2da44e; border-radius:2px; position:relative; margin-left:6px; vertical-align:middle; }
    .mi-veg::after { content:''; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:6px; height:6px; background:#2da44e; border-radius:50%; }
    .mi-price { font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; color:var(--gold-lt); white-space:nowrap; }
    .mi-dots { flex:1; border-bottom:1px dotted rgba(255,255,255,.12); margin:0 12px; min-width:20px; }
    .unavailable { opacity:.4; }

    /* ─── CELEBRATIONS ─── */
    #celebrations { background:var(--warm); }
    .cel-grid { display:grid; grid-template-columns:1fr 1fr; gap:60px; align-items:center; max-width:1100px; margin:0 auto; }
    .cel-text p { color:var(--muted); line-height:1.85; margin-bottom:18px; }
    .cel-features { list-style:none; margin-top:28px; }
    .cel-features li { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid rgba(0,0,0,.06); font-size:.9rem; color:var(--text); }
    .cel-features li::before { content:'◆'; color:var(--gold); font-size:.5rem; }
    .cel-mosaic { display:grid; grid-template-columns:1fr 1fr; grid-template-rows:200px 200px; gap:10px; }
    .cel-mosaic .cm1 { grid-row:1/3; }
    .cm-item { border-radius:4px; overflow:hidden; }
    .cm-item img { width:100%; height:100%; object-fit:cover; display:block; }

    /* ─── REVIEWS ─── */
    #reviews { background:var(--charcoal); }
    .reviews-header { text-align:center; margin-bottom:60px; }
    .reviews-header .section-title { color:#fff; }
    .reviews-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; max-width:1100px; margin:0 auto; }
    .review-card { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:4px; padding:32px; transition:border-color .3s,transform .3s; }
    .review-card:hover { border-color:rgba(200,134,10,.4); transform:translateY(-4px); }
    .stars { color:var(--gold-lt); font-size:1rem; margin-bottom:18px; }
    .review-text { color:rgba(255,255,255,.65); line-height:1.75; margin-bottom:24px; font-family:'Cormorant Garamond',serif; font-style:italic; font-size:1.05rem; }
    .review-author { display:flex; align-items:center; gap:12px; }
    .author-avatar { width:40px; height:40px; border-radius:50%; background:rgba(200,134,10,.2); display:flex; align-items:center; justify-content:center; font-family:'Playfair Display',serif; color:var(--gold); font-weight:700; font-size:.9rem; }
    .author-name { color:#fff; font-size:.85rem; font-weight:500; }
    .author-date { color:rgba(255,255,255,.3); font-size:.75rem; margin-top:2px; }

    /* ─── CONTACT ─── */
    #contact { background:var(--dark); }
    .contact-wrapper { max-width:1000px; margin:0 auto; display:grid; grid-template-columns:1fr 1.2fr; gap:80px; align-items:start; }
    .contact-info .section-title { color:#fff; }
    .contact-info p { color:rgba(255,255,255,.5); line-height:1.8; margin-bottom:36px; }
    .info-item { display:flex; gap:16px; margin-bottom:24px; align-items:flex-start; }
    .info-icon { width:40px; height:40px; border-radius:4px; background:rgba(200,134,10,.15); display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
    .info-label { font-size:.7rem; letter-spacing:2px; text-transform:uppercase; color:var(--gold); }
    .info-value { color:rgba(255,255,255,.7); font-size:.9rem; margin-top:4px; line-height:1.5; }
    .contact-form { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:4px; padding:40px; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .form-group { margin-bottom:20px; }
    .form-group label { display:block; font-size:.72rem; letter-spacing:2px; text-transform:uppercase; color:rgba(255,255,255,.4); margin-bottom:8px; }
    .form-group input,.form-group textarea,.form-group select { width:100%; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); color:#fff; padding:12px 16px; font-family:'DM Sans',sans-serif; font-size:.9rem; border-radius:2px; outline:none; transition:border-color .2s; }
    .form-group input:focus,.form-group textarea:focus,.form-group select:focus { border-color:var(--gold); }
    .form-group textarea { resize:vertical; min-height:100px; }
    .form-group select option { background:var(--dark); }
    .form-submit { width:100%; background:var(--gold); border:none; color:#fff; padding:14px; font-family:'DM Sans',sans-serif; font-size:.82rem; letter-spacing:2px; text-transform:uppercase; cursor:pointer; border-radius:2px; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:10px; }
    .form-submit:hover:not(:disabled) { background:var(--gold-lt); }
    .form-submit:disabled { opacity:.6; cursor:not-allowed; }
    .spinner { width:16px; height:16px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; display:none; }
    @keyframes spin { to{transform:rotate(360deg)} }

    /* ─── FOOTER ─── */
    footer { background:#0A0906; padding:50px 5% 30px; border-top:1px solid rgba(200,134,10,.15); }
    .footer-inner { max-width:1100px; margin:0 auto; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:24px; padding-bottom:30px; border-bottom:1px solid rgba(255,255,255,.06); }
    .footer-logo { font-family:'Playfair Display',serif; font-size:1.6rem; font-weight:900; color:var(--gold-lt); }
    .footer-logo small { color:rgba(255,255,255,.3); font-size:.65rem; display:block; letter-spacing:3px; }
    .footer-links { display:flex; gap:28px; flex-wrap:wrap; }
    .footer-links a { color:rgba(255,255,255,.4); font-size:.8rem; text-decoration:none; transition:color .2s; }
    .footer-links a:hover { color:var(--gold); }
    .footer-copy { color:rgba(255,255,255,.2); font-size:.78rem; text-align:center; margin-top:24px; max-width:1100px; margin-left:auto; margin-right:auto; }

    /* ─── MOBILE MENU ─── */
    .mobile-menu { display:none; position:fixed; inset:0; z-index:998; background:var(--charcoal); flex-direction:column; align-items:center; justify-content:center; gap:36px; }
    .mobile-menu.open { display:flex; }
    .mobile-menu a { font-family:'Playfair Display',serif; font-size:1.8rem; color:#fff; text-decoration:none; }
    .mobile-menu a:hover { color:var(--gold-lt); }
    .close-btn { position:absolute; top:24px; right:5%; background:none; border:none; color:#fff; font-size:1.6rem; cursor:pointer; }

    /* ─── ANIMATIONS ─── */
    .fade-up { opacity:0; transform:translateY(28px); transition:opacity .7s ease,transform .7s ease; }
    .fade-up.visible { opacity:1; transform:translateY(0); }
    .ph { background:repeating-linear-gradient(45deg,rgba(200,134,10,.07) 0px,rgba(200,134,10,.07) 1px,transparent 1px,transparent 12px),var(--charcoal); }

    /* ─── RESPONSIVE ─── */
    @media(max-width:900px) {
      .nav-links,.nav-cta,.nav-link-ghost { display:none; }
      .hamburger { display:flex; }
      .about-grid,.cel-grid,.contact-wrapper { grid-template-columns:1fr; gap:40px; }
      .gallery-grid { grid-template-columns:1fr 1fr; grid-template-rows:auto; }
      .gallery-grid .g1,.gallery-grid .g2,.gallery-grid .g3,.gallery-grid .g4,.gallery-grid .g5 { grid-column:auto; grid-row:auto; height:220px; }
      .reviews-grid { grid-template-columns:1fr; }
      .form-row { grid-template-columns:1fr; }
      .about-visual { grid-template-rows:160px 160px; }
      .cel-mosaic { grid-template-rows:160px 160px; }
    }
  </style>
</head>
<body>

<!-- TOAST NOTIFICATION -->
<div id="toast"></div>

<!-- NAV -->
<nav id="navbar">
  <a class="nav-logo" href="/">Terminal 1 <span>The Startup Canteen</span></a>
  <ul class="nav-links">
    <li><a href="#about">About</a></li>
    <li><a href="#gallery">Gallery</a></li>
    <li><a href="#menu">Menu</a></li>
    <li><a href="#celebrations">Celebrations</a></li>
    <li><a href="#reviews">Reviews</a></li>
  </ul>
  <div class="nav-right">
    <?php if($user): ?>
      <?php if($user['role'] === 'admin'): ?>
        <a class="nav-link-ghost" href="/admin">⬡ Admin Panel</a>
      <?php endif; ?>
      <span class="nav-user">Hi, <?= e(explode(' ', $user['name'])[0]) ?></span>
      <a class="nav-cta" href="/auth/logout">Logout</a>
    <?php else: ?>
      <a class="nav-link-ghost" href="/auth/login">Login</a>
      <a class="nav-cta" href="#contact">Reserve a Table</a>
    <?php endif; ?>
  </div>
  <div class="hamburger" onclick="openMenu()">
    <span></span><span></span><span></span>
  </div>
</nav>

<!-- MOBILE MENU -->
<div class="mobile-menu" id="mobileMenu">
  <button class="close-btn" onclick="closeMenu()">✕</button>
  <a href="#about" onclick="closeMenu()">About</a>
  <a href="#gallery" onclick="closeMenu()">Gallery</a>
  <a href="#menu" onclick="closeMenu()">Menu</a>
  <a href="#celebrations" onclick="closeMenu()">Celebrations</a>
  <a href="#reviews" onclick="closeMenu()">Reviews</a>
  <a href="#contact" onclick="closeMenu()">Reserve</a>
  <?php if($user): ?>
    <?php if($user['role']==='admin'): ?><a href="/admin" onclick="closeMenu()">Admin Panel</a><?php endif; ?>
    <a href="/auth/logout" onclick="closeMenu()">Logout</a>
  <?php else: ?>
    <a href="/auth/login" onclick="closeMenu()">Login</a>
  <?php endif; ?>
</div>

<!-- HERO -->
<section class="hero" id="hero">
  <div class="hero-bg"></div>
  <div class="hero-grain"></div>
  <div class="hero-line"></div>
  <div class="hero-content fade-up visible">
    <div class="hero-eyebrow">Cooch Behar, West Bengal</div>
    <h1>Terminal<em>The Startup Canteen</em>1</h1>
    <p class="hero-sub">Where every meal is a celebration. Authentic Bengali flavors<br>with a soul that sparks stories.</p>
    <div class="hero-btns">
      <a class="btn-primary" href="#menu">Explore Menu</a>
      <a class="btn-ghost" href="#contact">Book Your Table</a>
    </div>
  </div>
  <div class="hero-scroll"><div class="scroll-dot"></div>Scroll</div>
</section>

<!-- ABOUT -->
<section id="about">
  <div class="about-grid">
    <div class="about-text fade-up">
      <div class="section-tag">Our Story</div>
      <h2 class="section-title">More than a canteen,<br>it's a vibe.</h2>
      <div class="divider"></div>
      <p>Terminal 1 — The Startup Canteen was born from a simple idea: great food, great company, and a space that feels alive. Tucked in the heart of Cooch Behar, we blend Bengali home-cooking with a quirky, artsy atmosphere.</p>
      <p>Our walls tell stories. From black-and-white portraits to post-it notes written by hundreds of guests — Terminal 1 is a place people return to, again and again.</p>
      <div class="about-stats">
        <div class="stat-card"><div class="stat-num">500+</div><div class="stat-label">Happy Tables</div></div>
        <div class="stat-card"><div class="stat-num"><?= !empty($menuGrouped) ? array_sum(array_map('count',$menuGrouped)) : '30+' ?></div><div class="stat-label">Menu Items</div></div>
        <div class="stat-card"><div class="stat-num">4.7★</div><div class="stat-label">Google Rating</div></div>
        <div class="stat-card"><div class="stat-num">100%</div><div class="stat-label">Made with Love</div></div>
      </div>
    </div>
    <div class="about-visual fade-up">
      <div class="photo-card av-1" style="height:220px"><img src="/images/interior.png" alt="Terminal 1 interior" onerror="this.parentElement.classList.add('ph')"/></div>
      <div class="photo-card" style="height:220px"><img src="/images/exterior.png" alt="Terminal 1 exterior" onerror="this.parentElement.classList.add('ph')"/></div>
      <div class="photo-card" style="height:220px"><img src="/images/food1.png" alt="Food spread" onerror="this.parentElement.classList.add('ph')"/></div>
    </div>
  </div>
</section>

<!-- GALLERY -->
<section id="gallery">
  <div class="gallery-header fade-up">
    <div class="section-tag" style="justify-content:center">Our World</div>
    <h2 class="section-title">Moments at Terminal 1</h2>
    <div class="divider" style="margin:20px auto"></div>
  </div>
  <div class="gallery-grid fade-up">
    <div class="g-item g1"><img src="/images/feast.png" alt="Family feast" onerror="this.style.background='#2a2820'"/><div class="g-overlay"><span>The Feast Table</span></div></div>
    <div class="g-item g2"><img src="/images/biryani.png" alt="Biryani" onerror="this.style.background='#2a2820'"/><div class="g-overlay"><span>Biryani</span></div></div>
    <div class="g-item g3"><img src="/images/bowl.png" alt="Fried rice bowl" onerror="this.style.background='#2a2820'"/><div class="g-overlay"><span>Fried Rice Bowl</span></div></div>
    <div class="g-item g4"><img src="/images/vibe.png" alt="Interior post-it wall" onerror="this.style.background='#2a2820'"/><div class="g-overlay"><span>The Vibe</span></div></div>
    <div class="g-item g5"><img src="/images/celebration.png" alt="Celebration dinner" onerror="this.style.background='#2a2820'"/><div class="g-overlay"><span>Special Evening</span></div></div>
  </div>
</section>

<!-- MENU — loaded live from DB -->
<section id="menu">
  <div class="menu-header fade-up">
    <div class="section-tag" style="justify-content:center">What We Serve</div>
    <h2 class="section-title">Terminal's Menu</h2>
    <p>From fiery Dhonkami Chicken to perfectly fragrant Kashmiri Polao — every dish is crafted with care.</p>
  </div>

  <?php if(!empty($menuGrouped)): ?>
  <div class="menu-tabs fade-up">
    <?php $first=true; foreach($menuGrouped as $catName => $items): ?>
      <button class="tab-btn <?= $first?'active':'' ?>"
              onclick="switchTab('cat-<?= md5($catName) ?>',this)">
        <?= e($catName) ?>
      </button>
    <?php $first=false; endforeach; ?>
  </div>

  <?php $first=true; foreach($menuGrouped as $catName => $items): ?>
  <div class="menu-panel <?= $first?'active':'' ?>" id="tab-cat-<?= md5($catName) ?>">
    <?php foreach($items as $item): ?>
    <div class="menu-item <?= (!$item['is_available'] || $item['is_available']==='f') ? 'unavailable' : '' ?>">
      <div class="mi-left">
        <div class="mi-name">
          <?= e($item['name']) ?>
          <?php if($item['badge']): ?><span class="mi-badge"><?= e($item['badge']) ?></span><?php endif; ?>
          <?php if($item['is_veg']==='t'||$item['is_veg']===true): ?><span class="mi-veg" title="Vegetarian"></span><?php endif; ?>
          <?php if(!$item['is_available']||$item['is_available']==='f'): ?><span class="mi-badge" style="background:rgba(207,34,46,.15);color:#cf222e">Unavailable</span><?php endif; ?>
        </div>
        <?php if($item['description']): ?><div class="mi-desc"><?= e($item['description']) ?></div><?php endif; ?>
      </div>
      <div class="mi-dots"></div>
      <div class="mi-price">
        <?php if($item['price'] > 0): ?>₹<?= number_format((float)$item['price'],0) ?><?php else: ?>Call Us<?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php $first=false; endforeach; ?>

  <?php else: ?>
  <!-- Fallback static menu if DB not connected -->
  <div class="menu-tabs fade-up">
    <button class="tab-btn active" onclick="switchTab('specials',this)">Chef's Specials</button>
    <button class="tab-btn" onclick="switchTab('rice',this)">Rice & Polao</button>
  </div>
  <div class="menu-panel active" id="tab-specials">
    <?php foreach([['Dhonkami Chicken 4.0','2 Naan + 1 Kulcha + 2 Corn','850','Signature'],['Dhonkami Chicken','Our legendary smoky bold chicken','690',''],['Sendori Chicken','Rich slow-cooked chicken','600',''],['Jangli Chicken','Wild-spiced rustic chicken','630','']] as $r): ?>
    <div class="menu-item"><div class="mi-left"><div class="mi-name"><?= e($r[0]) ?><?php if($r[3]): ?> <span class="mi-badge"><?= e($r[3]) ?></span><?php endif; ?></div><?php if($r[1]): ?><div class="mi-desc"><?= e($r[1]) ?></div><?php endif; ?></div><div class="mi-dots"></div><div class="mi-price">₹<?= e($r[2]) ?></div></div>
    <?php endforeach; ?>
  </div>
  <div class="menu-panel" id="tab-rice">
    <?php foreach([['Kashmiri Polao','260'],['Mixed Fried Rice','230'],['Egg Chicken Fried Rice','210'],['Golden Garlic Fried Rice','200'],['Chicken Fried Rice','190'],['Schezwan Fried Rice','180'],['Egg Fried Rice','170'],['Steam Rice','70']] as $r): ?>
    <div class="menu-item"><div class="mi-left"><div class="mi-name"><?= e($r[0]) ?></div></div><div class="mi-dots"></div><div class="mi-price">₹<?= e($r[1]) ?></div></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<!-- CELEBRATIONS -->
<section id="celebrations">
  <div class="cel-grid">
    <div class="cel-text fade-up">
      <div class="section-tag">Special Occasions</div>
      <h2 class="section-title">Celebrate every<br>milestone here</h2>
      <div class="divider"></div>
      <p>Whether it's a birthday, anniversary, or simply a gathering with loved ones — Terminal 1 transforms your table into an experience.</p>
      <ul class="cel-features">
        <li>Marigold & floral table decoration</li>
        <li>Customised birthday cake arrangements</li>
        <li>Traditional Bengali celebration thali</li>
        <li>Intimate couple's dining setup</li>
        <li>Group family feast packages</li>
        <li>Photography-friendly ambience</li>
      </ul>
    </div>
    <div class="cel-mosaic fade-up">
      <div class="cm-item cm1"><img src="/images/exterior.png" alt="Celebration setup" style="height:100%;width:100%;object-fit:cover;" onerror="this.style.background='#d4c9b0'"/></div>
      <div class="cm-item"><img src="/images/couple.png" alt="Couple dining" style="height:200px;width:100%;object-fit:cover;" onerror="this.style.background='#c8b898'"/></div>
      <div class="cm-item"><img src="/images/feast.png" alt="Feast spread" style="height:200px;width:100%;object-fit:cover;" onerror="this.style.background='#bfac8c'"/></div>
    </div>
  </div>
</section>

<!-- REVIEWS -->
<section id="reviews">
  <div class="reviews-header fade-up">
    <div class="section-tag" style="justify-content:center">Guest Stories</div>
    <h2 class="section-title">What our guests say</h2>
    <div class="divider" style="margin:20px auto"></div>
  </div>
  <div class="reviews-grid fade-up">
    <div class="review-card"><div class="stars">★★★★★</div><p class="review-text">"The Dhonkami Chicken is like nothing else in Cooch Behar. The celebration setup they did for my wife's birthday was absolutely magical!"</p><div class="review-author"><div class="author-avatar">R</div><div><div class="author-name">Rajib Chakraborty</div><div class="author-date">Google Review</div></div></div></div>
    <div class="review-card"><div class="stars">★★★★★</div><p class="review-text">"Came for lunch, stayed for three hours. The vibe here is incredible — the post-it note wall, the portraits, the food. Terminal 1 is a gem."</p><div class="review-author"><div class="author-avatar">S</div><div><div class="author-name">Sunita Das</div><div class="author-date">Google Review</div></div></div></div>
    <div class="review-card"><div class="stars">★★★★★</div><p class="review-text">"The Bengali Thali here is an event in itself. Clay pots, fish curry, warm rice — it took me straight back to my grandmother's kitchen."</p><div class="review-author"><div class="author-avatar">A</div><div><div class="author-name">Ananya Roy</div><div class="author-date">Google Review</div></div></div></div>
  </div>
</section>

<!-- CONTACT / RESERVATION — posts to /bookings -->
<section id="contact">
  <div class="contact-wrapper">
    <div class="contact-info fade-up">
      <div class="section-tag">Find Us</div>
      <h2 class="section-title">Visit Terminal 1</h2>
      <div class="divider"></div>
      <?php if($user): ?>
        <p>Welcome back, <strong style="color:rgba(255,255,255,.8)"><?= e($user['name']) ?></strong>! Fill in the details below to reserve your table.</p>
      <?php else: ?>
        <p>We'd love to have you. Walk in anytime, or reserve your table for special occasions.</p>
      <?php endif; ?>
      <div class="info-item"><div class="info-icon">📍</div><div><div class="info-label">Address</div><div class="info-value">Terminal 1 — The Startup Canteen<br>Cooch Behar, West Bengal, India</div></div></div>
      <div class="info-item"><div class="info-icon">🕐</div><div><div class="info-label">Hours</div><div class="info-value">Monday – Sunday<br>11:00 AM – 10:00 PM</div></div></div>
      <div class="info-item"><div class="info-icon">📞</div><div><div class="info-label">Phone</div><div class="info-value">Call us to enquire about<br>celebration packages</div></div></div>
    </div>

    <div class="contact-form fade-up">
      <!-- Hidden CSRF token injected from PHP session -->
      <input type="hidden" id="csrf_token" value="<?= csrfToken() ?>">

      <div class="form-row">
        <div class="form-group">
          <label>Your Name</label>
          <input type="text" id="fname" placeholder="Rajib Das"
                 value="<?= $user ? e($user['name']) : '' ?>"/>
        </div>
        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" id="fphone" placeholder="+91 98765 00000"/>
        </div>
      </div>
      <div class="form-group">
        <label>Email (optional)</label>
        <input type="email" id="femail" placeholder="you@example.com"
               value="<?= $user ? e($user['email']) : '' ?>"/>
      </div>
      <div class="form-group">
        <label>Occasion</label>
        <select id="foccasion">
          <option value="">Select occasion</option>
          <option>Regular Dining</option>
          <option>Birthday</option>
          <option>Anniversary</option>
          <option>Family Gathering</option>
          <option>Other</option>
        </select>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Number of Guests</label>
          <input type="number" id="fguests" placeholder="e.g. 4" min="1"/>
        </div>
        <div class="form-group">
          <label>Preferred Date</label>
          <input type="date" id="fdate" min="<?= date('Y-m-d') ?>"/>
        </div>
      </div>
      <div class="form-group">
        <label>Message (Optional)</label>
        <textarea id="fmsg" placeholder="Any special requests or dietary needs..."></textarea>
      </div>

      <button class="form-submit" id="submitBtn" onclick="submitBooking()">
        <span id="btnText">Send Reservation Request</span>
        <div class="spinner" id="btnSpinner"></div>
      </button>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-logo">Terminal 1<small>The Startup Canteen</small></div>
    <div class="footer-links">
      <a href="#about">About</a><a href="#gallery">Gallery</a><a href="#menu">Menu</a>
      <a href="#celebrations">Celebrations</a><a href="#reviews">Reviews</a><a href="#contact">Contact</a>
      <?php if($user && $user['role']==='admin'): ?><a href="/admin">Admin</a><?php endif; ?>
    </div>
  </div>
  <p class="footer-copy">© <?= date('Y') ?> Terminal 1 — The Startup Canteen, Cooch Behar. All rights reserved.</p>
</footer>

<script>
// ── Fade-up on scroll ──
const obs = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.12 });
document.querySelectorAll('.fade-up').forEach(el => obs.observe(el));

// ── Menu tabs ──
function switchTab(id, btn) {
  document.querySelectorAll('.menu-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + id).classList.add('active');
  btn.classList.add('active');
}

// ── Mobile nav ──
function openMenu()  { document.getElementById('mobileMenu').classList.add('open'); }
function closeMenu() { document.getElementById('mobileMenu').classList.remove('open'); }

// ── Navbar shrink ──
window.addEventListener('scroll', () => {
  document.getElementById('navbar').style.padding = window.scrollY > 60 ? '12px 5%' : '18px 5%';
});

// ── Toast ──
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'show ' + type;
  setTimeout(() => { t.className = ''; }, 4000);
}

// ── Booking form → POST /bookings with CSRF ──
async function submitBooking() {
  const name   = document.getElementById('fname').value.trim();
  const phone  = document.getElementById('fphone').value.trim();
  const csrf   = document.getElementById('csrf_token').value;

  if(!name || !phone) {
    showToast('Please enter your name and phone number.', 'error');
    return;
  }

  const btn     = document.getElementById('submitBtn');
  const txt     = document.getElementById('btnText');
  const spinner = document.getElementById('btnSpinner');

  btn.disabled       = true;
  txt.textContent    = 'Sending...';
  spinner.style.display = 'block';

  try {
    const res = await fetch('/bookings', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': csrf,
      },
      body: new URLSearchParams({
        csrf_token:   csrf,
        name:         name,
        phone:        phone,
        email:        document.getElementById('femail').value.trim(),
        occasion:     document.getElementById('foccasion').value,
        guests:       document.getElementById('fguests').value || 1,
        booking_date: document.getElementById('fdate').value,
        message:      document.getElementById('fmsg').value.trim(),
      })
    });

    const data = await res.json();

    if(data.success) {
      showToast('✦ ' + data.message, 'success');
      // Reset form (keep name/email if logged in)
      document.getElementById('fphone').value = '';
      document.getElementById('foccasion').selectedIndex = 0;
      document.getElementById('fguests').value = '';
      document.getElementById('fdate').value = '';
      document.getElementById('fmsg').value = '';
    } else {
      showToast(data.message || 'Something went wrong. Please try again.', 'error');
    }
  } catch(err) {
    showToast('Network error. Please try again.', 'error');
  } finally {
    btn.disabled       = false;
    txt.textContent    = 'Send Reservation Request';
    spinner.style.display = 'none';
  }
}
</script>
</body>
</html>
