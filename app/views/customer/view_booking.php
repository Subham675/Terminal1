<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservation #<?= e($booking['id']) ?> — Terminal 1</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --gold: #C8860A;
      --gold-lt: #E8A820;
      --dark: #0F0E0B;
      --charcoal: #171510;
      --card-bg: rgba(255,255,255,0.03);
      --border: rgba(255,255,255,0.08);
      --text: #F5EFE6;
      --muted: rgba(245,239,230,0.55);
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--dark);
      color: var(--text);
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    nav {
      background: #0A0906;
      border-bottom: 1px solid rgba(200,134,10,0.18);
      padding: 16px 5%;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .logo {
      font-family: 'Cinzel', serif;
      font-size: 1.3rem;
      font-weight: 900;
      color: var(--gold-lt);
      text-decoration: none;
      letter-spacing: 2px;
    }
    .nav-links { display: flex; gap: 20px; align-items: center; }
    .nav-links a { color: var(--muted); text-decoration: none; font-size: 0.9rem; transition: color .2s; }
    .nav-links a:hover { color: var(--gold); }
    .container {
      max-width: 680px;
      margin: 40px auto;
      padding: 0 20px;
      width: 100%;
      flex: 1;
    }
    .detail-card {
      background: var(--charcoal);
      border: 1px solid rgba(200,134,10,0.25);
      border-radius: 8px;
      padding: 36px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.5);
    }
    .header-tag {
      font-size: 0.72rem;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    h1 {
      font-family: 'Cinzel', serif;
      font-size: 1.8rem;
      color: #fff;
      margin-bottom: 24px;
    }
    .grid-info {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 28px;
      padding-bottom: 24px;
      border-bottom: 1px solid var(--border);
    }
    .info-label {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--muted);
      margin-bottom: 4px;
    }
    .info-val {
      font-size: 1rem;
      color: #fff;
      font-weight: 600;
    }
    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 0.75rem;
      letter-spacing: 1px;
      text-transform: uppercase;
      font-weight: 600;
    }
    .badge-pending { background: rgba(210,153,34,0.15); color: #d29922; border: 1px solid rgba(210,153,34,0.3); }
    .badge-confirmed { background: rgba(45,164,78,0.15); color: #4caf70; border: 1px solid rgba(45,164,78,0.3); }
    .badge-cancelled { background: rgba(207,34,46,0.15); color: #cf222e; border: 1px solid rgba(207,34,46,0.3); }
    .badge-completed { background: rgba(45,164,78,0.15); color: #4caf70; border: 1px solid rgba(45,164,78,0.3); }
    .badge-paid { background: rgba(45,164,78,0.15); color: #4caf70; }
    .badge-unpaid { background: rgba(210,153,34,0.15); color: #d29922; }
    .badge-refunded { background: rgba(207,34,46,0.15); color: #cf222e; }
    .stream-status {
      background: rgba(200,134,10,0.06);
      border: 1px solid rgba(200,134,10,0.2);
      border-radius: 6px;
      padding: 16px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 28px;
    }
    .status-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: #4caf70;
      box-shadow: 0 0 10px #4caf70;
      animation: pulse 2s infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.4;} }
    .actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 11px 22px;
      border-radius: 4px;
      font-size: 0.82rem;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      font-weight: 600;
      text-decoration: none;
      transition: all .2s;
    }
    .btn-gold { background: var(--gold); color: #fff; }
    .btn-gold:hover { background: var(--gold-lt); }
    .btn-ghost { background: transparent; border: 1px solid var(--border); color: var(--muted); }
    .btn-ghost:hover { border-color: #fff; color: #fff; }
    .security-note {
      font-size: 0.72rem;
      color: rgba(255,255,255,0.3);
      margin-top: 24px;
      text-align: center;
    }
    footer {
      background: #0A0906;
      border-top: 1px solid var(--border);
      padding: 24px 5%;
      text-align: center;
      color: rgba(255,255,255,0.25);
      font-size: 0.8rem;
    }
  </style>
</head>
<body>
  <nav>
    <a href="<?= url('/') ?>" class="logo">Terminal 1</a>
    <div class="nav-links">
      <a href="<?= url('/') ?>">Home</a>
      <a href="<?= url('/my-bookings') ?>">My Bookings</a>
      <a href="<?= url('/#menu') ?>">Menu</a>
    </div>
  </nav>

  <div class="container">
    <div class="detail-card">
      <div class="header-tag">
        <span>🔒 Verified Reservation</span>
      </div>
      <h1>Reservation Details</h1>

      <div class="stream-status">
        <div class="status-dot"></div>
        <div>
          <div style="font-size:0.85rem;font-weight:600;color:#fff;">
            Live Status: <span class="badge badge-<?= e($booking['status']) ?>"><?= ucfirst(e($booking['status'])) ?></span>
          </div>
          <div style="font-size:0.75rem;color:var(--muted);margin-top:2px;">
            Payment: <span class="badge badge-<?= e($booking['payment_status'] ?? 'unpaid') ?>"><?= ucfirst(e($booking['payment_status'] ?? 'unpaid')) ?></span>
            <?php if(($booking['deposit_amount']??0) > 0): ?> • Amount: ₹<?= number_format($booking['deposit_amount'],0) ?><?php endif; ?>
          </div>
        </div>
      </div>

      <div class="grid-info">
        <div>
          <div class="info-label">Guest Name</div>
          <div class="info-val"><?= e($booking['name']) ?></div>
        </div>
        <div>
          <div class="info-label">Contact Phone</div>
          <div class="info-val"><?= e($booking['phone']) ?></div>
        </div>
        <div>
          <div class="info-label">Reserved Date</div>
          <div class="info-val">
            <?= $booking['booking_date'] ? date('D, d M Y', strtotime($booking['booking_date'])) : date('D, d M Y', strtotime($booking['created_at'])) ?>
          </div>
        </div>
        <div>
          <div class="info-label">Reserved Time</div>
          <div class="info-val">
            <?= $booking['booking_time'] ? date('h:i A', strtotime($booking['booking_time'])) : 'Regular slot' ?>
          </div>
        </div>
        <div>
          <div class="info-label">Party Size</div>
          <div class="info-val"><?= (int)$booking['guests'] ?> Guests</div>
        </div>
        <div>
          <div class="info-label">Occasion</div>
          <div class="info-val"><?= e($booking['occasion'] ?: 'Standard Dining') ?></div>
        </div>
      </div>

      <?php if(!empty($booking['message'])): ?>
        <div style="margin-bottom:24px;">
          <div class="info-label">Special Requests</div>
          <div style="font-size:0.9rem;color:rgba(255,255,255,0.7);background:rgba(255,255,255,0.02);border:1px solid var(--border);border-radius:4px;padding:12px 16px;">
            <?= e($booking['message']) ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="actions">
        <a href="<?= url('/') ?>" class="btn btn-gold">← Return to Home</a>
        <?php if(isLoggedIn()): ?>
          <a href="<?= url('/my-bookings') ?>" class="btn btn-ghost">View All My Bookings</a>
        <?php endif; ?>
      </div>

      <div class="security-note">
        🛡️ Ownership verified by Terminal 1 Server Engine. Unauthorized URL tampering is actively monitored and blocked.
      </div>
    </div>
  </div>

  <footer>
    © <?= date('Y') ?> Terminal 1 — The Restaurant. All rights reserved.
  </footer>
</body>
</html>
