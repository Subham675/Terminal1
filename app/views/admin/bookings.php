<?php 
$pageTitle = 'Table Bookings'; 
$activePage = 'bookings'; 
require APP_ROOT . '/app/views/layouts/admin_layout.php'; 

$totalCount = count($bookings);
$pendingCount = 0;
$confirmedCount = 0;
$completedCount = 0;
$cancelledCount = 0;
$totalDeposit = 0;

foreach ($bookings as $b) {
    $st = $b['status'] ?? 'pending';
    if ($st === 'pending') $pendingCount++;
    elseif ($st === 'confirmed') $confirmedCount++;
    elseif ($st === 'completed') $completedCount++;
    elseif ($st === 'cancelled') $cancelledCount++;

    if (($b['payment_status'] ?? '') === 'paid') {
        $totalDeposit += (float)($b['deposit_amount'] ?? 0);
    }
}
?>

<?php $f = flash('bookings'); if ($f): ?>
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

<!-- Live Booking Alert (Server-Sent Events) -->
<div id="liveBookingAlert" style="display:none;margin-bottom:20px;padding:14px 20px;background:rgba(63,185,80,0.1);border:1px solid rgba(63,185,80,0.3);border-radius:8px;color:#3fb950;align-items:center;justify-content:space-between;gap:12px;">
  <div style="display:flex;align-items:center;gap:10px;">
    <span style="width:8px;height:8px;border-radius:50%;background:#3fb950;box-shadow:0 0 8px #3fb950;display:inline-block;animation:pulseDot 1.5s infinite;"></span>
    <span id="liveBookingAlertText" style="font-weight:600;font-size:0.88rem;">New table reservation received!</span>
  </div>
  <button onclick="location.reload()" class="btn btn-sm btn-success">Refresh Ledger</button>
</div>

<!-- TOP METRIC CARDS -->
<div class="kpi-grid" style="margin-bottom:24px;">
  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">Total Volume</span>
      <div class="kpi-icon-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;"><?= $totalCount ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-neutral">All Historic Requests</span>
    </div>
  </div>

  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">Pending Action</span>
      <div class="kpi-icon-wrap" style="color:var(--warn);background:var(--warn-bg);border-color:rgba(210,153,34,0.25);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;color:var(--warn);"><?= $pendingCount ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator <?= $pendingCount > 0 ? 'pill-amber' : 'pill-neutral' ?>"><?= $pendingCount > 0 ? 'Requires Host Review' : 'Queue Clear' ?></span>
    </div>
  </div>

  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">Confirmed Tables</span>
      <div class="kpi-icon-wrap" style="color:var(--success);background:var(--success-bg);border-color:rgba(63,185,80,0.25);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;color:var(--success);"><?= $confirmedCount ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-green"><?= $completedCount ?> Fulfilled</span>
    </div>
  </div>

  <div class="kpi-card" style="padding:18px;">
    <div class="kpi-top">
      <span class="kpi-label">Deposits Settled</span>
      <div class="kpi-icon-wrap" style="color:var(--gold-lt);background:var(--gold-glow);border-color:var(--border-gold);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12M6 8h12M6 13l7.5 8M6 13h4a4 4 0 0 0 0-8"/></svg>
      </div>
    </div>
    <div class="kpi-value" style="font-size:1.6rem;color:var(--gold-lt);">₹<?= number_format($totalDeposit, 0) ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-neutral">Cover Guarantee Inflow</span>
    </div>
  </div>
</div>

<!-- RESERVATIONS LEDGER CARD -->
<div class="card">
  <div class="card-header" style="gap:16px;">
    <div>
      <div class="card-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
        Master Reservations Ledger
        <span id="liveIndicator" style="font-size:0.7rem;color:var(--success);font-weight:600;background:var(--success-bg);border:1px solid rgba(63,185,80,0.25);padding:3px 8px;border-radius:12px;display:inline-flex;align-items:center;gap:5px;margin-left:6px;">
          <span style="width:6px;height:6px;border-radius:50%;background:var(--success);display:inline-block;"></span> Live Stream
        </span>
      </div>
      <div style="font-size:0.78rem;color:var(--text-dim);margin-top:3px;">
        Manage guest arrivals, approve pending dining slots, and administer deposit refunds.
      </div>
    </div>

    <!-- Live Filter & Search Controls -->
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <div style="position:relative;">
        <input type="text" id="ledgerSearch" placeholder="Search guest, phone, email…" oninput="filterBookings()" style="background:rgba(255,255,255,0.04);border:1px solid var(--border);color:#fff;padding:8px 12px 8px 32px;border-radius:6px;font-size:0.82rem;width:240px;outline:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-dim)" stroke-width="2" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/></svg>
      </div>

      <div style="display:flex;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:6px;padding:3px;">
        <button class="filter-tab active" data-filter="all" onclick="setFilter('all', this)">All (<?= $totalCount ?>)</button>
        <button class="filter-tab" data-filter="pending" onclick="setFilter('pending', this)">Pending (<?= $pendingCount ?>)</button>
        <button class="filter-tab" data-filter="confirmed" onclick="setFilter('confirmed', this)">Confirmed (<?= $confirmedCount ?>)</button>
        <button class="filter-tab" data-filter="completed" onclick="setFilter('completed', this)">Fulfilled (<?= $completedCount ?>)</button>
        <button class="filter-tab" data-filter="cancelled" onclick="setFilter('cancelled', this)">Cancelled (<?= $cancelledCount ?>)</button>
      </div>
    </div>
  </div>

  <style>
    .filter-tab{background:none;border:none;color:var(--text-muted);font-size:0.75rem;font-weight:600;padding:5px 10px;border-radius:4px;cursor:pointer;transition:all .15s;font-family:var(--font-body)}
    .filter-tab:hover{color:#fff}
    .filter-tab.active{background:rgba(200,134,10,0.18);color:var(--gold-lt);border:1px solid var(--border-gold)}
    .action-group{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
    .btn-act{padding:4px 9px;border-radius:4px;font-size:0.72rem;font-weight:600;cursor:pointer;border:none;transition:all .15s;display:inline-flex;align-items:center;gap:4px;font-family:var(--font-body)}
    .btn-act-confirm{background:var(--success-bg);color:var(--success);border:1px solid rgba(63,185,80,0.3)}.btn-act-confirm:hover{background:var(--success);color:#fff}
    .btn-act-cancel{background:var(--danger-bg);color:var(--danger);border:1px solid rgba(248,81,73,0.3)}.btn-act-cancel:hover{background:var(--danger);color:#fff}
    .btn-act-complete{background:var(--gold-glow);color:var(--gold-lt);border:1px solid var(--border-gold)}.btn-act-complete:hover{background:var(--gold);color:#fff}
    .btn-act-delete{background:rgba(255,255,255,0.04);color:var(--text-dim);border:1px solid var(--border)}.btn-act-delete:hover{background:var(--danger);color:#fff;border-color:var(--danger)}
  </style>

  <div class="table-responsive">
    <table id="bookingsTable">
      <thead>
        <tr>
          <th>Guest Details</th>
          <th>Contact</th>
          <th>Occasion &amp; Notes</th>
          <th>Party</th>
          <th>Dining Schedule</th>
          <th>Deposit</th>
          <th>Status</th>
          <th>Operational Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if(empty($bookings)): ?>
        <tr>
          <td colspan="8" style="padding:48px 20px;text-align:center;color:var(--text-dim);">
            No table reservations recorded in system.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach($bookings as $b): ?>
          <?php 
            $initials = '';
            $parts = explode(' ', trim($b['name']));
            foreach($parts as $p) { if(!empty($p)) $initials .= strtoupper($p[0]); }
            $initials = substr($initials, 0, 2) ?: 'G';
            $status = strtolower($b['status'] ?? 'pending');
            $searchHaystack = strtolower(($b['name']??'') . ' ' . ($b['phone']??'') . ' ' . ($b['email']??'') . ' #' . $b['id'] . ' ' . ($b['occasion']??''));
          ?>
          <tr class="booking-row" data-status="<?= $status ?>" data-search="<?= e($searchHaystack) ?>">
            <!-- Guest -->
            <td>
              <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:34px;height:34px;border-radius:6px;background:linear-gradient(135deg,rgba(200,134,10,0.25),rgba(200,134,10,0.08));border:1px solid var(--border-gold);color:var(--gold-lt);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.78rem;flex-shrink:0;">
                  <?= $initials ?>
                </div>
                <div>
                  <div style="font-weight:600;color:#fff;font-size:0.88rem;"><?= e($b['name']) ?></div>
                  <div style="font-size:0.74rem;color:var(--text-dim);margin-top:1px;">
                    #<?= $b['id'] ?> · <?= e($b['email'] ?: 'No email') ?>
                  </div>
                </div>
              </div>
            </td>

            <!-- Contact -->
            <td>
              <a href="tel:<?= e($b['phone']) ?>" style="color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:5px;font-size:0.84rem;transition:color .15s;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <?= e($b['phone']) ?>
              </a>
            </td>

            <!-- Occasion & Notes -->
            <td>
              <?php if(!empty($b['occasion'])): ?>
                <span style="display:inline-block;padding:3px 8px;background:rgba(255,255,255,0.05);border:1px solid var(--border);border-radius:4px;font-size:0.75rem;color:var(--text);margin-bottom:3px;">
                  <?= e($b['occasion']) ?>
                </span>
              <?php endif; ?>
              <?php if(!empty($b['message'])): ?>
                <div style="font-size:0.74rem;color:var(--text-dim);max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= e($b['message']) ?>">
                  <?= e($b['message']) ?>
                </div>
              <?php else: ?>
                <div style="font-size:0.72rem;color:var(--text-dim);font-style:italic;">No special requests</div>
              <?php endif; ?>
            </td>

            <!-- Party Size -->
            <td>
              <span style="font-weight:600;color:#fff;font-size:0.86rem;display:inline-flex;align-items:center;gap:5px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--gold-lt)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <?= (int)$b['guests'] ?> Diners
              </span>
            </td>

            <!-- Dining Schedule -->
            <td>
              <div style="font-weight:600;color:var(--text);font-size:0.84rem;">
                <?= $b['booking_date'] ? date('d M Y', strtotime($b['booking_date'])) : date('d M Y', strtotime($b['created_at'])) ?>
              </div>
              <div style="font-size:0.72rem;color:var(--text-dim);margin-top:1px;">
                Booked <?= date('d M · H:i', strtotime($b['created_at'])) ?>
              </div>
            </td>

            <!-- Deposit / Payment -->
            <td>
              <?php $pay = strtolower($b['payment_status'] ?? 'unpaid'); ?>
              <?php if($pay === 'paid'): ?>
                <span class="badge badge-confirmed" style="font-size:0.7rem;">Paid</span>
                <div style="font-size:0.75rem;color:var(--success);font-weight:600;margin-top:2px;">
                  ₹<?= number_format((float)($b['deposit_amount'] ?? 0), 0) ?>
                </div>
              <?php elseif($pay === 'refunded'): ?>
                <span class="badge badge-cancelled" style="font-size:0.7rem;">Refunded</span>
              <?php else: ?>
                <span class="badge badge-user" style="font-size:0.7rem;">Unpaid</span>
              <?php endif; ?>
            </td>

            <!-- Status Badge -->
            <td>
              <span class="badge badge-<?= $status ?>">
                <span style="width:5px;height:5px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                <?= ucfirst($status) ?>
              </span>
            </td>

            <!-- Actions -->
            <td>
              <div class="action-group">
                <!-- If Pending: Show Confirm and Cancel -->
                <?php if($status !== 'confirmed'): ?>
                  <form method="POST" action="<?= url('/admin/bookings/status') ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" class="btn-act btn-act-confirm" title="Confirm Table">
                      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                      Confirm
                    </button>
                  </form>
                <?php endif; ?>

                <?php if($status === 'confirmed'): ?>
                  <form method="POST" action="<?= url('/admin/bookings/status') ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn-act btn-act-complete" title="Mark Completed">
                      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                      Fulfill
                    </button>
                  </form>
                <?php endif; ?>

                <?php if($status !== 'cancelled' && $status !== 'completed'): ?>
                  <form method="POST" action="<?= url('/admin/bookings/status') ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn-act btn-act-cancel" title="Cancel Booking">
                      Cancel
                    </button>
                  </form>
                <?php endif; ?>

                <!-- Refund Deposit (if paid) -->
                <?php if(($b['payment_status'] ?? '') === 'paid'): ?>
                  <form method="POST" action="<?= url('/admin/bookings/refund') ?>" style="display:inline;" onsubmit="return confirm('Initiate refund of ₹<?= number_format((float)($b['deposit_amount']??0),0) ?> to diner? This cannot be undone.')">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                    <button type="submit" class="btn-act btn-act-cancel" title="Refund Deposit">
                      Refund ₹<?= number_format((float)($b['deposit_amount']??0),0) ?>
                    </button>
                  </form>
                <?php endif; ?>

                <!-- Delete record -->
                <form method="POST" action="<?= url('/admin/bookings/delete') ?>" style="display:inline;" onsubmit="return confirm('Permanently remove reservation #<?= $b['id'] ?> from ledger?')">
                  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                  <input type="hidden" name="id" value="<?= $b['id'] ?>">
                  <button type="submit" class="btn-act btn-act-delete" title="Delete record">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </form>
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
let currentFilter = 'all';

function setFilter(filter, btn) {
  currentFilter = filter;
  document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  filterBookings();
}

function filterBookings() {
  const query = (document.getElementById('ledgerSearch').value || '').trim().toLowerCase();
  const rows = document.querySelectorAll('.booking-row');
  
  rows.forEach(row => {
    const rowStatus = row.getAttribute('data-status');
    const rowSearch = row.getAttribute('data-search') || '';
    
    const matchesFilter = (currentFilter === 'all' || rowStatus === currentFilter);
    const matchesSearch = (!query || rowSearch.includes(query));
    
    if (matchesFilter && matchesSearch) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

// ── Live "new booking" alert via Server-Sent Events ──
const bookingEvents = new EventSource('<?= url('/admin/track/stream') ?>');

bookingEvents.addEventListener('new_booking', (e) => {
  const data = JSON.parse(e.data);
  const alertBox = document.getElementById('liveBookingAlert');
  document.getElementById('liveBookingAlertText').textContent = 
    `A new booking just came in! (${data.pending_count} pending reviews awaiting confirmation)`;
  alertBox.style.display = 'flex';

  if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
    new Notification('Terminal 1 | New Table Reservation', { body: `${data.pending_count} bookings pending review.` });
  }
});

bookingEvents.addEventListener('error', () => {
  const ind = document.getElementById('liveIndicator');
  if(ind) {
    ind.style.color = '#8b949e';
    ind.innerHTML = '○ Reconnecting…';
  }
});
bookingEvents.addEventListener('open', () => {
  const ind = document.getElementById('liveIndicator');
  if(ind) {
    ind.style.color = 'var(--success)';
    ind.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:var(--success);display:inline-block;"></span> Live Stream';
  }
});
</script>

<?php require APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
