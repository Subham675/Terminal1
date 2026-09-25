<?php
class AuthController {
    // ── Show login page ──
    public static function showLogin(): void {
        if(isLoggedIn()) redirect(isAdmin() ? '/admin' : '/');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $requiresCaptcha = RateLimiter::requiresCaptcha($ip, 'login_ip', 2);
        $captcha = $requiresCaptcha ? RateLimiter::getCaptchaChallenge() : null;
        require APP_ROOT.'/app/views/auth/login.php';
    }

    // ── Email/password login ──
    public static function login(): void {
        verifyCsrf();
        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if(!$email || !$password){
            flash('login','Email and password are required.','error');
            redirect('/auth/login');
        }

        // CAPTCHA check if user/network had recent failed attempts
        if (RateLimiter::requiresCaptcha($ip, 'login_ip', 2) || RateLimiter::requiresCaptcha($email, 'login', 2)) {
            $captchaAns = $_POST['captcha_answer'] ?? null;
            if (!RateLimiter::verifyCaptcha($captchaAns)) {
                RateLimiter::recordAttempt($ip, 'login_ip', false);
                RateLimiter::recordAttempt($email, 'login', false);
                flash('login', 'Security verification challenge failed. Please try again.', 'error');
                redirect('/auth/login');
            }
        }

        // Escalating rate limit checks
        if (RateLimiter::isBlocked($email, 'login', 5, 15)) {
            $mins = RateLimiter::remainingCooldownMinutes($email, 'login', 15);
            flash('login', "Account temporarily locked due to repeated failed attempts. Try again in {$mins} minute(s).", 'error');
            redirect('/auth/login');
        }

        // Secondary IP-based limiter — prevents cross-account credential-stuffing
        if (RateLimiter::isBlocked($ip, 'login_ip', 20, 15)) {
            $mins = RateLimiter::remainingCooldownMinutes($ip, 'login_ip', 15);
            flash('login', "Too many login attempts from this network. Cooldown active for {$mins} minute(s).", 'error');
            redirect('/auth/login');
        }

        $user = User::findByEmail($email);
        if(!$user || !password_verify($password, $user['password'] ?? '')){
            RateLimiter::recordAttempt($email, 'login', false);
            RateLimiter::recordAttempt($ip, 'login_ip', false);
            flash('login','Invalid credentials.','error');
            redirect('/auth/login');
        }

        if(!$user['is_verified']){
            flash('login','Please verify your account.','error');
            redirect('/auth/login');
        }

        // ── 2FA FOR ADMINISTRATORS ──
        if (($user['role'] ?? '') === 'admin') {
            RateLimiter::clearAttempts($email, 'login');
            $otp = OtpModel::generate($email, (int)$user['id'], 'admin_login');
            Mailer::sendOtp($email, $user['name'] ?? 'Admin', $otp, 'admin_login');
            $_SESSION['otp_email'] = $email;
            $_SESSION['otp_purpose'] = 'admin_login';
            $_SESSION['admin_pending_user_id'] = $user['id'];
            if (env('APP_ENV') === 'development') {
                $_SESSION['dev_otp'] = $otp;
            }
            flash('otp', 'Admin 2FA Security: A 6-digit verification code has been emailed to you. Please enter it below.', 'success');
            redirect('/auth/otp');
            return;
        }

        RateLimiter::clearAttempts($email, 'login');
        self::loginSession($user);
        redirect('/');
    }

    // ── Show register ──
    public static function showRegister(): void {
        require APP_ROOT.'/app/views/auth/register.php';
    }

    // ── Register ──
    public static function register(): void {
        verifyCsrf();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (RateLimiter::isBlocked($ip, 'register', 5, 60)) {
            $mins = RateLimiter::remainingCooldownMinutes($ip, 'register', 60);
            flash('register',"Too many registration attempts from this device. Please wait {$mins} minute(s).",'error');
            redirect('/auth/register');
        }

        $name     = sanitize($_POST['name'] ?? '');
        $email    = filter_var(sanitize($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        if(!$name||!$email||!$password){ flash('register','All fields required.','error'); redirect('/auth/register'); }
        if(strlen($password)<8){ flash('register','Password must be at least 8 characters.','error'); redirect('/auth/register'); }
        if($password!==$confirm){ flash('register','Passwords do not match.','error'); redirect('/auth/register'); }

        RateLimiter::recordAttempt($ip, 'register', false);

        if(User::findByEmail($email)){ flash('register','Email already registered.','error'); redirect('/auth/register'); }
        $userId = User::create(['name'=>$name,'email'=>$email,'password'=>password_hash($password,PASSWORD_DEFAULT),'is_verified'=>false]);
        $otp = OtpModel::generate($email, $userId, 'register');
        Mailer::sendOtp($email, $name, $otp, 'register');
        $_SESSION['otp_email']   = $email;
        $_SESSION['otp_purpose'] = 'register';
        if (env('APP_ENV') === 'development') {
            $_SESSION['dev_otp'] = $otp;
        }
        redirect('/auth/otp');
    }

    // ── Google OAuth: redirect ──
    public static function googleRedirect(): void {
        $clientId = env('GOOGLE_CLIENT_ID');
        if (empty($clientId) || str_contains($clientId, 'YOUR_GOOGLE_CLIENT_ID') || str_starts_with($clientId, 'YOUR_')) {
            flash('login', 'Google Sign-In is not configured yet. Please configure your GOOGLE_CLIENT_ID in the .env file, or sign in with your email and password.', 'error');
            redirect('/auth/login');
        }

        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        if (empty($clientSecret) || str_contains($clientSecret, 'YOUR_GOOGLE_CLIENT_SECRET') || str_starts_with($clientSecret, 'YOUR_')) {
            flash('login', 'Google Client ID is set, but GOOGLE_CLIENT_SECRET is missing or still set to default in .env. Please provide your Google Client Secret.', 'error');
            redirect('/auth/login');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;

        $redirectUri = env('GOOGLE_REDIRECT_URI');
        if (empty($redirectUri) || str_contains($redirectUri, 'yourdomain.com')) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $redirectUri = $scheme . '://' . $host . url('/auth/google/callback');
        }

        $params = http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ]);
        redirect('https://accounts.google.com/o/oauth2/v2/auth?'.$params);
    }

    // ── Google OAuth: callback ──
    public static function googleCallback(): void {
        if(($_GET['state']??'') !== ($_SESSION['oauth_state']??'X')){
            flash('login','Invalid OAuth state.','error'); redirect('/auth/login');
        }
        $code = $_GET['code'] ?? '';
        if(!$code){ flash('login','Google login failed.','error'); redirect('/auth/login'); }

        // Exchange code for token
        $tokenRes = self::googleTokenExchange($code);
        if(!isset($tokenRes['access_token'])){ flash('login','Could not get Google token.','error'); redirect('/auth/login'); }

        // Get user info
        $gUser = self::googleUserInfo($tokenRes['access_token']);
        if(!isset($gUser['email'])){ flash('login','Could not get Google profile.','error'); redirect('/auth/login'); }

        // Store pending Google data in session, then send OTP
        $_SESSION['google_pending'] = [
            'google_id' => $gUser['sub'],
            'email'     => $gUser['email'],
            'name'      => $gUser['name'] ?? $gUser['email'],
            'avatar'    => $gUser['picture'] ?? null,
        ];
        $_SESSION['otp_email']   = $gUser['email'];
        $_SESSION['otp_purpose'] = 'google_verify';

        // Find or create user
        $existing = User::findByEmail($gUser['email']) ?? User::findByGoogleId($gUser['sub']);
        if($existing){
            $_SESSION['google_pending']['user_id'] = $existing['id'];
            $otp = OtpModel::generate($gUser['email'], $existing['id'], 'google_verify');
        } else {
            $otp = OtpModel::generate($gUser['email'], null, 'google_verify');
        }

        Mailer::sendOtp($gUser['email'], $gUser['name']??$gUser['email'], $otp, 'google_verify');
        if (env('APP_ENV') === 'development') {
            $_SESSION['dev_otp'] = $otp;
        }
        redirect('/auth/otp');
    }

    // ── Show OTP page ──
    public static function showOtp(): void {
        if(empty($_SESSION['otp_email'])){ redirect('/auth/login'); }
        require APP_ROOT.'/app/views/auth/otp.php';
    }

    // ── Verify OTP ──
    public static function verifyOtp(): void {
        verifyCsrf();
        $email   = $_SESSION['otp_email'] ?? '';
        $purpose = $_SESSION['otp_purpose'] ?? 'google_verify';
        $code    = trim($_POST['otp'] ?? '');
        if(!$email){ redirect('/auth/login'); }

        if (RateLimiter::isBlocked($email, 'otp')) {
            $mins = RateLimiter::remainingCooldownMinutes($email, 'otp');
            flash('otp', "Too many incorrect attempts. Try again in {$mins} minute(s).", 'error');
            redirect('/auth/otp');
        }

        if(!OtpModel::verify($email, $code, $purpose)){
            RateLimiter::recordAttempt($email, 'otp', false);
            flash('otp','Invalid or expired OTP. Please try again.','error');
            redirect('/auth/otp');
        }
        RateLimiter::clearAttempts($email, 'otp');

        // Admin 2FA Login
        if ($purpose === 'admin_login') {
            $pendingId = $_SESSION['admin_pending_user_id'] ?? null;
            if (!$pendingId) {
                flash('login', 'Admin authentication session expired. Please sign in again.', 'error');
                redirect('/auth/login');
            }
            $user = User::findById((int)$pendingId);
            if (!$user || ($user['role'] ?? '') !== 'admin') {
                redirect('/auth/login');
            }
            unset($_SESSION['admin_pending_user_id'], $_SESSION['otp_email'], $_SESSION['otp_purpose']);
            self::loginSession($user);

            $ip    = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            Mailer::sendAdminLoginAlert($user, $ip, $agent);
            auditLog('ADMIN_LOGIN_2FA', "Admin logged in successfully from IP: {$ip}");

            redirect('/admin');
            return;
        }

        if($purpose === 'google_verify'){
            $pending = $_SESSION['google_pending'] ?? [];
            $user = User::findByEmail($email) ?? User::findByGoogleId($pending['google_id']??'');
            if(!$user){
                $uid = User::create(['name'=>$pending['name'],'email'=>$email,'google_id'=>$pending['google_id'],'avatar'=>$pending['avatar'],'is_verified'=>true]);
                $user = User::findById($uid);
            } else {
                User::update($user['id'],['google_id'=>$pending['google_id'],'avatar'=>$pending['avatar']??$user['avatar'],'is_verified'=>true]);
                $user = User::findById($user['id']);
            }
            unset($_SESSION['google_pending']);
        } else {
            // register purpose
            $user = User::findByEmail($email);
            if($user) User::update($user['id'],['is_verified'=>true]);
            $user = User::findByEmail($email);
        }

        unset($_SESSION['otp_email'],$_SESSION['otp_purpose'],$_SESSION['oauth_state'],$_SESSION['dev_otp']);
        self::loginSession($user);
        redirect(isAdmin() ? '/admin' : '/');
    }

    // ── Resend OTP ──
    public static function resendOtp(): void {
        $email   = $_SESSION['otp_email'] ?? '';
        $purpose = $_SESSION['otp_purpose'] ?? 'google_verify';
        if(!$email){ redirect('/auth/login'); }

        if (RateLimiter::isBlocked($email, 'otp_resend', 3, 10)) {
            $mins = RateLimiter::remainingCooldownMinutes($email, 'otp_resend', 10);
            flash('otp', "Too many resend requests. Please wait {$mins} minute(s).", 'error');
            redirect('/auth/otp');
        }
        RateLimiter::recordAttempt($email, 'otp_resend', false);

        $user = User::findByEmail($email);
        $otp  = OtpModel::generate($email, $user['id']??null, $purpose);
        Mailer::sendOtp($email, $user['name']??'User', $otp, $purpose);
        if (env('APP_ENV') === 'development') {
            $_SESSION['dev_otp'] = $otp;
        }
        flash('otp','A new OTP has been sent to your email.','success');
        redirect('/auth/otp');
    }

    // ── Logout (POST-only + CSRF, prevents forced-logout via CSRF) ──
    public static function logout(): void {
        verifyCsrf();
        if (isAdmin()) {
            auditLog('ADMIN_LOGOUT', 'Admin signed out');
        }
        session_destroy();
        redirect('/auth/login');
    }

    // ── Internals ──────────────────────────────────────────
    private static function loginSession(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'avatar'=> $user['avatar'] ?? null,
        ];
        if (($user['role'] ?? '') === 'admin') {
            $_SESSION['admin_last_activity'] = time();
        }
    }

    private static function googleTokenExchange(string $code): array {
        $redirectUri = env('GOOGLE_REDIRECT_URI');
        if (empty($redirectUri) || str_contains($redirectUri, 'yourdomain.com')) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $redirectUri = $scheme . '://' . $host . url('/auth/google/callback');
        }

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch,[
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_POST=>true,
            CURLOPT_POSTFIELDS=>http_build_query([
                'code'=>$code,
                'client_id'=>env('GOOGLE_CLIENT_ID'),
                'client_secret'=>env('GOOGLE_CLIENT_SECRET'),
                'redirect_uri'=>$redirectUri,
                'grant_type'=>'authorization_code',
            ]),
        ]);
        $res = curl_exec($ch); curl_close($ch);
        return json_decode($res,true) ?? [];
    }

    private static function googleUserInfo(string $token): array {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt_array($ch,[
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_HTTPHEADER=>["Authorization: Bearer $token"],
        ]);
        $res = curl_exec($ch); curl_close($ch);
        return json_decode($res,true) ?? [];
    }
}
