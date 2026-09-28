<?php
$user = authUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings | Terminal 1</title>
  <meta name="description" content="Track your real-time table allocation, concierge dining status, and review reservation policies at Terminal 1.">
  <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
  <link rel="alternate icon" href="<?= asset('favicon.ico') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <style>
    :root {
      --gold: #C8860A;
      --gold-lt: #E8A820;
      --dark: #0F0E0B;
      --charcoal: #171510;
      --card-bg: rgba(255,255,255,0.03);
      --border: rgba(255,255,255,0.08);
      --text: #F5EFE6;
      --muted: rgba(245,239,230,0.72);
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
      font-family: 'Playfair Display', serif;
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--gold-lt);
      text-decoration: none;
      letter-spacing: 1.5px;
    }
    .nav-links { display: flex; gap: 20px; align-items: center; }
    .nav-links a { color: var(--muted); text-decoration: none; font-size: 0.9rem; transition: color .2s; }
    .nav-links a:hover { color: var(--gold); }
    .container {
      max-width: 960px;
      margin: 40px auto;
      padding: 0 20px;
      width: 100%;
      flex: 1;
    }
    .header-box {
      margin-bottom: 32px;
      border-bottom: 1px solid var(--border);
      padding-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      flex-wrap: wrap;
      gap: 16px;
    }
    h1 {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      font-weight: 700;
      color: #fff;
    }
    .subtitle {
      color: var(--muted);
      font-size: 0.9rem;
      margin-top: 6px;
    }
    .security-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.75rem;
      background: rgba(45,164,78,0.12);
      border: 1px solid rgba(45,164,78,0.3);
      color: #4caf70;
      padding: 4px 12px;
      border-radius: 20px;
      letter-spacing: 0.5px;
    }
    .booking-grid {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }
    .booking-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 24px;
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 20px;
      align-items: center;
      transition: border-color .2s ease;
    }
    .booking-card:hover {
      border-color: rgba(200,134,10,0.35);
    }
    .b-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #fff;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .b-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      color: var(--muted);
      font-size: 0.85rem;
      margin-bottom: 10px;
    }
    .b-meta span { display: flex; align-items: center; gap: 4px; }
    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 0.72rem;
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
    .btn-track {
      background: var(--gold);
      color: #fff;
      padding: 10px 18px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 0.82rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      transition: background .2s;
      white-space: nowrap;
    }
    .btn-track:hover { background: var(--gold-lt); }
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: var(--card-bg);
      border: 1px dashed var(--border);
      border-radius: 6px;
    }
    .empty-state p { color: var(--muted); margin-bottom: 20px; }
    footer {
      background: #0A0906;
      border-top: 1px solid var(--border);
      padding: 24px 5%;
      text-align: center;
      color: rgba(255,255,255,0.25);
      font-size: 0.8rem;
      margin-top: 40px;
    }
    @media(max-width: 650px) {
      nav { padding: 14px 16px; flex-wrap: wrap; gap: 10px; }
      .nav-links { gap: 14px; }
      .container { margin: 24px auto; padding: 0 16px; }
      h1 { font-size: 1.6rem; }
      .booking-card { grid-template-columns: 1fr; padding: 18px 16px; }
      .header-box { flex-direction: column; align-items: flex-start; gap: 12px; }
      .btn-track { text-align: center; display: block; width: 100%; margin-top: 8px; }
    }
  </style>
</head>
<body>
  <nav>
    <a href="<?= url('/') ?>" class="logo">Terminal 1</a>
    <div class="nav-links">
      <a href="<?= url('/') ?>">← Home</a>
      <a href="<?= url('/#menu') ?>">Menu</a>
      <a href="<?= url('/#contact') ?>">Book Table</a>
    </div>
  </nav>

  <div class="container">
    <div class="header-box">
      <div>
        <h1>My Reservations</h1>
        <p class="subtitle">Order &amp; Booking Ledger for <?= e($user['name']) ?></p>
      </div>
      <div class="security-badge">
        <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>Verified Account Ledger</span>
      </div>
    </div>

    <?php if(empty($bookings)): ?>
      <div class="empty-state">
        <div style="margin-bottom: 16px; color: var(--gold);">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
        </div>
        <h3>No reservations found</h3>
        <p>You have not made any table reservations yet.</p>
        <a href="<?= url('/#contact') ?>" class="btn-track">Reserve a Table Now</a>
      </div>
    <?php else: ?>
      <div class="booking-grid">
        <?php foreach($bookings as $b): ?>
          <div class="booking-card">
            <div>
              <div class="b-title">
                <span><?= e($b['occasion'] ?: 'Table Reservation') ?></span>
                <span class="badge badge-<?= e($b['status']) ?>"><?= ucfirst(e($b['status'])) ?></span>
                <span class="badge badge-<?= e($b['payment_status'] ?? 'unpaid') ?>">Deposit: <?= ucfirst(e($b['payment_status'] ?? 'unpaid')) ?></span>
              </div>
              <div class="b-meta">
                <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg><?= $b['booking_date'] ? date('D, d M Y', strtotime($b['booking_date'])) : date('D, d M Y', strtotime($b['created_at'])) ?></span>
                <?php if($b['booking_time']): ?>
                  <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><?= date('h:i A', strtotime($b['booking_time'])) ?></span>
                <?php endif; ?>
                <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><?= (int)$b['guests'] ?> <?= (int)$b['guests'] > 1 ? 'Guests' : 'Guest' ?></span>
                <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg><?= e($b['phone']) ?></span>
              </div>
              <?php if(!empty($b['message'])): ?>
                <div style="font-size:0.8rem;color:rgba(255,255,255,0.45);font-style:italic;">
                  "<?= e($b['message']) ?>"
                </div>
              <?php endif; ?>
            </div>
            <div>
              <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="<?= url('/bookings/view?id=' . $b['id'] . '&token=' . urlencode($b['tracking_token'] ?? '')) ?>" class="btn-track">
                  View &amp; Track &rarr;
                </a>
                <?php if($b['status'] !== 'cancelled' && $b['status'] !== 'completed'): ?>
                  <button type="button" class="btn-cancel-small" onclick="promptCancelBooking(<?= (int)$b['id'] ?>, '<?= e($b['tracking_token'] ?? '') ?>', '<?= e($b['booking_date'] ?: 'this date') ?>')">
                    Cancel Reservation
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="cancellation-policy-box" style="margin-top: 32px; padding: 18px 20px; background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 6px; font-size: 0.82rem; color: var(--muted); line-height: 1.5;">
        <strong style="color: #fff; display: block; margin-bottom: 4px;">Dining Cancellation Policy</strong>
        Table reservations may be cancelled up to 2 hours before scheduled dining service without penalty. For special group reservations or dietary tasting menus, please contact the concierge desk directly.
      </div>
    <?php endif; ?>
  </div>

  <!-- Cancellation Modal -->
  <div id="cancelModal" class="cancel-modal-overlay" onclick="closeCancelModal(event)">
    <div class="cancel-modal-box" onclick="event.stopPropagation()">
      <h3 style="font-family:'Playfair Display',serif; font-size:1.35rem; color:#fff; margin-bottom:10px;">Cancel Reservation</h3>
      <p id="cancelModalText" style="font-size:0.85rem; color:var(--muted); line-height:1.5; margin-bottom:18px;">
        Are you sure you wish to cancel this reservation?
      </p>
      <div style="display:flex; justify-content:flex-end; gap:12px;">
        <button type="button" class="btn btn-ghost" onclick="closeCancelModal()" style="background:transparent; border:1px solid var(--border); color:var(--muted); padding:9px 16px; border-radius:4px; cursor:pointer;">Keep Reservation</button>
        <button type="button" class="btn btn-cancel-confirm" id="btnConfirmCancel" onclick="executeBookingCancellation()" style="background:rgba(207,34,46,0.15); border:1px solid rgba(207,34,46,0.35); color:#ff7b72; padding:9px 18px; border-radius:4px; font-weight:600; cursor:pointer;">Yes, Cancel</button>
      </div>
    </div>
  </div>

  <style>
    .btn-cancel-small {
      background: transparent;
      border: 1px solid rgba(207, 34, 46, 0.3);
      color: #ff7b72;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 0.74rem;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      font-weight: 600;
      cursor: pointer;
      transition: all .2s;
      white-space: nowrap;
    }
    .btn-cancel-small:hover {
      background: rgba(207, 34, 46, 0.15);
      border-color: #ff7b72;
      color: #fff;
    }
    .cancel-modal-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,0.78); backdrop-filter: blur(8px);
      z-index: 999; display: none; align-items: center; justify-content: center; padding: 20px;
    }
    .cancel-modal-overlay.active { display: flex; }
    .cancel-modal-box {
      background: #181612; border: 1px solid rgba(255,255,255,0.12); border-radius: 6px;
      padding: 26px; max-width: 440px; width: 100%;
    }
  </style>

  <script>
  let targetCancelId = 0;
  let targetCancelToken = '';

  function promptCancelBooking(id, token, dateStr) {
    targetCancelId = id;
    targetCancelToken = token;
    document.getElementById('cancelModalText').textContent = 'Are you sure you wish to cancel your reservation for ' + dateStr + '? This action cannot be undone.';
    document.getElementById('cancelModal').classList.add('active');
  }

  function closeCancelModal(e) {
    if (e && e.target !== e.currentTarget && !e.target.classList.contains('btn-ghost')) return;
    document.getElementById('cancelModal').classList.remove('active');
  }

  async function executeBookingCancellation() {
    if (!targetCancelId) return;
    const btn = document.getElementById('btnConfirmCancel');
    if (btn.disabled) return;
    btn.disabled = true;
    btn.textContent = 'Cancelling...';

    try {
      const formData = new FormData();
      formData.append('csrf_token', '<?= csrfToken() ?>');
      formData.append('id', targetCancelId);
      formData.append('token', targetCancelToken);

      const res = await fetch('<?= url('/bookings/cancel') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      });
      const data = await res.json();
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.message || 'Could not cancel booking.');
        btn.disabled = false;
        btn.textContent = 'Yes, Cancel';
      }
    } catch(e) {
      alert('Network error while cancelling reservation.');
      btn.disabled = false;
      btn.textContent = 'Yes, Cancel';
    }
  }
  </script>

  <footer>
    &copy; <?= date('Y') ?> Terminal 1: The Restaurant. All rights reserved. &bull; <a href="<?= url('/privacy') ?>" style="color:inherit;text-decoration:none;">Privacy Policy</a> &bull; <a href="<?= url('/terms') ?>" style="color:inherit;text-decoration:none;">Terms &amp; Conditions</a>
  </footer>
</body>
</html>
