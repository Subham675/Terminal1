<?php $pageTitle='Bookings'; $activePage='bookings'; require APP_ROOT.'/app/views/layouts/admin_layout.php'; ?>
<?php $f=flash('bookings'); if($f): ?>
  <div class="flash flash-<?= $f['type'] ?>"><?= e($f['message']) ?></div>
<?php endif; ?>
<div class="card">
  <div class="card-header"><div class="card-title">All Reservations (<?= count($bookings) ?>)</div></div>
  <table>
    <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Occasion</th><th>Guests</th><th>Date</th><th>Message</th><th>Status</th><th>Actions</th></tr></thead>
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
      <td style="display:flex;gap:6px;flex-wrap:wrap">
        <?php foreach(['confirmed','cancelled','completed'] as $s): ?>
          <?php if($b['status']!==$s): ?>
          <form method="POST" action="/admin/bookings/status">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="id" value="<?= $b['id'] ?>">
            <input type="hidden" name="status" value="<?= $s ?>">
            <button class="btn btn-sm <?= $s==='confirmed'?'btn-success':($s==='cancelled'?'btn-danger':'btn-primary') ?>"><?= ucfirst($s) ?></button>
          </form>
          <?php endif; ?>
        <?php endforeach; ?>
        <form method="POST" action="/admin/bookings/delete" onsubmit="return confirm('Delete this booking?')">
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
<?php require APP_ROOT.'/app/views/layouts/admin_footer.php'; ?>
