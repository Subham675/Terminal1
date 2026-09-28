  </div><!-- .content -->
</div><!-- .main -->

<!-- GLOBAL STYLED CONFIRMATION MODAL -->
<div id="adminGlobalConfirmModal" class="admin-confirm-overlay" onclick="closeAdminConfirmModal(event)" style="position:fixed;inset:0;background:rgba(0,0,0,0.8);backdrop-filter:blur(8px);z-index:99999;display:none;align-items:center;justify-content:center;padding:20px;">
  <div class="admin-confirm-box" onclick="event.stopPropagation()" style="background:#181612;border:1px solid rgba(255,255,255,0.14);border-radius:6px;max-width:440px;width:100%;padding:28px;box-shadow:0 20px 40px rgba(0,0,0,0.8);">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
      <div id="adminConfirmIcon" style="width:36px;height:36px;border-radius:50%;background:rgba(200,134,10,0.15);color:var(--gold);display:flex;align-items:center;justify-content:center;font-size:1.1rem;">⚠</div>
      <h3 id="adminConfirmTitle" style="font-family:var(--font-display);font-size:1.3rem;color:#fff;font-weight:600;">Confirm Action</h3>
    </div>
    <p id="adminConfirmMessage" style="font-size:0.86rem;color:var(--text-muted);line-height:1.55;margin-bottom:24px;">Are you sure you wish to proceed?</p>
    <div style="display:flex;justify-content:flex-end;gap:12px;">
      <button type="button" onclick="closeAdminConfirmModal()" style="background:transparent;border:1px solid var(--border);color:var(--text-muted);padding:9px 16px;border-radius:4px;font-size:0.82rem;font-weight:600;cursor:pointer;font-family:inherit;">Cancel</button>
      <button type="button" id="adminConfirmSubmitBtn" style="background:#C8860A;border:none;color:#fff;padding:9px 20px;border-radius:4px;font-size:0.82rem;font-weight:600;cursor:pointer;font-family:inherit;">Proceed</button>
    </div>
  </div>
</div>

<script>
// ── Mobile Sidebar Toggle ──
function toggleAdminSidebar() {
  const sb = document.querySelector('.sidebar');
  const ov = document.getElementById('sidebarOverlay');
  if (sb) sb.classList.toggle('open');
  if (ov) ov.classList.toggle('open');
}

// ── Global Styled Confirm Modal ──
let pendingConfirmCallback = null;

function showAdminConfirm(title, message, callback, isDanger = false) {
  pendingConfirmCallback = callback;
  document.getElementById('adminConfirmTitle').textContent = title || 'Confirm Action';
  document.getElementById('adminConfirmMessage').textContent = message || 'Are you sure you wish to proceed?';
  
  const submitBtn = document.getElementById('adminConfirmSubmitBtn');
  const icon = document.getElementById('adminConfirmIcon');
  if (isDanger) {
    submitBtn.style.background = '#f85149';
    icon.style.background = 'rgba(248,81,73,0.15)';
    icon.style.color = '#f85149';
  } else {
    submitBtn.style.background = '#C8860A';
    icon.style.background = 'rgba(200,134,10,0.15)';
    icon.style.color = '#C8860A';
  }
  document.getElementById('adminGlobalConfirmModal').style.display = 'flex';
}

function closeAdminConfirmModal() {
  document.getElementById('adminGlobalConfirmModal').style.display = 'none';
  pendingConfirmCallback = null;
}

document.getElementById('adminConfirmSubmitBtn').addEventListener('click', () => {
  if (typeof pendingConfirmCallback === 'function') {
    const cb = pendingConfirmCallback;
    closeAdminConfirmModal();
    cb();
  }
});

// Intercept forms with data-confirm or confirmation triggers
document.addEventListener('submit', function(e) {
  const form = e.target;
  const confirmMsg = form.getAttribute('data-confirm');
  if (confirmMsg && !form.dataset.confirmed) {
    e.preventDefault();
    const title = form.getAttribute('data-title') || 'Confirm Action';
    const isDanger = form.getAttribute('data-danger') === 'true';
    showAdminConfirm(title, confirmMsg, () => {
      form.dataset.confirmed = 'true';
      form.submit();
    }, isDanger);
  }
});

// ── Rate limit double-submit protection on all admin forms ──
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function(e) {
    const btn = form.querySelector('button[type="submit"]');
    if (btn && !btn.disabled) {
      setTimeout(() => { btn.disabled = true; }, 10);
      setTimeout(() => { if (btn) btn.disabled = false; }, 3000);
    }
  });
});
</script>
</body>
</html>
