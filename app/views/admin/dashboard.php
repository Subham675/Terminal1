<?php $pageTitle='Dashboard'; $activePage='dashboard'; require APP_ROOT.'/app/views/layouts/admin_layout.php'; ?>

<?php $f=flash('dashboard'); if($f): ?>
  <div class="flash flash-<?= $f['type'] ?>"><?= e($f['message']) ?></div>
<?php endif; ?>

<div class="stat-grid">
  <div class="stat-card"><div class="stat-num"><?= $stats['users'] ?></div><div class="stat-label">Total Users</div></div>
  <div class="stat-card"><div class="stat-num"><?= $stats['bookings'] ?></div><div class="stat-label">Total Bookings</div></div>
  <div class="stat-card"><div class="stat-num" style="color:#d29922"><?= $stats['pending'] ?></div><div class="stat-label">Pending</div></div>
  <div class="stat-card"><div class="stat-num" style="color:#2da44e"><?= $stats['confirmed'] ?></div><div class="stat-label">Confirmed</div></div>
  <div class="stat-card"><div class="stat-num"><?= $stats['menu_items'] ?></div><div class="stat-label">Menu Items</div></div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">Recent Bookings</div>
    <a href="/admin/bookings" class="btn btn-primary btn-sm">View All</a>
  </div>
  <table>
    <thead><tr><th>Name</th><th>Phone</th><th>Occasion</th><th>Guests</th><th>Date</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($recent_bookings as $b): ?>
      <tr>
        <td><?= e($b['name']) ?></td>
        <td><?= e($b['phone']) ?></td>
        <td><?= e($b['occasion'] ?? '—') ?></td>
        <td><?= $b['guests'] ?></td>
        <td><?= $b['booking_date'] ? date('d M Y', strtotime($b['booking_date'])) : date('d M Y', strtotime($b['created_at'])) ?></td>
        <td><span class="badge badge-<?= $b['status'] ?>"><?= $b['status'] ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require APP_ROOT.'/app/views/layouts/admin_footer.php'; ?>
