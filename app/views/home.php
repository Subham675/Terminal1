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
  <title>Terminal 1 | Authentic Dining in Cooch Behar</title>
  <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>"/>
  <link rel="alternate icon" href="<?= asset('favicon.ico') ?>"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500&family=Cormorant+Garamond:ital,wght@1,400;1,600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --gold:#C8860A; --gold-lt:#E8A820; --dark:#111009;
      --charcoal:#1E1C18; --cream:#F6F1E8; --warm:#EDE5D4;
      --text:#2D2A24; --muted:#7A7264;

      /* Standardised Typography Tokens */
      --font-display:'Playfair Display', Georgia, serif;
      --font-body:'DM Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      --font-accent:'Cormorant Garamond', Georgia, serif;

      --text-2xs: clamp(0.65rem, 0.62rem + 0.15vw, 0.72rem);
      --text-xs: clamp(0.72rem, 0.68rem + 0.2vw, 0.78rem);
      --text-sm: clamp(0.82rem, 0.78rem + 0.2vw, 0.88rem);
      --text-base: clamp(0.92rem, 0.88rem + 0.25vw, 1rem);
      --text-lg: clamp(1.05rem, 0.98rem + 0.35vw, 1.2rem);
      --text-xl: clamp(1.25rem, 1.15rem + 0.5vw, 1.45rem);
      --text-2xl: clamp(1.6rem, 1.35rem + 1.25vw, 2.25rem);
      --text-3xl: clamp(2rem, 1.6rem + 2vw, 3.2rem);
      --text-hero: clamp(2.8rem, 2rem + 4.5vw, 6.5rem);

      --leading-tight: 1.15;
      --leading-snug: 1.35;
      --leading-normal: 1.6;
      --leading-relaxed: 1.85;

      --tracking-wide: 1.5px;
      --tracking-wider: 2.5px;
      --tracking-widest: 4px;
    }
    html { scroll-behavior: smooth; max-width: 100%; overflow-x: clip; }
    body { font-family:var(--font-body); font-size:var(--text-base); line-height:var(--leading-normal); background:var(--cream); color:var(--text); max-width: 100%; overflow-x: clip; -webkit-font-smoothing: antialiased; }

    /* ─── NAV ─── */
    nav { position:fixed; top:0; left:0; right:0; z-index:999; display:flex; align-items:center; justify-content:space-between; padding:18px 5%; background:rgba(17,16,9,.92); backdrop-filter:blur(10px); border-bottom:1px solid rgba(200,134,10,.2); transition:padding .25s ease, background-color .25s ease; width:100%; }
    nav.nav-scrolled { padding:12px 5%; background:rgba(17,16,9,.97); }
    .nav-logo { font-family:var(--font-display); font-size:var(--text-xl); font-weight:900; color:var(--gold-lt); letter-spacing:var(--tracking-wide); text-decoration:none; }
    .nav-logo span { color:#fff; font-weight:400; font-style:italic; font-size:var(--text-2xs); display:block; letter-spacing:var(--tracking-wider); }
    .nav-links { display:flex; gap:32px; list-style:none; }
    .nav-links a { color:rgba(255,255,255,.7); text-decoration:none; font-size:var(--text-sm); letter-spacing:var(--tracking-wide); text-transform:uppercase; transition:color .2s ease; }
    .nav-links a:hover { color:var(--gold-lt); }
    .nav-right { display:flex; align-items:center; gap:10px; }
    .nav-user { color:rgba(255,255,255,.6); font-size:var(--text-xs); }
    .nav-cta { background:var(--gold); color:#fff; padding:9px 22px; border-radius:2px; font-size:var(--text-xs); letter-spacing:1px; text-transform:uppercase; text-decoration:none; transition:background-color .2s ease; }
    .nav-cta:hover { background:var(--gold-lt); }
    .nav-link-ghost { color:rgba(255,255,255,.6); font-size:var(--text-xs); letter-spacing:1px; text-transform:uppercase; text-decoration:none; padding:9px 16px; border:1px solid rgba(255,255,255,.15); border-radius:2px; transition:border-color .2s ease, color .2s ease; }
    .nav-link-ghost:hover { border-color:var(--gold); color:var(--gold); }
    .hamburger { display:none; flex-direction:column; gap:5px; cursor:pointer; }
    .hamburger span { width:24px; height:2px; background:#fff; display:block; }

    /* ─── TOAST (MOBILE OVERFLOW SAFE) ─── */
    #toast { position:fixed; bottom:28px; left:50%; transform:translate3d(-50%, 80px, 0); z-index:9999; padding:14px 24px; border-radius:4px; font-size:var(--text-sm); letter-spacing:.5px; opacity:0; transition:transform .3s ease, opacity .3s ease; pointer-events:none; max-width:min(90vw, 450px); box-sizing:border-box; text-align:center; word-break:break-word; }
    #toast.show { transform:translate3d(-50%, 0, 0); opacity:1; }
    #toast.success { background:#1a3a22; border:1px solid rgba(45,164,78,.4); color:#4caf70; }
    #toast.error   { background:#3a1a1a; border:1px solid rgba(207,34,46,.4); color:#e05464; }

    /* ─── HERO ─── */
    .hero { min-height:100vh; background:var(--dark); display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden; text-align:center; padding:120px 5% 80px; width:100%; box-sizing:border-box; }
    .hero-bg { position:absolute; inset:0; background:radial-gradient(ellipse 60% 50% at 50% 60%,rgba(200,134,10,.18) 0%,transparent 70%),radial-gradient(ellipse 80% 40% at 20% 20%,rgba(212,98,42,.08) 0%,transparent 60%); pointer-events:none; }
    .hero-grain { position:absolute; inset:0; opacity:.04; background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); background-size:200px; pointer-events:none; contain:strict; }
    .hero-line { position:absolute; left:50%; top:0; bottom:0; width:1px; background:linear-gradient(to bottom,transparent,rgba(200,134,10,.4),transparent); pointer-events:none; }
    .hero-content { position:relative; z-index:2; max-width:760px; width:100%; }
    .hero-eyebrow { display:inline-block; font-size:var(--text-xs); letter-spacing:var(--tracking-widest); text-transform:uppercase; color:var(--gold); border:1px solid rgba(200,134,10,.4); padding:6px 18px; border-radius:2px; margin-bottom:28px; }
    .hero h1 { font-family:var(--font-display); font-size:var(--text-hero); font-weight:900; line-height:.95; color:#fff; letter-spacing:-1px; margin-bottom:10px; }
    .hero h1 em { font-style:italic; font-weight:400; color:var(--gold-lt); display:block; font-size:.55em; line-height:1.2; }
    .hero-sub { font-family:var(--font-accent); font-style:italic; font-size:var(--text-xl); color:rgba(255,255,255,.65); margin:20px 0 40px; letter-spacing:.5px; line-height:var(--leading-normal); }
    .hero-btns { display:flex; gap:14px; justify-content:center; flex-wrap:wrap; }
    .btn-primary { background:var(--gold); color:#fff; padding:14px 34px; font-size:var(--text-sm); letter-spacing:var(--tracking-wide); text-transform:uppercase; text-decoration:none; border-radius:2px; transition:background-color .2s ease, transform .2s ease; will-change:transform; }
    .btn-primary:hover { background:var(--gold-lt); transform:translate3d(0, -2px, 0); }
    .btn-ghost { border:1px solid rgba(255,255,255,.3); color:rgba(255,255,255,.8); padding:14px 34px; font-size:var(--text-sm); letter-spacing:var(--tracking-wide); text-transform:uppercase; text-decoration:none; border-radius:2px; transition:border-color .2s ease, color .2s ease; }
    .btn-ghost:hover { border-color:var(--gold); color:var(--gold); }
    .hero-scroll { position:absolute; bottom:30px; left:50%; transform:translateX(-50%); display:flex; flex-direction:column; align-items:center; gap:8px; color:rgba(255,255,255,.3); font-size:var(--text-2xs); letter-spacing:var(--tracking-wider); text-transform:uppercase; pointer-events:none; }
    .scroll-dot { width:6px; height:24px; border:1px solid rgba(255,255,255,.2); border-radius:10px; position:relative; overflow:hidden; }
    /* Performance-optimized: GPU-composited transform instead of top layout thrash */
    .scroll-dot::after { content:''; position:absolute; top:3px; left:50%; width:2px; height:6px; background:var(--gold); border-radius:2px; transform:translate3d(-50%,0,0); animation:scrollAnim 2s infinite ease-out; will-change:transform, opacity; }
    @keyframes scrollAnim { 0%{ transform:translate3d(-50%,0,0); opacity:1; } 100%{ transform:translate3d(-50%,12px,0); opacity:0; } }

    /* ─── SECTIONS ─── */
    section { padding:100px 5%; width:100%; box-sizing:border-box; }
    .section-tag { font-size:var(--text-xs); letter-spacing:var(--tracking-widest); text-transform:uppercase; color:var(--gold); display:flex; align-items:center; gap:12px; margin-bottom:16px; }
    .section-tag::before { content:''; display:block; width:32px; height:1px; background:var(--gold); }
    .section-title { font-family:var(--font-display); font-size:var(--text-3xl); font-weight:700; line-height:var(--leading-tight); margin-bottom:20px; }
    .divider { width:48px; height:2px; background:linear-gradient(to right,var(--gold),transparent); margin:20px 0 36px; }

    /* ─── ABOUT ─── */
    #about { background:var(--charcoal); }
    .about-grid { display:grid; grid-template-columns:1fr 1fr; gap:80px; align-items:center; max-width:1100px; margin:0 auto; width:100%; }
    .about-text .section-title { color:#fff; }
    .about-text p { color:rgba(255,255,255,.6); line-height:var(--leading-relaxed); font-size:var(--text-base); margin-bottom:18px; }
    .about-pillars { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:36px; }
    .pillar-card { border:1px solid rgba(200,134,10,.25); padding:20px 22px; border-radius:2px; background:rgba(200,134,10,.04); }
    .pillar-title { font-family:var(--font-display); font-size:var(--text-base); font-weight:700; color:var(--gold-lt); margin-bottom:6px; }
    .pillar-desc { font-size:var(--text-xs); color:rgba(255,255,255,.6); line-height:var(--leading-normal); }
    .about-visual { display:grid; grid-template-columns:1fr 1fr; grid-template-rows:220px 220px; gap:12px; width:100%; }
    .av-1 { grid-column:1/3; }
    .photo-card { border-radius:4px; overflow:hidden; background:var(--charcoal); position:relative; transform:translateZ(0); }
    .photo-card img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; display:block; will-change:transform; backface-visibility:hidden; }
    .photo-card:hover img { transform:scale(1.04); }
    .photo-card::after { content:''; position:absolute; inset:0; background:linear-gradient(to top,rgba(0,0,0,.4),transparent); pointer-events:none; }

    /* ─── GALLERY ─── */
    #gallery { background:var(--cream); }
    .gallery-header { text-align:center; margin-bottom:60px; }
    .gallery-grid { display:grid; grid-template-columns:repeat(4,1fr); grid-template-rows:260px 260px; gap:12px; max-width:1200px; margin:0 auto; width:100%; }
    .gallery-grid .g1 { grid-column:1/3; } .gallery-grid .g3 { grid-column:4/5; grid-row:1/3; } .gallery-grid .g5 { grid-column:2/4; }
    .g-item { border-radius:4px; overflow:hidden; position:relative; cursor:pointer; transform:translateZ(0); }
    .g-item img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; display:block; will-change:transform; backface-visibility:hidden; }
    .g-item:hover img { transform:scale(1.05); }
    .g-overlay { position:absolute; inset:0; background:rgba(17,16,9,.5); opacity:0; transition:opacity .25s ease; display:flex; align-items:center; justify-content:center; }
    .g-item:hover .g-overlay { opacity:1; }
    .g-overlay span { color:#fff; font-size:var(--text-xs); letter-spacing:var(--tracking-wider); text-transform:uppercase; border:1px solid rgba(255,255,255,.5); padding:8px 18px; }

    /* ─── MENU ─── */
    #menu { background:var(--dark); }
    .menu-header { text-align:center; max-width:600px; margin:0 auto 60px; }
    .menu-header .section-title { color:#fff; }
    .menu-header p { color:rgba(255,255,255,.5); line-height:var(--leading-relaxed); font-size:var(--text-base); }
    .menu-tabs { display:flex; gap:8px; justify-content:center; flex-wrap:wrap; margin-bottom:50px; }
    .tab-btn { background:transparent; border:1px solid rgba(200,134,10,.3); color:rgba(255,255,255,.6); padding:10px 22px; font-family:var(--font-body); font-size:var(--text-xs); letter-spacing:var(--tracking-wide); text-transform:uppercase; cursor:pointer; border-radius:2px; transition:background-color .2s ease, border-color .2s ease, color .2s ease; }
    .tab-btn.active,.tab-btn:hover { background:var(--gold); border-color:var(--gold); color:#fff; }
    .menu-panel { display:none; max-width:860px; margin:0 auto; width:100%; }
    .menu-panel.active { display:block; }
    .menu-item { display:flex; align-items:baseline; justify-content:space-between; padding:18px 0; border-bottom:1px solid rgba(255,255,255,.07); gap:20px; width:100%; box-sizing:border-box; }
    .menu-item:last-child { border-bottom:none; }
    .mi-left { flex:1; min-width:0; }
    .mi-name { font-family:var(--font-display); font-size:var(--text-lg); color:#fff; margin-bottom:4px; word-break:break-word; }
    .mi-desc { font-size:var(--text-sm); color:rgba(255,255,255,.4); line-height:var(--leading-normal); }
    .mi-badge { font-size:var(--text-2xs); letter-spacing:1px; text-transform:uppercase; background:rgba(200,134,10,.2); color:var(--gold); padding:2px 8px; border-radius:2px; margin-left:8px; vertical-align:middle; display:inline-block; }
    .mi-veg { display:inline-block; width:12px; height:12px; border:1.5px solid #2da44e; border-radius:2px; position:relative; margin-left:6px; vertical-align:middle; flex-shrink:0; }
    .mi-veg::after { content:''; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:6px; height:6px; background:#2da44e; border-radius:50%; }
    .mi-price { font-family:var(--font-display); font-size:var(--text-lg); font-weight:700; color:var(--gold-lt); white-space:nowrap; }
    .mi-dots { flex:1; border-bottom:1px dotted rgba(255,255,255,.12); margin:0 12px; min-width:20px; }
    .unavailable { opacity:.4; }

    /* ─── CELEBRATIONS ─── */
    #celebrations { background:var(--warm); }
    .cel-grid { display:grid; grid-template-columns:1fr 1fr; gap:60px; align-items:center; max-width:1100px; margin:0 auto; width:100%; }
    .cel-text p { color:var(--muted); line-height:var(--leading-relaxed); font-size:var(--text-base); margin-bottom:18px; }
    .cel-features { list-style:none; margin-top:28px; }
    .cel-features li { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid rgba(0,0,0,.06); font-size:var(--text-base); color:var(--text); }
    .cel-features li::before { content:'◆'; color:var(--gold); font-size:var(--text-2xs); flex-shrink:0; }
    .cel-mosaic { display:grid; grid-template-columns:1fr 1fr; grid-template-rows:200px 200px; gap:10px; width:100%; }
    .cel-mosaic .cm1 { grid-row:1/3; }
    .cm-item { border-radius:4px; overflow:hidden; transform:translateZ(0); }
    .cm-item img { width:100%; height:100%; object-fit:cover; display:block; }

    /* ─── STANDARDS ─── */
    #standards { background:var(--charcoal); }
    .standards-header { text-align:center; margin-bottom:60px; }
    .standards-header .section-title { color:#fff; }
    .standards-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; max-width:1100px; margin:0 auto; width:100%; }
    .standard-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:2px; padding:32px; transition:border-color .3s ease; }
    .standard-card:hover { border-color:rgba(200,134,10,.35); }
    .standard-icon { width:32px; height:32px; color:var(--gold); margin-bottom:18px; }
    .standard-title { color:#fff; font-family:var(--font-display); font-size:var(--text-lg); font-weight:700; margin-bottom:10px; }
    .standard-text { color:rgba(255,255,255,.65); line-height:var(--leading-relaxed); font-size:var(--text-sm); }

    /* ─── CONTACT ─── */
    #contact { background:var(--dark); }
    .contact-wrapper { max-width:1000px; margin:0 auto; display:grid; grid-template-columns:1fr 1.2fr; gap:80px; align-items:start; width:100%; }
    .contact-info .section-title { color:#fff; }
    .contact-info p { color:rgba(255,255,255,.5); line-height:var(--leading-relaxed); font-size:var(--text-base); margin-bottom:36px; }
    .info-item { display:flex; gap:16px; margin-bottom:24px; align-items:flex-start; }
    .info-icon { width:40px; height:40px; border-radius:4px; background:rgba(200,134,10,.15); display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
    .info-label { font-size:var(--text-xs); letter-spacing:var(--tracking-wide); text-transform:uppercase; color:var(--gold); }
    .info-value { color:rgba(255,255,255,.7); font-size:var(--text-base); margin-top:4px; line-height:var(--leading-normal); }
    .contact-form { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:4px; padding:40px; width:100%; box-sizing:border-box; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; width:100%; }
    .form-group { margin-bottom:20px; width:100%; }
    .form-group label { display:block; font-size:var(--text-xs); letter-spacing:var(--tracking-wider); text-transform:uppercase; color:rgba(255,255,255,.45); margin-bottom:8px; }
    .form-group input,.form-group textarea,.form-group select { width:100%; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); color:#fff; padding:12px 16px; font-family:var(--font-body); font-size:var(--text-base); border-radius:2px; outline:none; transition:border-color .2s ease; box-sizing:border-box; }
    .form-group input:focus,.form-group textarea:focus,.form-group select:focus { border-color:var(--gold); }
    .form-group textarea { resize:vertical; min-height:100px; }
    .form-group select option { background:var(--dark); }
    .form-submit { width:100%; background:var(--gold); border:none; color:#fff; padding:14px; font-family:var(--font-body); font-size:var(--text-sm); letter-spacing:var(--tracking-wider); text-transform:uppercase; cursor:pointer; border-radius:2px; transition:background-color .2s ease, opacity .2s ease; display:flex; align-items:center; justify-content:center; gap:10px; }
    .form-submit:hover:not(:disabled) { background:var(--gold-lt); }
    .form-submit:disabled { opacity:.6; cursor:not-allowed; }
    .spinner { width:16px; height:16px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; display:none; flex-shrink:0; }
    @keyframes spin { to{transform:rotate(360deg)} }

    /* ─── FOOTER ─── */
    footer { background:#0A0906; padding:50px 5% 30px; border-top:1px solid rgba(200,134,10,.15); width:100%; box-sizing:border-box; }
    .footer-inner { max-width:1100px; margin:0 auto; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:24px; padding-bottom:30px; border-bottom:1px solid rgba(255,255,255,.06); width:100%; }
    .footer-logo { font-family:var(--font-display); font-size:var(--text-2xl); font-weight:900; color:var(--gold-lt); }
    .footer-logo small { color:rgba(255,255,255,.3); font-size:var(--text-2xs); display:block; letter-spacing:var(--tracking-widest); }
    .footer-links { display:flex; gap:28px; flex-wrap:wrap; }
    .footer-links a { color:rgba(255,255,255,.5); font-size:var(--text-sm); text-decoration:none; transition:color .2s ease; }
    .footer-links a:hover { color:var(--gold); }
    .footer-copy { color:rgba(255,255,255,.25); font-size:var(--text-xs); text-align:center; margin-top:24px; max-width:1100px; margin-left:auto; margin-right:auto; }

    /* ─── MOBILE MENU ─── */
    .mobile-menu { display:none; position:fixed; inset:0; z-index:998; background:var(--charcoal); flex-direction:column; align-items:center; justify-content:center; gap:32px; width:100vw; height:100vh; overflow-y:auto; padding:40px 20px; box-sizing:border-box; }
    .mobile-menu.open { display:flex; }
    .mobile-menu a { font-family:var(--font-display); font-size:var(--text-2xl); color:#fff; text-decoration:none; text-align:center; }
    .mobile-menu a:hover { color:var(--gold-lt); }
    .close-btn { position:absolute; top:24px; right:5%; background:none; border:none; color:#fff; font-size:2rem; cursor:pointer; padding:8px; line-height:1; }

    /* ─── ANIMATIONS & OPTIMIZATION ─── */
    .fade-up { opacity:0; transform:translate3d(0, 24px, 0); transition:opacity .5s ease, transform .5s ease; will-change:opacity, transform; }
    .fade-up.visible { opacity:1; transform:translate3d(0, 0, 0); }
    .ph { background:repeating-linear-gradient(45deg,rgba(200,134,10,.07) 0px,rgba(200,134,10,.07) 1px,transparent 1px,transparent 12px),var(--charcoal); }

    /* ─── RESPONSIVE & HORIZONTAL OVERFLOW PREVENTION ─── */
    @media(max-width:900px) {
      nav { padding:14px 5%; }
      .nav-links,.nav-cta,.nav-link-ghost { display:none; }
      .hamburger { display:flex; }
      .about-grid,.cel-grid,.contact-wrapper { grid-template-columns:1fr; gap:40px; }
      .gallery-grid { grid-template-columns:1fr 1fr; grid-template-rows:auto; }
      .gallery-grid .g1,.gallery-grid .g2,.gallery-grid .g3,.gallery-grid .g4,.gallery-grid .g5 { grid-column:auto; grid-row:auto; height:200px; }
      .reviews-grid { grid-template-columns:1fr; }
      .form-row { grid-template-columns:1fr; }
      .about-visual { grid-template-rows:160px 160px; }
      .cel-mosaic { grid-template-rows:160px 160px; }
      .contact-form { padding:24px 18px; }
    }
    @media(max-width:540px) {
      .gallery-grid { grid-template-columns:1fr; }
      .gallery-grid .g1,.gallery-grid .g2,.gallery-grid .g3,.gallery-grid .g4,.gallery-grid .g5 { height:220px; }
      .about-stats { grid-template-columns:1fr; gap:14px; }
      .about-visual { grid-template-columns:1fr; grid-template-rows:auto; }
      .av-1 { grid-column:auto; }
      .photo-card { height:180px !important; }
      .cel-mosaic { grid-template-columns:1fr; grid-template-rows:auto; }
      .cm-item { height:180px !important; }
      .menu-item { flex-direction:column; align-items:flex-start; gap:6px; }
      .mi-dots { display:none; }
      .mi-price { align-self:flex-end; }
    }

    /* ─── PREFERS REDUCED MOTION ─── */
    @media(prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        animation-duration: 0.001ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.001ms !important;
        scroll-behavior: auto !important;
      }
      .fade-up { opacity:1 !important; transform:none !important; }
      .scroll-dot::after { animation:none !important; }
    }
  </style>
</head>
<body>

<!-- TOAST NOTIFICATION -->
<div id="toast"></div>

<!-- LIVE BOOKING TRACKER (populated via Server-Sent Events after a booking is placed) -->
<div id="liveTracker" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;
     background:#1E1C18;border:1px solid #C8860A55;border-radius:10px;padding:18px 20px;
     min-width:260px;box-shadow:0 8px 30px rgba(0,0,0,.4);color:#fff;font-family:inherit;">
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
    <span id="liveDot" style="width:9px;height:9px;border-radius:50%;background:#4caf70;display:inline-block;animation:pulseDot 1.5s infinite;"></span>
    <strong style="font-size:.85rem;letter-spacing:1px;color:#C8860A;">LIVE BOOKING STATUS</strong>
    <button onclick="closeLiveTracker()" style="margin-left:auto;background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;font-size:1rem;">✕</button>
  </div>
  <div id="liveStatusText" style="font-size:1.05rem;font-weight:600;">Waiting for confirmation…</div>
  <div id="livePaymentText" style="font-size:.8rem;color:rgba(255,255,255,.5);margin-top:4px;"></div>
</div>
<style>
  @keyframes pulseDot { 0%,100%{opacity:1;} 50%{opacity:.3;} }
</style>

<!-- NAV -->
<nav id="navbar">
  <a class="nav-logo" href="<?= url('/') ?>">Terminal 1 <span>The Restaurant</span></a>
  <ul class="nav-links">
    <li><a href="#about">About</a></li>
    <li><a href="#gallery">Gallery</a></li>
    <li><a href="#menu">Menu</a></li>
    <li><a href="#celebrations">Celebrations</a></li>
    <li><a href="#standards">Standards</a></li>
  </ul>
  <div class="nav-right">
    <?php if($user): ?>
      <a class="nav-link-ghost" href="<?= url('/my-bookings') ?>">My Bookings</a>
      <?php if($user['role'] === 'admin'): ?>
        <a class="nav-link-ghost" href="<?= url('/admin') ?>">Admin Dashboard</a>
      <?php endif; ?>
      <span class="nav-user">Hi, <?= e(explode(' ', $user['name'])[0]) ?></span>
      <a class="nav-cta" href="#contact">Reserve a Table</a>
      <form method="POST" action="<?= url('/auth/logout') ?>" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <button type="submit" class="nav-link-ghost" style="background:none;border:none;cursor:pointer;font:inherit;">Logout</button>
      </form>
    <?php else: ?>
      <a class="nav-link-ghost" href="<?= url('/auth/login') ?>">Login</a>
      <a class="nav-cta" href="<?= url('/auth/login?redirect=' . urlencode('/#contact')) ?>">Reserve a Table</a>
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
  <a href="#standards" onclick="closeMenu()">Standards</a>
  <?php if($user): ?>
    <a href="#contact" onclick="closeMenu()">Reserve a Table</a>
    <a href="<?= url('/my-bookings') ?>" onclick="closeMenu()">My Bookings</a>
    <?php if($user['role']==='admin'): ?><a href="<?= url('/admin') ?>" onclick="closeMenu()">Admin Dashboard</a><?php endif; ?>
    <form method="POST" action="<?= url('/auth/logout') ?>">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <button type="submit" style="background:none;border:none;cursor:pointer;font:inherit;color:inherit;padding:0;">Logout</button>
    </form>
  <?php else: ?>
    <a href="<?= url('/auth/login?redirect=' . urlencode('/#contact')) ?>" onclick="closeMenu()">Reserve a Table</a>
    <a href="<?= url('/auth/login') ?>" onclick="closeMenu()">Login</a>
    <a href="<?= url('/auth/register') ?>" onclick="closeMenu()">Register</a>
  <?php endif; ?>
</div>

<!-- HERO -->
<section class="hero" id="hero">
  <div class="hero-bg"></div>
  <div class="hero-grain"></div>
  <div class="hero-line"></div>
  <div class="hero-content fade-up visible">
    <div class="hero-eyebrow">Cooch Behar, West Bengal</div>
    <h1>Terminal<em>The Restaurant</em>1</h1>
    <p class="hero-sub">North Bengal culinary tradition, slow-cooked tandoori roasts, and authentic regional gravies. Prepared fresh daily in Cooch Behar for family dining and private celebrations.</p>
    <div class="hero-btns">
      <a class="btn-primary" href="#menu">Explore Menu</a>
      <?php if($user): ?>
        <a class="btn-ghost" href="#contact">Book Your Table</a>
      <?php else: ?>
        <a class="btn-ghost" href="<?= url('/auth/login?redirect=' . urlencode('/#contact')) ?>">Book Your Table</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="hero-scroll"><div class="scroll-dot"></div>Scroll</div>
</section>

<!-- ABOUT -->
<section id="about">
  <div class="about-grid">
    <div class="about-text fade-up">
      <div class="section-tag">Our Culinary Craft</div>
      <h2 class="section-title">Rooted in Tradition,<br>Crafted for Dining</h2>
      <div class="divider"></div>
      <p>Terminal 1: The Restaurant was founded on a straightforward principle: authentic North Bengal recipes, uncompromising ingredient freshness, and honest table hospitality. Located in Cooch Behar, our kitchen specializes in slow-reduced Bengali gravies, fragrantly spiced rice specialties, and wood-charcoal tandoori roasts.</p>
      <p>Every preparation is freshly seasoned to order with stone-ground spice blends. From intimate evening gatherings to celebratory family feasts, our dining room provides a comfortable, welcoming environment where genuine cooking takes center stage.</p>
      <div class="about-pillars">
        <div class="pillar-card">
          <div class="pillar-title">Market-Fresh Sourcing</div>
          <div class="pillar-desc">Daily morning selection of poultry, regional fish, and seasonal vegetables from Cooch Behar markets.</div>
        </div>
        <div class="pillar-card">
          <div class="pillar-title">Natural Charcoal Tandoor</div>
          <div class="pillar-desc">Traditional clay oven roasting over lump charcoal for genuine smoky char and tender cuts.</div>
        </div>
        <div class="pillar-card">
          <div class="pillar-title">In-House Spice Blends</div>
          <div class="pillar-desc">Whole spices roasted and stone-ground in-house; zero commercial pastes or artificial colorants.</div>
        </div>
        <div class="pillar-card">
          <div class="pillar-title">Private Celebration Dining</div>
          <div class="pillar-desc">Dedicated floor seating arrangements, floral table decor, and customized party feast platters.</div>
        </div>
      </div>
    </div>
    <div class="about-visual fade-up">
      <div class="photo-card av-1" style="height:220px"><img src="<?= asset('images/starters_platter.jpg') ?>" alt="Terminal 1 Starters Platter" onerror="this.parentElement.classList.add('ph')"/></div>
      <div class="photo-card" style="height:220px"><img src="<?= asset('images/dhonkami_chicken.jpg') ?>" alt="Chef's Special Dhonkami Chicken" onerror="this.parentElement.classList.add('ph')"/></div>
      <div class="photo-card" style="height:220px"><img src="<?= asset('images/polao.jpg') ?>" alt="Kashmiri Polao and Biryani" onerror="this.parentElement.classList.add('ph')"/></div>
    </div>
  </div>
</section>

<!-- GALLERY -->
<section id="gallery">
  <div class="gallery-header fade-up">
    <div class="section-tag" style="justify-content:center">Our Kitchen</div>
    <h2 class="section-title">Signatures &amp; Preparations</h2>
    <div class="divider" style="margin:20px auto"></div>
  </div>
  <div class="gallery-grid fade-up">
    <div class="g-item g1"><img src="<?= asset('images/dhonkami_chicken.jpg') ?>" alt="Chef's Signature Dhonkami Chicken" onerror="this.style.background='#2a2820'"/><div class="g-overlay"><span>Chef's Signature Chicken</span></div></div>
    <div class="g-item g2"><img src="<?= asset('images/polao.jpg') ?>" alt="Kashmiri Polao and Biryani" onerror="this.style.background='#2a2820'"/><div class="g-overlay"><span>Kashmiri Polao and Biryani</span></div></div>
    <div class="g-item g3"><img src="<?= asset('images/fried_rice.jpg') ?>" alt="Egg Chicken Fried Rice" onerror="this.style.background='#2a2820'"/><div class="g-overlay"><span>Egg Chicken Fried Rice</span></div></div>
    <div class="g-item g4"><img src="<?= asset('images/noodles.jpg') ?>" alt="Hakka Noodles and Breads" onerror="this.style.background='#2a2820'"/><div class="g-overlay"><span>Hakka Noodles and Breads</span></div></div>
    <div class="g-item g5"><img src="<?= asset('images/starters_platter.jpg') ?>" alt="Tandoori Starters Platter" onerror="this.style.background='#2a2820'"/><div class="g-overlay"><span>Tandoori Starters Platter</span></div></div>
  </div>
</section>

<!-- MENU — loaded live from DB -->
<section id="menu">
  <div class="menu-header fade-up">
    <div class="section-tag" style="justify-content:center">Our Offerings</div>
    <h2 class="section-title">Terminal 1 Menu</h2>
    <p>From slow-roasted Dhonkami Chicken to fragrant Kashmiri Polao, every dish is seasoned to order.</p>
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
    <button class="tab-btn" onclick="switchTab('rice',this)">Rice &amp; Polao</button>
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
      <h2 class="section-title">Celebrate Important<br>Milestones With Us</h2>
      <div class="divider"></div>
      <p>Whether planning a birthday, family anniversary, or festive get-together, Terminal 1 coordinates dedicated table arrangements and banquet dining options.</p>
      <ul class="cel-features">
        <li>Traditional floral and celebration table styling</li>
        <li>Custom anniversary and birthday cake arrangements</li>
        <li>Authentic regional celebration spreads</li>
        <li>Dedicated family and group seating sections</li>
        <li>Curated party platters and customized banquet menus</li>
        <li>Quiet, climate-controlled dining ambience</li>
      </ul>
    </div>
    <div class="cel-mosaic fade-up">
      <div class="cm-item cm1"><img src="<?= asset('images/dhonkami_chicken.jpg') ?>" alt="Celebration Feast" style="height:100%;width:100%;object-fit:cover;" onerror="this.style.background='#d4c9b0'"/></div>
      <div class="cm-item"><img src="<?= asset('images/starters_platter.jpg') ?>" alt="Tandoori Starters Platter" style="height:200px;width:100%;object-fit:cover;" onerror="this.style.background='#c8b898'"/></div>
      <div class="cm-item"><img src="<?= asset('images/polao.jpg') ?>" alt="Kashmiri Polao Celebration Spread" style="height:200px;width:100%;object-fit:cover;" onerror="this.style.background='#bfac8c'"/></div>
    </div>
  </div>
</section>

<!-- STANDARDS -->
<section id="standards">
  <div class="standards-header fade-up">
    <div class="section-tag" style="justify-content:center">Our Principles</div>
    <h2 class="section-title">Dining &amp; Kitchen Standards</h2>
    <div class="divider" style="margin:20px auto"></div>
  </div>
  <div class="standards-grid fade-up">
    <div class="standard-card">
      <div class="standard-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="standard-title">Honest Pricing &amp; Value</div>
      <p class="standard-text">All portions and charges are clearly listed with no hidden service markups or undisclosed fees. Transparent hospitality is our priority.</p>
    </div>
    <div class="standard-card">
      <div class="standard-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div class="standard-title">Hygiene &amp; Prep Integrity</div>
      <p class="standard-text">Strict sanitation across cooking stations, dedicated prep areas for vegetarian orders, and continuous compliance with food safety protocols.</p>
    </div>
    <div class="standard-card">
      <div class="standard-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div class="standard-title">Reserved Seating Assurance</div>
      <p class="standard-text">Confirmed table reservations enjoy an assured 15-minute arrival grace period so your dining party is seated promptly without crowd bottlenecks.</p>
    </div>
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
        <p>Welcome back, <strong style="color:rgba(255,255,255,.8)"><?= e($user['name']) ?></strong>. Fill in your dining details below to reserve your table.</p>
      <?php else: ?>
        <p>Advance table reservations require an authenticated account. Sign in to your account below to book your table.</p>
      <?php endif; ?>
      <div class="info-item">
        <div class="info-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div>
          <div class="info-label">Address</div>
          <div class="info-value">Terminal 1: The Restaurant<br>Cooch Behar, West Bengal, India</div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
          <div class="info-label">Hours</div>
          <div class="info-value">Monday to Sunday<br>11:00 AM – 10:00 PM</div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        </div>
        <div>
          <div class="info-label">Contact</div>
          <div class="info-value">Inquiries &amp; Celebration Packages<br><a href="mailto:contact@terminal1.in" style="color:#C8860A;text-decoration:none;">contact@terminal1.in</a></div>
        </div>
      </div>
    </div>

    <?php if($user): ?>
    <div class="contact-form fade-up">
      <!-- Hidden CSRF token injected from PHP session -->
      <input type="hidden" id="csrf_token" value="<?= csrfToken() ?>">

      <div class="form-row">
        <div class="form-group">
          <label>Your Name</label>
          <input type="text" id="fname" placeholder="Rajib Das"
                 value="<?= e($user['name']) ?>" required/>
        </div>
        <div class="form-group">
          <label>Phone Number *</label>
          <input type="tel" id="fphone" placeholder="+91 98765 00000" required/>
        </div>
      </div>
      <div class="form-group">
        <label>Account Email</label>
        <input type="email" id="femail" value="<?= e($user['email']) ?>" readonly
               style="background:rgba(255,255,255,.02);color:rgba(255,255,255,.5);cursor:not-allowed;"/>
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
          <input type="number" id="fguests" placeholder="e.g. 4" min="1" value="2"/>
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
    <?php else: ?>
    <div class="contact-form fade-up" style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:52px 30px;">
      <div style="width:62px;height:62px;border-radius:2px;background:rgba(200,134,10,.12);border:1px solid rgba(200,134,10,.3);display:flex;align-items:center;justify-content:center;margin-bottom:22px;color:#C8860A;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <h3 style="font-family:var(--font-display);font-size:1.5rem;color:#fff;margin-bottom:12px;">Sign In to Reserve a Table</h3>
      <p style="color:rgba(255,255,255,.65);font-size:0.92rem;line-height:1.6;max-width:390px;margin-bottom:28px;">
        To guarantee your table reservation, link your dining deposit, and receive live preparation updates, please sign in or register before booking.
      </p>
      <div style="display:flex;gap:12px;width:100%;max-width:340px;flex-direction:column;">
        <a href="<?= url('/auth/login?redirect=' . urlencode('/#contact')) ?>" class="btn-primary" style="text-align:center;padding:14px;font-size:0.85rem;letter-spacing:1.5px;text-transform:uppercase;text-decoration:none;display:block;">
          Sign In to Reserve Table
        </a>
        <a href="<?= url('/auth/register?redirect=' . urlencode('/#contact')) ?>" class="btn-ghost" style="text-align:center;padding:13px;font-size:0.82rem;letter-spacing:1px;text-transform:uppercase;text-decoration:none;display:block;border-color:rgba(255,255,255,.2);color:rgba(255,255,255,.8);">
          Create New Account
        </a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-logo">Terminal 1<small>The Restaurant</small></div>
    <div class="footer-links">
      <a href="#about">About</a><a href="#gallery">Gallery</a><a href="#menu">Menu</a>
      <a href="#celebrations">Celebrations</a><a href="#standards">Standards</a>
      <?php if($user): ?>
        <a href="#contact">Reserve</a>
      <?php else: ?>
        <a href="<?= url('/auth/login?redirect=' . urlencode('/#contact')) ?>">Reserve</a>
      <?php endif; ?>
      <a href="<?= url('/privacy') ?>">Privacy Policy</a>
      <a href="<?= url('/terms') ?>">Terms &amp; Conditions</a>
      <?php if($user && $user['role']==='admin'): ?><a href="<?= url('/admin') ?>">Admin Dashboard</a><?php endif; ?>
    </div>
  </div>
  <p class="footer-copy">&copy; <?= date('Y') ?> Terminal 1: The Restaurant, Cooch Behar. All rights reserved.</p>
</footer>

<script>
// ── 1. Performance-optimized Fade-up on scroll (unobserves once visible to prevent re-renders) ──
const obs = new IntersectionObserver((entries, observer) => {
  entries.forEach(e => {
    if(e.isIntersecting) {
      e.target.classList.add('visible');
      observer.unobserve(e.target);
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
document.querySelectorAll('.fade-up').forEach(el => obs.observe(el));

// ── 2. Direct-reference Menu tabs (Zero DOM-query thrashing) ──
let currentTabBtn = document.querySelector('.tab-btn.active');
let currentPanel  = document.querySelector('.menu-panel.active');
function switchTab(id, btn) {
  if (btn === currentTabBtn) return;
  if (currentTabBtn) currentTabBtn.classList.remove('active');
  if (currentPanel)  currentPanel.classList.remove('active');
  const target = document.getElementById('tab-' + id);
  btn.classList.add('active');
  if (target) target.classList.add('active');
  currentTabBtn = btn;
  currentPanel  = target;
}

// ── 3. Mobile nav ──
function openMenu()  { document.getElementById('mobileMenu').classList.add('open'); }
function closeMenu() { document.getElementById('mobileMenu').classList.remove('open'); }

// ── 4. Navbar scroll state (Passive listener with state toggle — zero layout thrashing) ──
let isNavScrolled = false;
const navBar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  const shouldScroll = window.scrollY > 40;
  if (shouldScroll !== isNavScrolled) {
    isNavScrolled = shouldScroll;
    navBar.classList.toggle('nav-scrolled', isNavScrolled);
  }
}, { passive: true });

// ── 5. Toast notification (Mobile overflow safe) ──
let toastTimer = null;
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'show ' + type;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { t.className = ''; }, 4500);
}

// ── 6. Intent-based Speculative Prefetching ──
const prefetchedHrefs = new Set();
function prefetchUrl(url) {
  if (!url || prefetchedHrefs.has(url)) return;
  try {
    const u = new URL(url, window.location.href);
    if (u.origin !== window.location.origin) return;
    if (u.pathname === window.location.pathname && u.search === window.location.search) return;
    if (u.pathname.includes('/auth/logout')) return;
    prefetchedHrefs.add(url);
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = url;
    document.head.appendChild(link);
  } catch(e) {}
}

let hoverTimer = null;
document.addEventListener('mouseover', (e) => {
  const a = e.target.closest('a');
  if (!a || !a.href) return;
  clearTimeout(hoverTimer);
  hoverTimer = setTimeout(() => prefetchUrl(a.href), 60);
}, { passive: true });

document.addEventListener('touchstart', (e) => {
  const a = e.target.closest('a');
  if (a && a.href) prefetchUrl(a.href);
}, { passive: true });

// ── 7. Real-time booking tracker (Server-Sent Events) ──
let liveTrackerSource = null;
let lastKnownStatus   = null;
let lastKnownPayment  = null;

function startLiveTracking(bookingId, trackingToken) {
  const widget = document.getElementById('liveTracker');
  widget.style.display = 'block';
  document.getElementById('liveStatusText').textContent = 'Waiting for confirmation…';
  document.getElementById('livePaymentText').textContent = '';

  if (liveTrackerSource) liveTrackerSource.close();

  const url = '<?= url('/track/stream') ?>' + `?id=${bookingId}&token=${encodeURIComponent(trackingToken)}`;
  liveTrackerSource = new EventSource(url);

  const statusLabels = {
    pending:   { text: 'Pending Confirmation',    color: '#d29922' },
    confirmed: { text: 'Table Confirmed',         color: '#4caf70' },
    cancelled: { text: 'Booking Cancelled',        color: '#e5534b' },
    completed: { text: 'Visit Completed',          color: '#4caf70' },
  };
  const paymentLabels = {
    unpaid:    'Deposit: Pending Payment',
    paid:      'Deposit: Confirmed Paid',
    refunded:  'Deposit: Refunded',
  };

  liveTrackerSource.addEventListener('status_update', (e) => {
    const data = JSON.parse(e.data);
    // Eliminate unnecessary DOM writes if payload did not change
    if (data.status === lastKnownStatus && data.payment_status === lastKnownPayment) return;
    lastKnownStatus  = data.status;
    lastKnownPayment = data.payment_status;

    const label = statusLabels[data.status] || { text: data.status, color: '#fff' };
    const statusEl = document.getElementById('liveStatusText');
    statusEl.textContent = label.text;
    statusEl.style.color = label.color;
    document.getElementById('livePaymentText').textContent = paymentLabels[data.payment_status] || '';
  });
}

function closeLiveTracker() {
  if (liveTrackerSource) { liveTrackerSource.close(); liveTrackerSource = null; }
  document.getElementById('liveTracker').style.display = 'none';
}

// ── 8. Booking form with client-side rate limits & double-click protection ──
let lastBookingSubmit = 0;
async function submitBooking() {
  const now = Date.now();
  if (now - lastBookingSubmit < 3000) {
    showToast('Please wait a moment before resending your request.', 'error');
    return;
  }

  const name   = document.getElementById('fname').value.trim();
  const phone  = document.getElementById('fphone').value.trim();
  const csrf   = document.getElementById('csrf_token').value;

  if(!name || !phone) {
    showToast('Please enter your name and phone number.', 'error');
    return;
  }

  lastBookingSubmit = now;
  const btn     = document.getElementById('submitBtn');
  const txt     = document.getElementById('btnText');
  const spinner = document.getElementById('btnSpinner');

  btn.disabled       = true;
  txt.textContent    = 'Sending...';
  spinner.style.display = 'block';

  try {
    const res = await fetch('<?= url('/bookings') ?>', {
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

    if (data.require_login && data.redirect) {
      showToast(data.message || 'Please log in to reserve a table.', 'error');
      setTimeout(() => { window.location.href = data.redirect; }, 1000);
      return;
    }

    if(data.success) {
      showToast(data.message, 'success');
      document.getElementById('fphone').value = '';
      document.getElementById('foccasion').selectedIndex = 0;
      document.getElementById('fguests').value = '';
      document.getElementById('fdate').value = '';
      document.getElementById('fmsg').value = '';

      if (data.requires_payment && data.deposit_amount > 0) {
        await startPayment(data.id, csrf, data.tracking_token);
      }

      if (data.tracking_token) {
        startLiveTracking(data.id, data.tracking_token);
      }
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

// ── Razorpay: create order, open checkout, verify on success (Ownership Verified) ──
async function startPayment(bookingId, csrf, trackingToken) {
  try {
    const orderRes = await fetch('<?= url('/payments/create-order') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf },
      body: new URLSearchParams({
        csrf_token:     csrf,
        booking_id:     bookingId,
        tracking_token: trackingToken || ''
      })
    });
    const order = await orderRes.json();
    if (!order.success) { showToast(order.message || 'Could not start payment.', 'error'); return; }

    const options = {
      key: order.key,
      amount: order.amount,
      currency: order.currency,
      name: order.name,
      description: 'Table Reservation Deposit',
      order_id: order.order_id,
      handler: async function (response) {
        const verifyRes = await fetch('<?= url('/payments/verify') ?>', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrf },
          body: new URLSearchParams({
            csrf_token:          csrf,
            booking_id:          bookingId,
            tracking_token:      trackingToken || '',
            razorpay_order_id:   response.razorpay_order_id,
            razorpay_payment_id: response.razorpay_payment_id,
            razorpay_signature:  response.razorpay_signature,
          })
        });
        const verify = await verifyRes.json();
        showToast(verify.message, verify.success ? 'success' : 'error');
      },
      modal: { ondismiss: function () { showToast('Payment cancelled. Your booking is saved but unconfirmed.', 'error'); } },
      theme: { color: '#C8860A' }
    };
    const rzp = new Razorpay(options);
    rzp.open();
  } catch (err) {
    showToast('Could not initiate payment. Please try again.', 'error');
  }
}
</script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</body>
</html>
