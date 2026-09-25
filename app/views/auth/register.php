<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Register — Terminal 1</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:#0F0E0B;font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
    .box{width:100%;max-width:440px;background:#1A1814;border:1px solid rgba(200,134,10,.2);border-radius:8px;overflow:hidden}
    .box-header{background:#C8860A;padding:24px;text-align:center}
    .box-header h1{font-family:'Playfair Display',serif;color:#fff;font-size:1.6rem}
    .box-header p{color:rgba(255,255,255,.7);font-size:.72rem;letter-spacing:2px;margin-top:3px}
    .box-body{padding:30px}
    .form-group{margin-bottom:16px}
    .form-group label{display:block;font-size:.7rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:6px}
    .form-control{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;padding:11px 14px;border-radius:4px;font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:#C8860A}
    .password-wrap{position:relative;display:flex;align-items:center}
    .password-wrap .form-control{padding-right:44px}
    .btn-toggle-pw{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:rgba(255,255,255,.35);cursor:pointer;padding:6px;display:flex;align-items:center;justify-content:center;transition:color .2s;border-radius:4px}
    .btn-toggle-pw:hover,.btn-toggle-pw:focus{color:#E8A820;outline:none}
    .btn-submit{width:100%;background:#C8860A;border:none;color:#fff;padding:13px;border-radius:4px;font-family:'DM Sans',sans-serif;font-size:.85rem;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;margin-top:6px}
    .btn-submit:hover{background:#E8A820}
    .flash{padding:10px 14px;border-radius:4px;margin-bottom:16px;font-size:.83rem}
    .flash-error{background:rgba(207,34,46,.15);border:1px solid rgba(207,34,46,.3);color:#cf222e}
    .footer-link{text-align:center;padding:0 30px 22px;color:rgba(255,255,255,.3);font-size:.82rem}
    .link{color:#C8860A;text-decoration:none}
  </style>
</head>
<body>
<div class="box">
  <div class="box-header"><h1>Create Account</h1><p>TERMINAL 1 — THE RESTAURANT</p></div>
  <div class="box-body">
    <?php $f=flash('register'); if($f): ?>
      <div class="flash flash-<?= $f['type'] ?>"><?= e($f['message']) ?></div>
    <?php endif; ?>
    <form method="POST" action="<?= url('/auth/register') ?>" id="regForm">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" placeholder="Rajib Das" required></div>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" id="regEmail" class="form-control" placeholder="you@example.com" required autocomplete="email">
        <div id="emailFeedback" style="display:none;font-size:.78rem;margin-top:6px;line-height:1.4;"></div>
      </div>
      <div class="form-group">
        <label>Password (min 8 chars)</label>
        <div class="password-wrap">
          <input type="password" name="password" id="regPassword" class="form-control" placeholder="••••••••" required minlength="8">
          <button type="button" class="btn-toggle-pw" onclick="togglePasswordVisibility(this, 'regPassword')" aria-label="Show password" title="Show/hide password">
            <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
            <svg class="icon-eye-off" style="display:none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
              <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
              <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
              <line x1="2" y1="2" x2="22" y2="22"/>
            </svg>
          </button>
        </div>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <div class="password-wrap">
          <input type="password" name="confirm_password" id="regConfirmPassword" class="form-control" placeholder="••••••••" required>
          <button type="button" class="btn-toggle-pw" onclick="togglePasswordVisibility(this, 'regConfirmPassword')" aria-label="Show password" title="Show/hide password">
            <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
            <svg class="icon-eye-off" style="display:none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
              <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
              <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
              <line x1="2" y1="2" x2="22" y2="22"/>
            </svg>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-submit" id="regBtn">Create Account & Verify Email</button>
    </form>
  </div>
  <div class="footer-link">Already have an account? <a class="link" href="<?= url('/auth/login') ?>">Sign in</a></div>
</div>
<script>
function togglePasswordVisibility(btn, inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const eye = btn.querySelector('.icon-eye');
  const eyeOff = btn.querySelector('.icon-eye-off');
  if (input.type === 'password') {
    input.type = 'text';
    eye.style.display = 'none';
    eyeOff.style.display = 'block';
    btn.setAttribute('aria-label', 'Hide password');
  } else {
    input.type = 'password';
    eye.style.display = 'block';
    eyeOff.style.display = 'none';
    btn.setAttribute('aria-label', 'Show password');
  }
}

const regEmail = document.getElementById('regEmail');
const emailFeedback = document.getElementById('emailFeedback');
const typoMap = {
  'gmai.com': 'gmail.com', 'gamil.com': 'gmail.com', 'gmial.com': 'gmail.com',
  'gmaill.com': 'gmail.com', 'gmal.com': 'gmail.com', 'gmail.co': 'gmail.com',
  'yaho.com': 'yahoo.com', 'yahooo.com': 'yahoo.com', 'hotmial.com': 'hotmail.com'
};
const disposableSet = new Set(['mailinator.com','tempmail.com','10minutemail.com','guerrillamail.com','trashmail.com','temp-mail.org']);

function validateEmailInput(val) {
  val = (val || '').trim().toLowerCase();
  if (!val) {
    emailFeedback.style.display = 'none';
    regEmail.style.borderColor = '';
    return { valid: true };
  }

  const parts = val.split('@');
  if (parts.length !== 2 || !parts[1]) {
    emailFeedback.style.display = 'none';
    regEmail.style.borderColor = '';
    return { valid: false, reason: 'incomplete' };
  }

  const user = parts[0];
  const domain = parts[1];

  if (typoMap[domain]) {
    emailFeedback.style.display = 'block';
    emailFeedback.style.color = '#E8A820';
    emailFeedback.innerHTML = '💡 Did you mean <strong>@' + typoMap[domain] + '</strong>?';
    regEmail.style.borderColor = '#E8A820';
    return { valid: false, reason: 'typo' };
  }

  if (disposableSet.has(domain)) {
    emailFeedback.style.display = 'block';
    emailFeedback.style.color = '#cf222e';
    emailFeedback.textContent = '⚠️ Disposable email addresses are not permitted.';
    regEmail.style.borderColor = '#cf222e';
    return { valid: false, reason: 'disposable' };
  }

  if (domain === 'gmail.com' || domain === 'googlemail.com') {
    const rawUser = user.replace(/\./g, '').split('+')[0];
    if (rawUser.length < 6) {
      emailFeedback.style.display = 'block';
      emailFeedback.style.color = '#cf222e';
      emailFeedback.textContent = `⚠️ Gmail username must have at least 6 characters ('${user}' has only ${rawUser.length}).`;
      regEmail.style.borderColor = '#cf222e';
      return { valid: false, reason: 'gmail_too_short' };
    }
    if (user.length > 30) {
      emailFeedback.style.display = 'block';
      emailFeedback.style.color = '#cf222e';
      emailFeedback.textContent = '⚠️ Gmail username cannot exceed 30 characters.';
      regEmail.style.borderColor = '#cf222e';
      return { valid: false, reason: 'gmail_too_long' };
    }
    if (!/^[a-z0-9.]+$/.test(user)) {
      emailFeedback.style.display = 'block';
      emailFeedback.style.color = '#cf222e';
      emailFeedback.textContent = '⚠️ Gmail username can only contain letters, numbers, and periods.';
      regEmail.style.borderColor = '#cf222e';
      return { valid: false, reason: 'gmail_invalid_chars' };
    }
    if (user.startsWith('.') || user.endsWith('.') || user.includes('..')) {
      emailFeedback.style.display = 'block';
      emailFeedback.style.color = '#cf222e';
      emailFeedback.textContent = '⚠️ Gmail username cannot start, end, or have consecutive periods.';
      regEmail.style.borderColor = '#cf222e';
      return { valid: false, reason: 'gmail_dots' };
    }
  }

  emailFeedback.style.display = 'none';
  regEmail.style.borderColor = '#2da44e';
  return { valid: true };
}

regEmail.addEventListener('input', function() {
  validateEmailInput(this.value);
});

regEmail.addEventListener('blur', function() {
  validateEmailInput(this.value);
});

document.getElementById('regForm').addEventListener('submit', function(e) {
  const result = validateEmailInput(regEmail.value);
  if (!result.valid && result.reason !== 'incomplete') {
    e.preventDefault();
    regEmail.focus();
    return;
  }
  const btn = document.getElementById('regBtn');
  btn.disabled = true;
  btn.textContent = 'Creating account...';
});
</script>
</body>
</html>
