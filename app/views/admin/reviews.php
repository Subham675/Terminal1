<?php 
$pageTitle = 'Guest Reviews'; 
$activePage = 'reviews'; 
require APP_ROOT . '/app/views/layouts/admin_layout.php'; 

$totalReviews = count($reviews);
$complimentCount = 0;
$complaintCount = 0;
$pendingCount = 0;

foreach ($reviews as $r) {
    if (($r['type'] ?? '') === 'compliment') $complimentCount++;
    if (($r['type'] ?? '') === 'complaint') $complaintCount++;
    if (($r['status'] ?? '') === 'pending') $pendingCount++;
}
?>

<?php $f = flash('reviews'); if ($f): ?>
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

<!-- KPI EXECUTIVE OVERVIEW -->
<div class="kpi-grid">
  <div class="kpi-card">
    <div class="kpi-top">
      <span class="kpi-label">Total Reflections</span>
      <div class="kpi-icon-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </div>
    </div>
    <div class="kpi-value"><?= $totalReviews ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-green"><?= $stats['avg_rating'] ?> / 5.0 Avg</span>
      <span>All guest entries</span>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-top">
      <span class="kpi-label">Compliments &amp; Praise</span>
      <div class="kpi-icon-wrap" style="color:var(--success)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="color:var(--success)"><?= $complimentCount ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-green"><?= $totalReviews > 0 ? round(($complimentCount / $totalReviews) * 100) : 0 ?>%</span>
      <span>Positive sentiment</span>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-top">
      <span class="kpi-label">Critiques &amp; Complaints</span>
      <div class="kpi-icon-wrap" style="color:var(--danger)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="color:#f87171"><?= $complaintCount ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator" style="background:rgba(248,81,73,0.12);color:var(--danger);border:1px solid rgba(248,81,73,0.25)">Action Required</span>
      <span>Operational feedback</span>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-top">
      <span class="kpi-label">Pending Moderation</span>
      <div class="kpi-icon-wrap" style="color:var(--warn)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="color:var(--warn)"><?= $pendingCount ?></div>
    <div class="kpi-footer">
      <span>Require status check</span>
    </div>
  </div>
</div>

<!-- FILTER & SEARCH BAR -->
<div class="filter-bar" style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px;background:var(--card);border:1px solid var(--border);padding:14px 20px;border-radius:8px;flex-wrap:wrap">
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <button class="filter-tab active" onclick="filterReviews('all', this)">All (<?= $totalReviews ?>)</button>
    <button class="filter-tab" onclick="filterReviews('compliment', this)" style="border-left:2px solid var(--success)">Compliments (<?= $complimentCount ?>)</button>
    <button class="filter-tab" onclick="filterReviews('complaint', this)" style="border-left:2px solid var(--danger)">Complaints (<?= $complaintCount ?>)</button>
  </div>
  <div style="position:relative">
    <input type="text" id="reviewSearch" placeholder="Search guests, titles, reflections..." onkeyup="searchReviews()" style="background:#12100C;border:1px solid var(--border);color:#fff;padding:8px 14px 8px 34px;border-radius:6px;font-size:0.84rem;width:260px">
    <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-dim)" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
  </div>
</div>

<!-- REVIEWS DATA TABLE / FEED -->
<div class="card" style="padding:0;overflow:hidden">
  <div style="padding:18px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
    <h2 style="font-family:var(--font-display);font-size:1.15rem;font-weight:600">Guestbook Reflections &amp; Service Ledger</h2>
    <span style="font-size:0.78rem;color:var(--text-dim)">Showing <?= count($reviews) ?> entries</span>
  </div>

  <?php if (empty($reviews)): ?>
    <div style="padding:60px 20px;text-align:center;color:var(--text-dim)">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px;opacity:0.4"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      <p style="font-size:0.95rem">No guest reviews submitted yet.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table" id="reviewsTable">
        <thead>
          <tr>
            <th>Guest &amp; Date</th>
            <th>Type</th>
            <th>Rating</th>
            <th>Reflection</th>
            <th>Status</th>
            <th>Official Response</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reviews as $rev): ?>
            <tr class="review-row" data-type="<?= e($rev['type']) ?>" data-search="<?= e(strtolower(($rev['name'] ?? '') . ' ' . ($rev['title'] ?? '') . ' ' . ($rev['content'] ?? ''))) ?>">
              <td style="min-width:180px">
                <div style="display:flex;align-items:center;gap:10px">
                  <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,rgba(200,134,10,0.3),rgba(200,134,10,0.08));border:1px solid var(--border-gold);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.8rem;color:var(--gold-lt)">
                    <?= strtoupper(substr($rev['name'] ?? 'G', 0, 1)) ?>
                  </div>
                  <div>
                    <strong style="color:#fff;display:block;font-size:0.88rem"><?= e($rev['name']) ?></strong>
                    <span style="font-size:0.75rem;color:var(--text-dim)"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
                    <?php if (!empty($rev['email'])): ?>
                      <div style="font-size:0.72rem;color:var(--text-muted);opacity:0.8"><?= e($rev['email']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <td>
                <?php if ($rev['type'] === 'compliment'): ?>
                  <span class="badge" style="background:rgba(63,185,80,0.12);color:#3fb950;border:1px solid rgba(63,185,80,0.25);font-size:0.75rem;padding:3px 10px;border-radius:12px;font-weight:600">★ Compliment</span>
                <?php else: ?>
                  <span class="badge" style="background:rgba(248,81,73,0.12);color:#f85149;border:1px solid rgba(248,81,73,0.25);font-size:0.75rem;padding:3px 10px;border-radius:12px;font-weight:600">⚠ Complaint</span>
                <?php endif; ?>
              </td>

              <td>
                <div style="color:var(--gold-lt);font-size:0.88rem;letter-spacing:1px">
                  <?= str_repeat('★', (int)$rev['rating']) ?><span style="opacity:0.2"><?= str_repeat('★', 5 - (int)$rev['rating']) ?></span>
                </div>
              </td>

              <td style="max-width:320px">
                <?php if (!empty($rev['title'])): ?>
                  <div style="font-weight:600;color:var(--gold-lt);font-size:0.85rem;margin-bottom:3px"><?= e($rev['title']) ?></div>
                <?php endif; ?>
                <div style="font-size:0.82rem;color:var(--text-muted);line-height:1.45"><?= nl2br(e($rev['content'])) ?></div>
                <?php if (!empty($rev['visit_date'])): ?>
                  <div style="font-size:0.72rem;color:var(--text-dim);margin-top:4px">Dined on: <?= date('M j, Y', strtotime($rev['visit_date'])) ?></div>
                <?php endif; ?>
              </td>

              <td>
                <form method="POST" action="<?= url('/admin/reviews/status') ?>" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="id" value="<?= $rev['id'] ?>">
                  <select name="status" onchange="this.form.submit()" style="background:#12100C;border:1px solid var(--border);color:<?= $rev['status']==='approved'?'#3fb950':($rev['status']==='hidden'?'#f85149':'#d29922') ?>;padding:4px 8px;border-radius:4px;font-size:0.78rem;font-weight:600;cursor:pointer">
                    <option value="approved" <?= $rev['status']==='approved'?'selected':'' ?>>Approved</option>
                    <option value="pending" <?= $rev['status']==='pending'?'selected':'' ?>>Pending</option>
                    <option value="hidden" <?= $rev['status']==='hidden'?'selected':'' ?>>Hidden</option>
                  </select>
                </form>
              </td>

              <td style="min-width:200px">
                <?php if (!empty($rev['admin_reply'])): ?>
                  <div style="background:rgba(200,134,10,0.06);border-left:2px solid var(--gold);padding:6px 10px;border-radius:0 4px 4px 0;font-size:0.78rem;color:#E8A820;margin-bottom:6px">
                    <?= e($rev['admin_reply']) ?>
                  </div>
                <?php endif; ?>
                <button type="button" class="btn btn-sm" onclick="openReplyModal(<?= $rev['id'] ?>, <?= htmlspecialchars(json_encode($rev['admin_reply'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($rev['name']), ENT_QUOTES, 'UTF-8') ?>)" style="font-size:0.72rem;padding:3px 8px;background:rgba(255,255,255,0.04);border:1px solid var(--border);color:var(--text-muted);border-radius:3px">
                  <?= !empty($rev['admin_reply']) ? 'Edit Response' : '+ Write Response' ?>
                </button>
              </td>

              <td style="text-align:right">
                <form method="POST" action="<?= url('/admin/reviews/delete') ?>" onsubmit="return confirm('Permanently remove this review from the guestbook?');" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="id" value="<?= $rev['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger" style="padding:4px 8px;font-size:0.75rem" title="Delete Review">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- OFFICIAL RESPONSE MODAL -->
<div id="replyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);z-index:999;align-items:center;justify-content:center;padding:20px">
  <div style="background:#171511;border:1px solid var(--border-gold);border-radius:10px;width:100%;max-width:520px;padding:26px;box-shadow:0 16px 40px rgba(0,0,0,0.6)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
      <h3 style="font-family:var(--font-display);font-size:1.15rem;color:#fff">Management Response</h3>
      <button onclick="closeReplyModal()" style="background:none;border:none;color:var(--text-dim);font-size:1.2rem;cursor:pointer">&times;</button>
    </div>
    <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:16px">Replying to <span id="replyGuestName" style="color:#fff;font-weight:600">Guest</span>. This response will appear publicly beneath their review.</p>
    
    <form method="POST" action="<?= url('/admin/reviews/reply') ?>">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="id" id="replyReviewId" value="">
      <div style="margin-bottom:18px">
        <textarea name="admin_reply" id="replyTextarea" rows="4" placeholder="Dear Guest, thank you for sharing your thoughts..." style="width:100%;background:#0F0E0B;border:1px solid var(--border);border-radius:6px;padding:12px;color:#fff;font-family:inherit;font-size:0.85rem;resize:vertical;outline:none"></textarea>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px">
        <button type="button" onclick="closeReplyModal()" class="btn btn-sm" style="background:rgba(255,255,255,0.06);border:1px solid var(--border);color:var(--text-muted)">Cancel</button>
        <button type="submit" class="btn btn-sm btn-primary">Save Response</button>
      </div>
    </form>
  </div>
</div>

<style>
.filter-tab {
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--border);
  color: var(--text-muted);
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 0.82rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.16s ease;
}
.filter-tab:hover {
  color: #fff;
  background: rgba(255,255,255,0.06);
}
.filter-tab.active {
  background: var(--gold-glow);
  border-color: var(--border-gold);
  color: var(--gold-lt);
  font-weight: 600;
}
</style>

<script>
function filterReviews(type, btn) {
  document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const rows = document.querySelectorAll('.review-row');
  rows.forEach(r => {
    if (type === 'all' || r.getAttribute('data-type') === type) {
      r.style.display = '';
    } else {
      r.style.display = 'none';
    }
  });
}

function searchReviews() {
  const query = document.getElementById('reviewSearch').value.toLowerCase().trim();
  const rows = document.querySelectorAll('.review-row');
  rows.forEach(r => {
    const text = r.getAttribute('data-search') || '';
    r.style.display = text.includes(query) ? '' : 'none';
  });
}

function openReplyModal(id, reply, name) {
  document.getElementById('replyReviewId').value = id;
  document.getElementById('replyTextarea').value = reply || '';
  document.getElementById('replyGuestName').innerText = name || 'Guest';
  document.getElementById('replyModal').style.display = 'flex';
}

function closeReplyModal() {
  document.getElementById('replyModal').style.display = 'none';
}
</script>

<?php require APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
