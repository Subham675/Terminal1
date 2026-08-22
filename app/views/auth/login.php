<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login — Terminal 1</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:#0F0E0B;font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
    .box{width:100%;max-width:420px;background:#1A1814;border:1px solid rgba(200,134,10,.2);border-radius:8px;overflow:hidden}
    .box-header{background:#C8860A;padding:28px;text-align:center}
    .box-header h1{font-family:'Playfair Display',serif;color:#fff;font-size:1.8rem;letter-spacing:1px}
    .box-header p{color:rgba(255,255,255,.7);font-size:.75rem;letter-spacing:2px;margin-top:4px}
    .box-body{padding:32px}
    .form-group{margin-bottom:18px}
    .form-group label{display:block;font-size:.7rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:6px}
    .form-control{width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;padding:11px 14px;border-radius:4px;font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:#C8860A}
    .btn-submit{width:100%;background:#C8860A;border:none;color:#fff;padding:13px;border-radius:4px;font-family:'DM Sans',sans-serif;font-size:.85rem;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;transition:background .2s;margin-top:6px}
    .btn-submit:hover{background:#E8A820}
    .google-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.8);padding:12px;border-radius:4px;font-family:'DM Sans',sans-serif;font-size:.85rem;cursor:pointer;text-decoration:none;margin-top:14px;transition:all .2s}
    .google-btn:hover{border-color:rgba(200,134,10,.4);background:rgba(200,134,10,.08)}
    .divider{display:flex;align-items:center;gap:12px;margin:18px 0;color:rgba(255,255,255,.2);font-size:.75rem}
    .divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.08)}
    .link{color:#C8860A;text-decoration:none;font-size:.85rem}
    .flash{padding:10px 14px;border-radius:4px;margin-bottom:16px;font-size:.83rem}
    .flash-error{background:rgba(207,34,46,.15);border:1px solid rgba(207,34,46,.3);color:#cf222e}
    .flash-success{background:rgba(45,164,78,.15);border:1px solid rgba(45,164,78,.3);color:#2da44e}
    .footer-link{text-align:center;padding:0 32px 24px;color:rgba(255,255,255,.3);font-size:.82rem}
  </style>
</head>
<body>
<div class="box">
  <div class="box-header">
    <h1>Terminal 1</h1>
    <p>THE STARTUP CANTEEN — ADMIN & USER LOGIN</p>
  </div>
  <div class="box-body">
    <?php $f=flash('login'); if($f): ?>
      <div class="flash flash-<?= $f['type'] ?>"><?= e($f['message']) ?></div>
    <?php endif; ?>
    <form method="POST" action="/auth/login">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-submit">Sign In</button>
    </form>
    <div class="divider">or continue with</div>
    <a class="google-btn" href="/auth/google">
      <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
      Sign in with Google (OTP Verified)
    </a>
  </div>
  <div class="footer-link">
    No account? <a class="link" href="/auth/register">Create one</a>
  </div>
</div>
</body>
</html>
