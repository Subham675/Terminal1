<?php $pageTitle='Bookings'; $activePage='bookings'; require APP_ROOT.'/app/views/layouts/admin_layout.php'; ?>
<?php $f=flash('bookings'); if($f): ?>
  <div class="flash flash-<?= $f['type'] ?>"><?= e($f['message']) ?></div>
<?php endif; ?>

<div id="liveBookingAlert" style="display:none;margin-bottom:16px;padding:12px 18px;background:#1a3a2a;
     border:1px solid #2da44e;border-radius:8px;color:#4caf70;display:flex;align-items:center;gap:10px;">
  <span style="width:8px;height:8px;border-radius:50%;background:#2da44e;display:inline-block;animation:pulseDot 1.5s infinite;"></span>
  <span id="liveBookingAlertText">🔔 A new booking just came in!</span>
  <button onclick="location.reload()" class="btn btn-sm btn-success" style="margin-left:auto;">Refresh to view</button>
</div>
<style>@keyframes pulseDot { 0%,100%{opacity:1;} 50%{opacity:.3;} }</style>

<div class="card">
  <div class="card-header"><div class="card-title">All Reservations (<?= count($bookings) ?>) <span id="liveIndicator" style="font-size:.7rem;color:#4caf70;font-weight:normal;">● live</span></div></div>
  <div class="table-responsive">
    <table>
      <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Occasion</th><th>Guests</th><th>Date</th><th>Message</th><th>Status</th><th>Payment</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($bookings as $b): ?>
      <tr>
        <td><?= $b['id'] ?></td>
        <td><?= e($b['name']) ?></td>
        <td><?= e($b['phone']) ?></td>
        <td><?= e($b['email'] ?? '—') ?></td>
        <td><?= e($b['occasion'] ?? '—') ?></td>
        <td><?= $b['guests'] ?></td>
        <td><?= $b['booking_date'] ? date('d M Y', strtotime($b['booking_date'])) : date('d M Y', strtotime($b['created_at'])) ?></td>
        <td><?= e(substr($b['message']??'—',0,40)) ?></td>
        <td><span class="badge badge-<?= $b['status'] ?>"><?= $b['status'] ?></span></td>
        <td>
          <span class="badge badge-<?= $b['payment_status']==='paid'?'confirmed':($b['payment_status']==='refunded'?'cancelled':'pending') ?>">
            <?= ucfirst($b['payment_status'] ?? 'unpaid') ?>
          </span>
          <?php if(($b['deposit_amount']??0) > 0): ?>
            <div style="font-size:.75rem;color:#8b949e;">₹<?= number_format($b['deposit_amount'],0) ?></div>
          <?php endif; ?>
        </td>
        <td style="display:flex;gap:6px;flex-wrap:wrap">
          <?php foreach(['confirmed','cancelled','completed'] as $s): ?>
            <?php if($b['status']!==$s): ?>
            <form method="POST" action="<?= url('/admin/bookings/status') ?>">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="id" value="<?= $b['id'] ?>">
              <input type="hidden" name="status" value="<?= $s ?>">
              <button class="btn btn-sm <?= $s==='confirmed'?'btn-success':($s==='cancelled'?'btn-danger':'btn-primary') ?>"><?= ucfirst($s) ?></button>
            </form>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php if(($b['payment_status']??'')==='paid'): ?>
            <form method="POST" action="<?= url('/admin/bookings/refund') ?>" onsubmit="return confirm('Refund this deposit? This cannot be undone.')">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
              <button class="btn btn-sm btn-danger">Refund</button>
            </form>
          <?php endif; ?>
          <form method="POST" action="<?= url('/admin/bookings/delete') ?>" onsubmit="return confirm('Delete this booking?')">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="id" value="<?= $b['id'] ?>">
            <button class="btn btn-danger btn-sm">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// ── Live "new booking" alert via Server-Sent Events ──
const bookingEvents = new EventSource('/admin/track/stream');

bookingEvents.addEventListener('new_booking', (e) => {
  const data = JSON.parse(e.data);
  const alertBox = document.getElementById('liveBookingAlert');
  document.getElementById('liveBookingAlertText').textContent =
    `🔔 A new booking just came in! (${data.pending_count} pending total)`;
  alertBox.style.display = 'flex';

  // Optional: browser notification if the tab isn't focused
  if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
    new Notification('Terminal 1 — New Booking', { body: `${data.pending_count} bookings pending review.` });
  }
});

bookingEvents.addEventListener('error', () => {
  document.getElementById('liveIndicator').style.color = '#8b949e';
  document.getElementById('liveIndicator').textContent = '○ reconnecting…';
});
bookingEvents.addEventListener('open', () => {
  document.getElementById('liveIndicator').style.color = '#4caf70';
  document.getElementById('liveIndicator').textContent = '● live';
});
</script>

<?php require APP_ROOT.'/app/views/layouts/admin_footer.php'; ?>
