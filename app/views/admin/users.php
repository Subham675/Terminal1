<?php $pageTitle='Users'; $activePage='users'; require APP_ROOT.'/app/views/layouts/admin_layout.php'; ?>
<?php $f=flash('users'); if($f): ?>
  <div class="flash flash-<?= $f['type'] ?>"><?= e($f['message']) ?></div>
<?php endif; ?>
<div class="card">
  <div class="card-header"><div class="card-title">All Users (<?= count($users) ?>)</div></div>
  <table>
    <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Verified</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= e($u['name']) ?></td>
      <td><?= e($u['email']) ?></td>
      <td><span class="badge badge-<?= $u['role'] ?>"><?= $u['role'] ?></span></td>
      <td><?= $u['is_verified']?'<span style="color:#2da44e">✓</span>':'<span style="color:#cf222e">✗</span>' ?></td>
      <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
      <td style="display:flex;gap:6px">
        <?php if($u['id']!==(int)authUser()['id']): ?>
        <form method="POST" action="/admin/users/role">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="id" value="<?= $u['id'] ?>">
          <input type="hidden" name="role" value="<?= $u['role']==='admin'?'user':'admin' ?>">
          <button class="btn btn-sm btn-primary"><?= $u['role']==='admin'?'→ User':'→ Admin' ?></button>
        </form>
        <form method="POST" action="/admin/users/delete" onsubmit="return confirm('Delete user?')">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="id" value="<?= $u['id'] ?>">
          <button class="btn btn-danger btn-sm">Delete</button>
        </form>
        <?php else: ?>
          <span style="color:rgba(255,255,255,.2);font-size:.75rem">You</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require APP_ROOT.'/app/views/layouts/admin_footer.php'; ?>
