  </div><!-- .content -->
</div><!-- .main -->
<script>
// ── Mobile Sidebar Toggle ──
function toggleAdminSidebar() {
  const sb = document.querySelector('.sidebar');
  const ov = document.getElementById('sidebarOverlay');
  if (sb) sb.classList.toggle('open');
  if (ov) ov.classList.toggle('open');
}

// ── Intent-based Speculative Prefetching ──
const prefetchedAdminHrefs = new Set();
function prefetchAdminUrl(url) {
  if (!url || prefetchedAdminHrefs.has(url)) return;
  try {
    const u = new URL(url, window.location.href);
    if (u.origin !== window.location.origin) return;
    if (u.pathname === window.location.pathname && u.search === window.location.search) return;
    if (u.pathname.includes('/auth/logout')) return;
    prefetchedAdminHrefs.add(url);
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = url;
    document.head.appendChild(link);
  } catch(e) {}
}

let adminHoverTimer = null;
document.addEventListener('mouseover', (e) => {
  const a = e.target.closest('a');
  if (!a || !a.href) return;
  clearTimeout(adminHoverTimer);
  adminHoverTimer = setTimeout(() => prefetchAdminUrl(a.href), 60);
}, { passive: true });

document.addEventListener('touchstart', (e) => {
  const a = e.target.closest('a');
  if (a && a.href) prefetchAdminUrl(a.href);
}, { passive: true });

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
