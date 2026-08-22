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
  <div class="box-header"><h1>Create Account</h1><p>TERMINAL 1 — THE STARTUP CANTEEN</p></div>
  <div class="box-body">
    <?php $f=flash('register'); if($f): ?>
      <div class="flash flash-<?= $f['type'] ?>"><?= e($f['message']) ?></div>
    <?php endif; ?>
    <form method="POST" action="/auth/register">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
      <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" required></div>
      <div class="form-group"><label>Password (min 8 chars)</label><input type="password" name="password" class="form-control" required minlength="8"></div>
      <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm_password" class="form-control" required></div>
      <button type="submit" class="btn-submit">Create Account & Verify Email</button>
    </form>
  </div>
  <div class="footer-link">Already have an account? <a class="link" href="/auth/login">Sign in</a></div>
</div>
</body>
</html>
