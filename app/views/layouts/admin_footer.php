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
