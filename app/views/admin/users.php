<?php 
$pageTitle = 'User Ledger'; 
$activePage = 'users'; 
require APP_ROOT . '/app/views/layouts/admin_layout.php'; 

$totalUsers = count($users);
$adminCount = 0;
$verifiedCount = 0;

foreach ($users as $u) {
    if (($u['role'] ?? '') === 'admin') $adminCount++;
    if (!empty($u['is_verified'])) $verifiedCount++;
}
$dinerCount = $totalUsers - $adminCount;
$currentAdminId = (int)authUser()['id'];
?>

<?php $f = flash('users'); if ($f): ?>
  <div class="flash flash-<?= $f['type'] ?>">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <?php if ($f['type'] === 'success'): ?>
        <polyline points="20 6 9 17 4 12"/>
      <?php else: ?>
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
      <?php endif; ?>
    </svg>
    <span><?= e($f['message']) ?></span>
  </div>
<?php endif; ?>

<!-- TOP METRIC CARDS -->
<div class="kpi-grid" style="margin-bottom:24px;">
  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">Registered Accounts</span>
      <div class="kpi-icon-wrap" style="color:var(--gold-lt);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;"><?= $totalUsers ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-neutral">Total Customer &amp; Staff Base</span>
    </div>
  </div>

  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">Diners &amp; Guests</span>
      <div class="kpi-icon-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;"><?= $dinerCount ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-neutral">Standard Dining Accounts</span>
    </div>
  </div>

  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">System Admins</span>
      <div class="kpi-icon-wrap" style="color:var(--gold-lt);background:var(--gold-glow);border-color:var(--border-gold);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;color:var(--gold-lt);"><?= $adminCount ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-amber">Full Operations Clearance</span>
    </div>
  </div>

  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">Verified Security</span>
      <div class="kpi-icon-wrap" style="color:var(--success);background:var(--success-bg);border-color:rgba(63,185,80,0.25);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;color:var(--success);"><?= $verifiedCount ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-green"><?= round(($verifiedCount / max(1, $totalUsers)) * 100) ?>% Confirmed Mailbox</span>
    </div>
  </div>
</div>

<!-- USER LEDGER CARD -->
<div class="card">
  <div class="card-header" style="gap:16px;">
    <div>
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Master User Directory
      </div>
      <div style="font-size:0.78rem;color:var(--text-dim);margin-top:3px;">
        Oversee registered customer accounts, verify credentials, and manage operational administrative roles.
      </div>
    </div>

    <!-- Controls -->
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
      <div style="position:relative;">
        <input type="text" id="userSearch" placeholder="Search by name, email, or #ID…" oninput="filterUsers()" style="background:rgba(255,255,255,0.04);border:1px solid var(--border);color:#fff;padding:8px 12px 8px 32px;border-radius:6px;font-size:0.82rem;width:240px;outline:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-dim)" stroke-width="2" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/></svg>
      </div>

      <div style="display:flex;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:6px;padding:3px;">
        <button class="user-filter-tab active" data-filter="all" onclick="setUserFilter('all', this)">All (<?= $totalUsers ?>)</button>
        <button class="user-filter-tab" data-filter="admin" onclick="setUserFilter('admin', this)">Admins (<?= $adminCount ?>)</button>
        <button class="user-filter-tab" data-filter="user" onclick="setUserFilter('user', this)">Diners (<?= $dinerCount ?>)</button>
      </div>
    </div>
  </div>

  <style>
    .user-filter-tab{background:none;border:none;color:var(--text-muted);font-size:0.75rem;font-weight:600;padding:5px 12px;border-radius:4px;cursor:pointer;transition:all .15s;font-family:var(--font-body)}
    .user-filter-tab:hover{color:#fff}
    .user-filter-tab.active{background:rgba(200,134,10,0.18);color:var(--gold-lt);border:1px solid var(--border-gold)}
    .user-avatar-initials{width:36px;height:36px;border-radius:6px;background:linear-gradient(135deg,rgba(255,255,255,0.1),rgba(255,255,255,0.02));border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.82rem;flex-shrink:0}
    .user-avatar-admin{background:linear-gradient(135deg,rgba(200,134,10,0.3),rgba(200,134,10,0.08));border-color:var(--border-gold);color:var(--gold-lt)}
  </style>

  <div class="table-responsive">
    <table id="usersTable">
      <thead>
        <tr>
          <th>Account Details</th>
          <th>Email Address</th>
          <th>Role &amp; Permissions</th>
          <th>Security Status</th>
          <th>Member Since</th>
          <th>Access Governance</th>
        </tr>
      </thead>
      <tbody>
      <?php if(empty($users)): ?>
        <tr>
          <td colspan="6" style="padding:48px 20px;text-align:center;color:var(--text-dim);">
            No user accounts found.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach($users as $u): ?>
          <?php 
            $initials = '';
            $parts = explode(' ', trim($u['name']));
            foreach($parts as $p) { if(!empty($p)) $initials .= strtoupper($p[0]); }
            $initials = substr($initials, 0, 2) ?: 'U';
            $role = strtolower($u['role'] ?? 'user');
            $isAdmin = ($role === 'admin');
            $isSelf = ((int)$u['id'] === $currentAdminId);
            $searchHaystack = strtolower(($u['name']??'') . ' ' . ($u['email']??'') . ' #' . $u['id'] . ' ' . $role);
          ?>
          <tr class="user-row" data-role="<?= $role ?>" data-search="<?= e($searchHaystack) ?>">
            <!-- Account -->
            <td>
              <div style="display:flex;align-items:center;gap:12px;">
                <div class="user-avatar-initials <?= $isAdmin ? 'user-avatar-admin' : '' ?>">
                  <?= $initials ?>
                </div>
                <div>
                  <div style="font-weight:600;color:#fff;font-size:0.88rem;">
                    <?= e($u['name']) ?>
                    <?php if($isSelf): ?>
                      <span style="font-size:0.68rem;padding:2px 6px;border-radius:3px;background:rgba(200,134,10,0.15);color:var(--gold-lt);border:1px solid var(--border-gold);margin-left:6px;font-weight:700;">YOU</span>
                    <?php endif; ?>
                  </div>
                  <div style="font-size:0.74rem;color:var(--text-dim);margin-top:1px;">
                    Account ID #<?= $u['id'] ?>
                  </div>
                </div>
              </div>
            </td>

            <!-- Email -->
            <td>
              <span style="color:var(--text-muted);font-size:0.84rem;">
                <?= e($u['email']) ?>
              </span>
            </td>

            <!-- Role -->
            <td>
              <?php if($isAdmin): ?>
                <span class="badge badge-admin">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                  Lead Admin
                </span>
              <?php else: ?>
                <span class="badge badge-user">Diner Account</span>
              <?php endif; ?>
            </td>

            <!-- Security / Verified -->
            <td>
              <?php if(!empty($u['is_verified'])): ?>
                <span class="badge badge-confirmed" style="font-size:0.7rem;">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  Verified
                </span>
              <?php else: ?>
                <span class="badge badge-pending" style="font-size:0.7rem;">
                  Unverified
                </span>
              <?php endif; ?>
            </td>

            <!-- Member Since -->
            <td>
              <div style="color:var(--text-muted);font-size:0.82rem;">
                <?= date('d M Y', strtotime($u['created_at'])) ?>
              </div>
            </td>

            <!-- Actions -->
            <td>
              <div style="display:flex;align-items:center;gap:6px;">
                <?php if(!$isSelf): ?>
                  <!-- Toggle Role -->
                  <form method="POST" action="<?= url('/admin/users/role') ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="role" value="<?= $isAdmin ? 'user' : 'admin' ?>">
                    <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,0.05);border:1px solid var(--border);color:var(--text);" title="<?= $isAdmin ? 'Demote to Diner' : 'Promote to Admin' ?>">
                      <?= $isAdmin ? 'Revoke Admin' : 'Grant Admin' ?>
                    </button>
                  </form>

                  <!-- Delete User -->
                  <form method="POST" action="<?= url('/admin/users/delete') ?>" style="display:inline;" onsubmit="return confirm('Permanently delete account for &quot;<?= addslashes(e($u['name'])) ?>&quot;?')">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" style="padding:5px 9px;" title="Delete user account">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                  </form>
                <?php else: ?>
                  <span style="font-size:0.75rem;color:var(--text-dim);font-style:italic;">Current Session</span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
let currentUserFilter = 'all';

function setUserFilter(filter, btn) {
  currentUserFilter = filter;
  document.querySelectorAll('.user-filter-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  filterUsers();
}

function filterUsers() {
  const query = (document.getElementById('userSearch').value || '').trim().toLowerCase();
  const rows = document.querySelectorAll('.user-row');

  rows.forEach(row => {
    const rowRole = row.getAttribute('data-role') || '';
    const rowSearch = row.getAttribute('data-search') || '';

    const matchesRole = (currentUserFilter === 'all' || rowRole === currentUserFilter);
    const matchesSearch = (!query || rowSearch.includes(query));

    if (matchesRole && matchesSearch) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}
</script>

<?php require APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
