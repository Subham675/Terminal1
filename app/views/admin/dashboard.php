<?php $pageTitle='Executive Dashboard'; $activePage='dashboard'; require APP_ROOT.'/app/views/layouts/admin_layout.php'; ?>

<?php $f=flash('dashboard'); if($f): ?>
  <div class="flash flash-<?= $f['type'] ?>"><?= e($f['message']) ?></div>
<?php endif; ?>

<!-- 4 EXECUTIVE KPI SUMMARY CARDS -->
<div class="kpi-grid">
  <!-- 1. Total Revenue -->
  <div class="kpi-card">
    <div class="kpi-top">
      <span class="kpi-label">Gross Revenue</span>
      <div class="kpi-icon-wrap" style="color:#3fb950;background:rgba(63,185,80,0.08);border-color:rgba(63,185,80,0.25)">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 3h12M6 8h12M6 13l7.5 8M6 13h4a4 4 0 0 0 0-8"/>
        </svg>
      </div>
    </div>
    <div class="kpi-value" style="color:#3fb950">₹<?= number_format($stats['revenue'], 0) ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-green"><?= $stats['paid_count'] ?> Settled</span>
      <?php if ($stats['refunded_count'] > 0): ?>
        <span class="pill-indicator pill-red"><?= $stats['refunded_count'] ?> Refunded</span>
      <?php else: ?>
        <span class="pill-indicator pill-neutral">0 Chargebacks</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- 2. Total Reservations -->
  <div class="kpi-card">
    <div class="kpi-top">
      <span class="kpi-label">Dining Reservations</span>
      <div class="kpi-icon-wrap">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
        </svg>
      </div>
    </div>
    <div class="kpi-value"><?= number_format($stats['bookings'], 0) ?></div>
    <div class="kpi-footer">
      <?php if ($stats['pending'] > 0): ?>
        <span class="pill-indicator pill-amber"><?= $stats['pending'] ?> Pending Approval</span>
      <?php endif; ?>
      <span class="pill-indicator pill-green"><?= $stats['confirmed'] ?> Confirmed</span>
    </div>
  </div>

  <!-- 3. Registered Customers -->
  <div class="kpi-card">
    <div class="kpi-top">
      <span class="kpi-label">Registered Diners</span>
      <div class="kpi-icon-wrap">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
      </div>
    </div>
    <div class="kpi-value"><?= number_format($stats['users'], 0) ?></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-neutral">Verified Accounts</span>
    </div>
  </div>

  <!-- 4. Active Menu Items -->
  <div class="kpi-card">
    <div class="kpi-top">
      <span class="kpi-label">Kitchen Catalog</span>
      <div class="kpi-icon-wrap">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 2v20"/><path d="M21 15V2v0a3 3 0 0 0-3 3v6a3 3 0 0 0 3 3v7"/><path d="M8 2v20"/><path d="M4 2v7a4 4 0 0 0 8 0V2"/>
        </svg>
      </div>
    </div>
    <div class="kpi-value"><?= number_format($stats['menu_items'], 0) ?> <span style="font-size:1.1rem;font-weight:400;color:var(--text-dim)">Dishes</span></div>
    <div class="kpi-footer">
      <span class="pill-indicator pill-neutral">Available Across Kitchen</span>
    </div>
  </div>
</div>

<!-- 2-COLUMN ANALYTICS SECTION -->
<div class="analytics-grid">
  <!-- Revenue Chart -->
  <div class="card" style="margin-bottom:0">
    <div class="card-header">
      <div>
        <div class="card-title">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3fb950" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
          Revenue Trajectory
        </div>
        <div style="font-size:0.75rem;color:var(--text-dim);margin-top:2px;">Last 14 Days (Deposit Inflow)</div>
      </div>
    </div>
    <div class="card-body" style="padding:16px 20px;">
      <div style="position:relative;height:240px;width:100%;">
        <canvas id="revenueChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Bookings Chart -->
  <div class="card" style="margin-bottom:0">
    <div class="card-header">
      <div>
        <div class="card-title">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#E8A820" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><rect width="4" height="7" x="7" y="10" rx="1"/><rect width="4" height="12" x="15" y="5" rx="1"/></svg>
          Reservation Volume
        </div>
        <div style="font-size:0.75rem;color:var(--text-dim);margin-top:2px;">Last 14 Days (Daily Booking Inflow)</div>
      </div>
    </div>
    <div class="card-body" style="padding:16px 20px;">
      <div style="position:relative;height:240px;width:100%;">
        <canvas id="bookingsChart"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- RECENT RESERVATIONS LEDGER -->
<div class="card">
  <div class="card-header">
    <div class="card-title">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
      Recent Table Reservations
      <span class="status-pill" style="font-size:0.7rem;padding:3px 9px;margin-left:6px;"><span class="status-dot"></span> Live Feed</span>
    </div>
    <a href="<?= url('/admin/bookings') ?>" class="btn btn-primary btn-sm">
      Manage All Bookings ↗
    </a>
  </div>
  <div class="table-responsive">
    <?php if(empty($recent_bookings)): ?>
      <div style="padding:48px 24px;text-align:center;color:var(--text-dim)">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;opacity:.4"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
        <div style="font-size:0.95rem;font-weight:600;color:var(--text-muted)">No Reservations Recorded Yet</div>
        <div style="font-size:0.8rem;margin-top:4px;">Incoming customer table bookings will appear here automatically.</div>
      </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Guest Details</th>
          <th>Phone</th>
          <th>Occasion</th>
          <th>Party Size</th>
          <th>Dining Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($recent_bookings as $b): ?>
        <?php 
          $initials = '';
          $parts = explode(' ', trim($b['name']));
          foreach($parts as $p) { if(!empty($p)) $initials .= strtoupper($p[0]); }
          $initials = substr($initials, 0, 2);
        ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:12px">
              <div style="width:34px;height:34px;border-radius:6px;background:rgba(200,134,10,.14);border:1px solid var(--border-gold);color:var(--gold-lt);font-weight:700;font-size:0.8rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <?= $initials ?: 'G' ?>
              </div>
              <div>
                <div style="font-weight:600;color:#fff"><?= e($b['name']) ?></div>
                <div style="font-size:0.75rem;color:var(--text-dim)">#<?= $b['id'] ?> · <?= e($b['email'] ?? 'No email') ?></div>
              </div>
            </div>
          </td>
          <td>
            <span style="font-family:monospace;font-size:0.84rem;color:var(--text-muted)"><?= e($b['phone']) ?></span>
          </td>
          <td>
            <?php if(!empty($b['occasion'])): ?>
              <span class="pill-indicator pill-neutral" style="font-size:0.74rem;">
                <?= e($b['occasion']) ?>
              </span>
            <?php else: ?>
              <span style="color:var(--text-dim)">Standard</span>
            <?php endif; ?>
          </td>
          <td>
            <span style="font-weight:600;color:#fff"><?= $b['guests'] ?></span>
            <span style="font-size:0.75rem;color:var(--text-dim)">Guest<?= $b['guests'] > 1 ? 's' : '' ?></span>
          </td>
          <td>
            <div style="font-weight:600;color:#fff">
              <?= $b['booking_date'] ? date('d M Y', strtotime($b['booking_date'])) : date('d M Y', strtotime($b['created_at'])) ?>
            </div>
            <div style="font-size:0.72rem;color:var(--text-dim)">Assigned Service</div>
          </td>
          <td>
            <span class="badge badge-<?= $b['status'] ?>">
              <?= ucfirst($b['status']) ?>
            </span>
          </td>
          <td>
            <a href="<?= url('/admin/bookings') ?>" class="btn btn-sm" style="background:rgba(255,255,255,0.06);color:#fff;border:1px solid var(--border)">
              Manage
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const revenueData  = <?= json_encode($revenue_by_day) ?>;
const bookingsData = <?= json_encode($bookings_by_day) ?>;

// Dark Executive Chart Styling Defaults
Chart.defaults.color = 'rgba(243, 238, 227, 0.45)';
Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
Chart.defaults.font.size = 11;

// 1. Revenue Trajectory Line Chart
const revCtx = document.getElementById('revenueChart').getContext('2d');
const revGradient = revCtx.createLinearGradient(0, 0, 0, 240);
revGradient.addColorStop(0, 'rgba(63, 185, 80, 0.28)');
revGradient.addColorStop(1, 'rgba(63, 185, 80, 0.00)');

new Chart(revCtx, {
  type: 'line',
  data: {
    labels: revenueData.map(r => r.day),
    datasets: [{
      label: 'Revenue (₹)',
      data: revenueData.map(r => r.total),
      borderColor: '#3fb950',
      borderWidth: 2.2,
      backgroundColor: revGradient,
      fill: true,
      tension: 0.35,
      pointRadius: 3,
      pointHoverRadius: 6,
      pointBackgroundColor: '#3fb950',
      pointBorderColor: '#181612',
      pointBorderWidth: 2
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#1E1C17',
        borderColor: 'rgba(63, 185, 80, 0.4)',
        borderWidth: 1,
        titleColor: '#fff',
        bodyColor: '#3fb950',
        bodyFont: { weight: 'bold', size: 13 },
        padding: 10,
        cornerRadius: 6,
        displayColors: false,
        callbacks: {
          label: (ctx) => `Revenue: ₹${Number(ctx.parsed.y).toLocaleString('en-IN')}`
        }
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { maxTicksLimit: 7 }
      },
      y: {
        grid: { color: 'rgba(255, 255, 255, 0.05)' },
        ticks: {
          callback: (val) => '₹' + val
        }
      }
    }
  }
});

// 2. Bookings Inflow Bar Chart
const bookCtx = document.getElementById('bookingsChart').getContext('2d');
new Chart(bookCtx, {
  type: 'bar',
  data: {
    labels: bookingsData.map(r => r.day),
    datasets: [{
      label: 'Reservations',
      data: bookingsData.map(r => r.count),
      backgroundColor: '#C8860A',
      hoverBackgroundColor: '#E8A820',
      borderRadius: 4,
      barThickness: 16
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#1E1C17',
        borderColor: 'rgba(200, 134, 10, 0.4)',
        borderWidth: 1,
        titleColor: '#fff',
        bodyColor: '#E8A820',
        bodyFont: { weight: 'bold', size: 13 },
        padding: 10,
        cornerRadius: 6,
        displayColors: false,
        callbacks: {
          label: (ctx) => `Bookings: ${ctx.parsed.y}`
        }
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { maxTicksLimit: 7 }
      },
      y: {
        grid: { color: 'rgba(255, 255, 255, 0.05)' },
        ticks: { stepSize: 1 }
      }
    }
  }
});
</script>

<?php require APP_ROOT.'/app/views/layouts/admin_footer.php'; ?>
