<?php
function loadEnv(string $path): void {
    if(!file_exists($path)) throw new RuntimeException(".env not found at: $path");
    foreach(file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){
        $trimmed = trim($line);
        if(str_starts_with($trimmed,'#') || !str_contains($trimmed,'=')) continue;
        [$k,$v] = explode('=',$trimmed,2);
        $k=trim($k);
        $v=trim($v);
        // If not quoted, strip trailing inline comments
        if (!str_starts_with($v, '"') && !str_starts_with($v, "'") && str_contains($v, '#')) {
            $v = explode('#', $v, 2)[0];
        }
        $v=trim($v," \t\n\r\0\x0B\"'");
        $_ENV[$k]=$v; putenv("$k=$v");
    }
}
loadEnv(dirname(__DIR__,2).'/.env');
function env(string $k, mixed $d=null): mixed { return $_ENV[$k] ?? getenv($k) ?: $d; }
date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Kolkata'));
if(session_status()===PHP_SESSION_NONE){
    session_cache_limiter('');
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
               (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    ini_set('session.cookie_secure', (env('APP_ENV')==='production' && $isHttps) ? '1' : '0');
    ini_set('session.cookie_samesite','Lax');
    ini_set('session.use_strict_mode','1');
    session_start();
}
if(env('APP_ENV')==='production'){ ini_set('display_errors','0'); error_reporting(0); }
else { ini_set('display_errors','1'); error_reporting(E_ALL); }
function csrfToken(): string {
    if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verifyCsrf(): void {
    $t=$_POST['csrf_token']??$_SERVER['HTTP_X_CSRF_TOKEN']??'';
    if(!hash_equals($_SESSION['csrf_token']??'',$t)){ http_response_code(419); die(json_encode(['error'=>'CSRF mismatch'])); }
}
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES|ENT_HTML5,'UTF-8'); }
function url(string $path = ''): string {
    $path = ltrim($path, '/');
    static $base = null;
    if ($base === null) {
        $scriptDir = str_replace('\\', '/', rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\'));
        $base = ($scriptDir !== '' && $scriptDir !== '/') ? $scriptDir : '';
    }
    return ($base ? $base . '/' : '/') . $path;
}
function asset(string $path): string {
    return url($path);
}
function sanitize(string $s): string {
    $s = (string)preg_replace('@<script[^>]*?>.*?</script>@si', '', $s);
    return trim(strip_tags($s));
}
function flash(string $k, string $msg='', string $type='success'): ?array {
    if($msg){ $_SESSION['flash'][$k]=['message'=>$msg,'type'=>$type]; return null; }
    $f=$_SESSION['flash'][$k]??null; unset($_SESSION['flash'][$k]); return $f;
}
function authUser(): ?array  { return isLoggedIn() ? ($_SESSION['user'] ?? null) : null; }
function isLoggedIn(): bool  {
    if (!isset($_SESSION['user']['id'])) return false;
    // Once-per-request DB check: verify user still exists (prevents stale-session FK errors)
    static $verified = null;
    if ($verified === null) {
        $verified = (bool) User::findById((int)$_SESSION['user']['id']);
        if (!$verified) {
            unset($_SESSION['user'], $_SESSION['user_bookings']);
        }
    }
    return $verified;
}
function isAdmin(): bool     { return ($_SESSION['user']['role']??'')==='admin'; }
function requireLogin(): void{ if(!isLoggedIn()){ redirect('/auth/login'); } }
function requireAdmin(): void{
    if(!isAdmin()){ http_response_code(403); die('403 Forbidden'); }
    // 30-minute inactivity timeout for admin session
    $timeout = 1800; // 30 minutes
    if(isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > $timeout)){
        unset($_SESSION['user'], $_SESSION['admin_last_activity']);
        flash('login', 'Admin session expired due to 30 minutes of inactivity. Please log in again.', 'error');
        redirect('/auth/login');
    }
    $_SESSION['admin_last_activity'] = time();
}
function redirect(string $u): void {
    if (!str_starts_with($u, 'http://') && !str_starts_with($u, 'https://')) {
        $u = url($u);
    }
    header("Location: $u");
    exit;
}

function auditLog(string $action, string $details = ''): void {
    $user = authUser();
    $userEmail = $user['email'] ?? 'guest';
    $userId = $user['id'] ?? 0;
    $userRole = $user['role'] ?? 'guest';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $timestamp = date('Y-m-d H:i:s');
    $logDir = dirname(__DIR__, 2) . '/storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $entry = sprintf("[%s] USER_ID:%s ROLE:%s EMAIL:%s IP:%s ACTION:%s DETAILS:%s\n", $timestamp, $userId, $userRole, $userEmail, $ip, $action, $details);
    @file_put_contents($logDir . '/audit.log', $entry, FILE_APPEND | LOCK_EX);
}

/**
 * Server-rendered security error page for unauthorized access, IDOR, or URL ID tampering.
 * Renders directly from the server — cannot be bypassed by client-side scripts.
 */
function renderSecurityError(int $code, string $title, string $message): string {
    http_response_code($code);
    return '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>' . e($title) . ' | Terminal 1</title>
  <link rel="icon" type="image/svg+xml" href="' . asset('favicon.svg') . '">
  <link rel="alternate icon" href="' . asset('favicon.ico') . '">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <style>
    body { margin:0; min-height:100vh; background:#0B0A08; color:#fff; font-family:"DM Sans",sans-serif; display:flex; align-items:center; justify-content:center; padding:20px; box-sizing:border-box; }
    .err-card { background:#14120E; border:1px solid rgba(207,34,46,.35); border-radius:4px; max-width:540px; width:100%; padding:44px 34px; text-align:center; box-shadow:0 24px 48px rgba(0,0,0,.7); }
    .err-icon { width:60px; height:60px; border-radius:2px; background:rgba(207,34,46,.15); color:#cf222e; display:inline-flex; align-items:center; justify-content:center; margin-bottom:20px; border:1px solid rgba(207,34,46,.35); }
    .err-code { font-size:0.75rem; letter-spacing:3px; text-transform:uppercase; color:#cf222e; font-weight:700; margin-bottom:8px; }
    h1 { font-family:"Playfair Display",serif; font-size:1.8rem; color:#fff; margin:0 0 16px; line-height:1.2; }
    p { color:rgba(255,255,255,.65); line-height:1.6; font-size:0.95rem; margin:0 0 28px; }
    .err-actions { display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }
    .btn { display:inline-flex; align-items:center; gap:8px; padding:12px 24px; border-radius:2px; font-size:0.85rem; letter-spacing:1px; text-transform:uppercase; text-decoration:none; font-weight:600; transition:all .2s; }
    .btn-gold { background:#C8860A; color:#fff; }
    .btn-gold:hover { background:#E8A820; }
    .btn-ghost { background:transparent; border:1px solid rgba(255,255,255,.2); color:rgba(255,255,255,.8); }
    .btn-ghost:hover { border-color:#fff; color:#fff; }
    .security-note { font-size:0.75rem; color:rgba(255,255,255,.3); margin-top:28px; border-top:1px solid rgba(255,255,255,.08); padding-top:16px; }
  </style>
</head>
<body>
  <div class="err-card">
    <div class="err-icon">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>
    <div class="err-code">HTTP ' . $code . ' Security Policy Violation</div>
    <h1>' . e($title) . '</h1>
    <p>' . e($message) . '</p>
    <div class="err-actions">
      <a href="' . url('/') . '" class="btn btn-gold">Return to Home</a>
      <a href="' . url('/my-bookings') . '" class="btn btn-ghost">My Bookings</a>
    </div>
    <div class="security-note">
      Terminal 1 Security Engine • Incident timestamp: ' . date('Y-m-d H:i:s') . ' • IP: ' . e($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '
    </div>
  </div>
</body>
</html>';
}

/**
 * Strict Server-Side Ownership & IDOR Protection:
 * Verifies that the requested booking/order belongs to the authenticated user,
 * or belongs to the current session (for guest reservations), or matches a cryptographically
 * secure tracking token. If a user attempts to change an ID in the URL to another user\'s ID,
 * the server halts immediately with an HTTP 403 Forbidden security error.
 */
function verifyBookingOwnership(int $bookingId, ?string $token = null): array {
    $booking = Booking::findById($bookingId);
    $isJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
           || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/payments/')
           || str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/track/');

    if (!$booking) {
        http_response_code(404);
        if ($isJson) {
            die(json_encode(['success' => false, 'error' => 'Order/Booking not found.']));
        }
        die(renderSecurityError(404, 'Order Not Found', 'The requested order or reservation does not exist.'));
    }

    // 1. System administrators have authorized access
    if (isAdmin()) {
        return $booking;
    }

    $currentUser = authUser();

    // 2. If user is logged in, verify they are the actual owner of this booking
    if ($currentUser && !empty($booking['user_id']) && (int)$booking['user_id'] === (int)$currentUser['id']) {
        return $booking;
    }

    // 3. If guest / session booking created in this specific visitor session
    if (isset($_SESSION['user_bookings'][$bookingId])) {
        $sessionToken = $_SESSION['user_bookings'][$bookingId];
        if ($token && !hash_equals($sessionToken, $token)) {
            // Tampered token - do not grant access
        } else {
            return $booking;
        }
    }

    // 4. If a valid cryptographically secure tracking token was passed and matches DB
    if (!empty($token) && !empty($booking['tracking_token']) && hash_equals($booking['tracking_token'], $token)) {
        return $booking;
    }

    // ── ACCESS DENIED: Server-Side IDOR & Tampering Block ──
    $culprit = $currentUser ? "User #{$currentUser['id']} ({$currentUser['email']})" : "Guest";
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $requestedUri = $_SERVER['REQUEST_URI'] ?? '';
    auditLog('IDOR_TAMPER_ATTEMPT', "{$culprit} from IP {$ip} attempted unauthorized access/tampering on Booking #{$bookingId}. Requested URI: {$requestedUri}");

    http_response_code(403);
    if ($isJson) {
        die(json_encode([
            'success' => false,
            'error'   => '403 Forbidden: Access denied. You do not have permission to view or modify this order. ID tampering detected.',
            'code'    => 'SECURITY_IDOR_VIOLATION'
        ]));
    }

    die(renderSecurityError(
        403,
        'Access Denied: Security Violation',
        "Server-Side Security Check Failed: You do not have permission to view or manipulate Order #{$bookingId}. Attempting to change or forge IDs in the URL or request parameters is strictly forbidden. This incident has been recorded for security audit."
    ));
}

function sendSecurityHeaders(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://checkout.razorpay.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: blob: https:; connect-src 'self' https://*.razorpay.com; frame-src 'self' https://api.razorpay.com https://*.razorpay.com; object-src 'none'; base-uri 'self';");
}
