<!-- Navigation Header -->
<nav>
  <a href="<?= url('/') ?>" class="brand-logo">TERMINAL 1</a>

  <ul class="nav-center-links">
    <li><a href="#about">About</a></li>
    <li><a href="#experiences">Experiences</a></li>
    <li><a href="#menu">Menu</a></li>
    <li><a href="#philosophy">Philosophy</a></li>
    <li><a href="#contact">Reservations</a></li>
    <li><a href="#reviews">Reviews</a></li>
  </ul>

  <div class="nav-actions">
    <?php if($user): ?>
      <a href="<?= url('/my-bookings') ?>" class="btn-nav-auth"><?= e(explode(' ', $user['name'])[0]) ?></a>
      <?php if(($user['role'] ?? '') === 'admin'): ?>
        <a href="<?= url('/admin/dashboard') ?>" class="btn-nav-auth" style="color:var(--gold-accent);">[Admin]</a>
      <?php endif; ?>
      <a href="<?= url('/auth/logout') ?>" class="btn-nav-auth" style="opacity:0.6;">Logout</a>
    <?php else: ?>
      <a href="<?= url('/auth/login') ?>" class="btn-nav-auth">Sign In</a>
    <?php endif; ?>
    <a href="#contact" class="btn-aveline-cta">BOOK A TABLE</a>
    <button type="button" class="btn-mobile-nav-toggle" id="mobileNavToggle" onclick="toggleMobileNav()" aria-label="Toggle navigation">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</nav>

<!-- MOBILE BACKDROP OVERLAY -->
<div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="closeMobileNav()"></div>

<!-- MOBILE SLIDE-IN DRAWER -->
<div class="mobile-nav-drawer" id="mobileNavDrawer">
  <div class="mobile-drawer-header">
    <span class="mobile-drawer-brand">Terminal 1</span>
    <button type="button" class="mobile-drawer-close" onclick="closeMobileNav()" aria-label="Close navigation">✕</button>
  </div>
  <div class="mobile-nav-links">
    <a href="#about" onclick="closeMobileNav()">About Terminal 1</a>
    <a href="#experiences" onclick="closeMobileNav()">Experiences</a>
    <a href="#menu" onclick="closeMobileNav()">Artisanal Menu</a>
    <a href="#philosophy" onclick="closeMobileNav()">Culinary Philosophy</a>
    <a href="#contact" onclick="closeMobileNav()">Book a Table</a>
    <a href="#reviews" onclick="closeMobileNav()">Praise & Critiques</a>
    <hr class="mobile-nav-divider">
    <?php if($user): ?>
      <a href="<?= url('/my-bookings') ?>" onclick="closeMobileNav()">My Reservations (<?= e($user['name']) ?>)</a>
      <?php if(($user['role'] ?? '') === 'admin'): ?>
        <a href="<?= url('/admin/dashboard') ?>" onclick="closeMobileNav()" style="color:var(--gold-accent);">Admin Dashboard</a>
      <?php endif; ?>
      <a href="<?= url('/auth/logout') ?>" data-signout>Sign Out</a>
    <?php else: ?>
      <a href="<?= url('/auth/login') ?>" onclick="closeMobileNav()">Sign In</a>
      <a href="<?= url('/auth/register') ?>" onclick="closeMobileNav()">Create an Account</a>
    <?php endif; ?>
  </div>
  <div class="mobile-drawer-footer">
    <div class="mobile-drawer-footer-text">A Fine Dining Experience</div>
  </div>
</div>
