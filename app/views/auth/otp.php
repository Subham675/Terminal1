<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>OTP Verification — Terminal 1</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:#0F0E0B;font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
    .box{width:100%;max-width:400px;background:#1A1814;border:1px solid rgba(200,134,10,.2);border-radius:8px;overflow:hidden;text-align:center}
    .box-header{background:#C8860A;padding:28px}
    .box-header h1{font-family:'Playfair Display',serif;color:#fff;font-size:1.6rem}
    .box-body{padding:36px 32px}
    .otp-icon{font-size:3rem;margin-bottom:16px}
    .otp-title{font-size:1.1rem;color:#fff;font-weight:600;margin-bottom:8px}
    .otp-sub{color:rgba(255,255,255,.45);font-size:.85rem;line-height:1.6;margin-bottom:28px}
    .otp-inputs{display:flex;gap:10px;justify-content:center;margin-bottom:24px}
    .otp-input{width:48px;height:56px;background:rgba(255,255,255,.06);border:2px solid rgba(255,255,255,.12);color:#fff;font-size:1.4rem;font-weight:700;text-align:center;border-radius:6px;outline:none;transition:border-color .2s;font-family:'DM Sans',sans-serif}
    .otp-input:focus{border-color:#C8860A;background:rgba(200,134,10,.08)}
    input[name="otp"]{display:none}
    .btn-verify{width:100%;background:#C8860A;border:none;color:#fff;padding:13px;border-radius:4px;font-family:'DM Sans',sans-serif;font-size:.85rem;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer}
    .btn-verify:hover{background:#E8A820}
    .resend-link{color:rgba(255,255,255,.3);font-size:.82rem;margin-top:18px;display:block}
    .resend-link a{color:#C8860A;text-decoration:none}
    .flash{padding:10px 14px;border-radius:4px;margin-bottom:16px;font-size:.83rem}
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
    <div class="otp-icon">🔐</div>
    <div class="otp-title">Check your email</div>
    <div class="otp-sub">
      We sent a 6-digit OTP to<br>
      <strong style="color:rgba(255,255,255,.7)"><?= e($_SESSION['otp_email'] ?? '') ?></strong><br><br>
      Expires in <span id="timer">10:00</span>
    </div>
    <form method="POST" action="/auth/otp/verify">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="otp-inputs">
        <?php for($i=0;$i<6;$i++): ?>
          <input class="otp-input" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <?php endfor; ?>
      </div>
      <input type="hidden" name="otp" id="otpHidden">
      <button type="submit" class="btn-verify" id="verifyBtn">Verify OTP</button>
    </form>
    <span class="resend-link">Didn't receive it? <a href="/auth/otp/resend">Resend OTP</a></span>
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

// Countdown timer
let secs = <?= (int)env('OTP_EXPIRY_MINUTES',10) ?> * 60;
const t = setInterval(() => {
  secs--;
  const m = String(Math.floor(secs/60)).padStart(2,'0');
  const s = String(secs%60).padStart(2,'0');
  document.getElementById('timer').textContent = m+':'+s;
  if(secs <= 0){ clearInterval(t); document.getElementById('timer').textContent='Expired'; }
},1000);
</script>
</body>
</html>
