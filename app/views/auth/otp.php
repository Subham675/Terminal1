<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>OTP Verification | Terminal 1</title>
  <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>">
  <link rel="alternate icon" href="<?= asset('favicon.ico') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:#0F0E0B;font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
    .box{width:100%;max-width:400px;background:#1A1814;border:1px solid rgba(200,134,10,.2);border-radius:4px;overflow:hidden;text-align:center}
    .box-header{background:#C8860A;padding:28px}
    .box-header h1{font-family:'Playfair Display',serif;color:#fff;font-size:1.6rem}
    .box-body{padding:36px 32px}
    .otp-icon{margin-bottom:16px;display:flex;justify-content:center}
    .otp-title{font-size:1.1rem;color:#fff;font-weight:600;margin-bottom:8px}
    .otp-sub{color:rgba(255,255,255,.45);font-size:.85rem;line-height:1.6;margin-bottom:28px}
    .otp-inputs{display:flex;gap:10px;justify-content:center;margin-bottom:24px}
    .otp-input{width:48px;height:56px;background:rgba(255,255,255,.06);border:2px solid rgba(255,255,255,.12);color:#fff;font-size:1.4rem;font-weight:700;text-align:center;border-radius:2px;outline:none;transition:border-color .2s;font-family:'DM Sans',sans-serif}
    .otp-input:focus{border-color:#C8860A;background:rgba(200,134,10,.08)}
    input[name="otp"]{display:none}
    .btn-verify{width:100%;background:#C8860A;border:none;color:#fff;padding:13px;border-radius:2px;font-family:'DM Sans',sans-serif;font-size:.85rem;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer}
    .btn-verify:hover{background:#E8A820}
    .resend-link{color:rgba(255,255,255,.3);font-size:.82rem;margin-top:18px;display:block}
    .resend-link a{color:#C8860A;text-decoration:none}
    .flash{padding:10px 14px;border-radius:2px;margin-bottom:16px;font-size:.83rem}
    .flash-error{background:rgba(207,34,46,.15);border:1px solid rgba(207,34,46,.3);color:#cf222e}
    .flash-success{background:rgba(45,164,78,.15);border:1px solid rgba(45,164,78,.3);color:#2da44e}
    #timer{color:#C8860A;font-weight:600}
  </style>
</head>
<body>
<div class="box">
  <div class="box-header"><h1>OTP Verification</h1></div>
  <div class="box-body">
    <?php $f=flash('otp'); if($f): ?>
      <div class="flash flash-<?= $f['type'] ?>"><?= e($f['message']) ?></div>
    <?php endif; ?>
    <?php if (env('APP_ENV') === 'development' && !empty($_SESSION['dev_otp'])): ?>
      <div style="background:rgba(200,134,10,.1);border:1px dashed #C8860A;border-radius:2px;padding:12px;margin-bottom:20px;color:#E8A820;text-align:center;">
        <div style="font-size:.72rem;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:4px;">Development Mode Code</div>
        <div style="font-family:monospace;font-size:1.4rem;font-weight:700;letter-spacing:4px;color:#fff;"><?= e($_SESSION['dev_otp']) ?></div>
        <div style="font-size:.7rem;color:rgba(255,255,255,.4);margin-top:4px;">(Visible on local environment for testing)</div>
      </div>
    <?php endif; ?>
    <div class="otp-icon">
      <?php if (($_SESSION['otp_purpose'] ?? '') === 'admin_login'): ?>
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#C8860A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      <?php else: ?>
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#C8860A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <?php endif; ?>
    </div>
    <div class="otp-title"><?= ($_SESSION['otp_purpose'] ?? '') === 'admin_login' ? 'Admin Two-Factor Authentication' : 'Check your email' ?></div>
    <div class="otp-sub">
      <?php if (($_SESSION['otp_purpose'] ?? '') === 'admin_login'): ?>
        Please enter the security verification code sent to your admin email address:<br>
      <?php else: ?>
        We sent a 6-digit OTP to<br>
      <?php endif; ?>
      <strong style="color:rgba(255,255,255,.7)"><?= e($_SESSION['otp_email'] ?? '') ?></strong><br><br>
      Expires in <span id="timer">10:00</span>
    </div>
    <form method="POST" action="<?= url('/auth/otp/verify') ?>" id="otpForm">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="otp-inputs">
        <?php for($i=0;$i<6;$i++): ?>
          <input class="otp-input" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" required autocomplete="off">
        <?php endfor; ?>
      </div>
      <input type="hidden" name="otp" id="otpHidden">
      <button type="submit" class="btn-verify" id="verifyBtn">Verify & Proceed</button>
    </form>
    <div style="margin-top:20px;font-size:.82rem;">
      <span id="resendContainer" style="color:rgba(255,255,255,.4);">
        Didn't receive it? <span id="resendWait">Resend in <span id="resendSecs">60</span>s</span>
        <a id="resendBtn" href="<?= url('/auth/otp/resend') ?>" style="display:none;color:#C8860A;text-decoration:none;font-weight:600;">Resend OTP</a>
      </span>
      <div style="margin-top:14px;">
        <a href="<?= url('/auth/register') ?>" style="color:rgba(255,255,255,.4);text-decoration:none;font-size:.78rem;transition:color .2s;">Mistyped your email? Re-enter</a>
      </div>
      <div style="margin-top:16px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.06);font-size:0.75rem;">
        <a href="<?= url('/privacy') ?>" style="color:rgba(255,255,255,0.4);text-decoration:none;">Privacy Policy</a> &bull;
        <a href="<?= url('/terms') ?>" style="color:rgba(255,255,255,0.4);text-decoration:none;">Terms &amp; Conditions</a>
      </div>
    </div>
  </div>
</div>
<script>
const inputs = document.querySelectorAll('.otp-input');
inputs.forEach((inp, i) => {
  inp.addEventListener('input', e => {
    if(e.target.value && i < 5) inputs[i+1].focus();
    syncOtp();
  });
  inp.addEventListener('keydown', e => {
    if(e.key==='Backspace' && !e.target.value && i > 0) inputs[i-1].focus();
  });
  inp.addEventListener('paste', e => {
    e.preventDefault();
    const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'');
    paste.split('').forEach((ch,j) => { if(inputs[j]) inputs[j].value=ch; });
    syncOtp();
    if(inputs[5]) inputs[5].focus();
  });
});
function syncOtp(){ document.getElementById('otpHidden').value = [...inputs].map(i=>i.value).join(''); }

// Double-submit protection & sync
document.getElementById('otpForm').addEventListener('submit', function(e) {
  syncOtp();
  const btn = document.getElementById('verifyBtn');
  btn.disabled = true;
  btn.textContent = 'Verifying...';
});

// Countdown timer for expiry
let secs = <?= (int)env('OTP_EXPIRY_MINUTES',10) ?> * 60;
const t = setInterval(() => {
  secs--;
  const m = String(Math.floor(secs/60)).padStart(2,'0');
  const s = String(secs%60).padStart(2,'0');
  document.getElementById('timer').textContent = m+':'+s;
  if(secs <= 0){ clearInterval(t); document.getElementById('timer').textContent='Expired'; }
}, 1000);

// Client-side rate-limit cooldown for Resend button (60s)
let resendSecs = 60;
const resendWait = document.getElementById('resendWait');
const resendBtn = document.getElementById('resendBtn');
const resendSecsEl = document.getElementById('resendSecs');

const resendTimer = setInterval(() => {
  resendSecs--;
  if (resendSecsEl) resendSecsEl.textContent = resendSecs;
  if (resendSecs <= 0) {
    clearInterval(resendTimer);
    if (resendWait) resendWait.style.display = 'none';
    if (resendBtn) resendBtn.style.display = 'inline';
  }
}, 1000);
</script>
</body>
</html>
