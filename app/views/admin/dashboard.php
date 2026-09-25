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
  <div class="stat-card"><div class="stat-num" style="color:#2da44e">₹<?= number_format($stats['revenue'],0) ?></div><div class="stat-label">Total Revenue</div></div>
  <div class="stat-card"><div class="stat-num" style="color:#2da44e"><?= $stats['paid_count'] ?></div><div class="stat-label">Payments Received</div></div>
  <div class="stat-card"><div class="stat-num" style="color:#cf222e"><?= $stats['refunded_count'] ?></div><div class="stat-label">Refunded</div></div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">Revenue — Last 14 Days</div></div>
  <canvas id="revenueChart" height="80"></canvas>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">Bookings — Last 14 Days</div></div>
  <canvas id="bookingsChart" height="80"></canvas>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">Recent Bookings</div>
    <a href="<?= url('/admin/bookings') ?>" class="btn btn-primary btn-sm">View All</a>
  </div>
  <div class="table-responsive">
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
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const revenueData = <?= json_encode($revenue_by_day) ?>;
const bookingsData = <?= json_encode($bookings_by_day) ?>;

new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: revenueData.map(r => r.day),
    datasets: [{ label: 'Revenue (₹)', data: revenueData.map(r => r.total), borderColor: '#2da44e', backgroundColor: 'rgba(45,164,78,.1)', fill: true, tension: 0.3 }]
  },
  options: { responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('bookingsChart'), {
  type: 'bar',
  data: {
    labels: bookingsData.map(r => r.day),
    datasets: [{ label: 'Bookings', data: bookingsData.map(r => r.count), backgroundColor: '#C8860A' }]
  },
  options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>

<?php require APP_ROOT.'/app/views/layouts/admin_footer.php'; ?>
