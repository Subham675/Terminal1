<?php
class AuthController {
    // ── Show login page ──
    public static function showLogin(): void {
        if(isLoggedIn()) redirect(isAdmin() ? '/admin' : '/');
        require APP_ROOT.'/app/views/auth/login.php';
    }

    // ── Email/password login ──
    public static function login(): void {
        verifyCsrf();
        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if(!$email || !$password){ flash('login','Email and password are required.','error'); redirect('/auth/login'); }
        $user = User::findByEmail($email);
        if(!$user || !password_verify($password, $user['password'] ?? '')){
            flash('login','Invalid credentials.','error'); redirect('/auth/login');
        }
        if(!$user['is_verified']){ flash('login','Please verify your account.','error'); redirect('/auth/login'); }
        self::loginSession($user);
        redirect(isAdmin() ? '/admin' : '/');
    }

    // ── Show register ──
    public static function showRegister(): void {
        require APP_ROOT.'/app/views/auth/register.php';
    }

    // ── Register ──
    public static function register(): void {
        verifyCsrf();
        $name     = sanitize($_POST['name'] ?? '');
        $email    = filter_var(sanitize($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        if(!$name||!$email||!$password){ flash('register','All fields required.','error'); redirect('/auth/register'); }
        if(strlen($password)<8){ flash('register','Password must be at least 8 characters.','error'); redirect('/auth/register'); }
        if($password!==$confirm){ flash('register','Passwords do not match.','error'); redirect('/auth/register'); }
        if(User::findByEmail($email)){ flash('register','Email already registered.','error'); redirect('/auth/register'); }
        $userId = User::create(['name'=>$name,'email'=>$email,'password'=>password_hash($password,PASSWORD_DEFAULT),'is_verified'=>false]);
        $otp = OtpModel::generate($email, $userId, 'register');
        Mailer::sendOtp($email, $name, $otp, 'register');
        $_SESSION['otp_email']   = $email;
        $_SESSION['otp_purpose'] = 'register';
        redirect('/auth/otp');
    }

    // ── Google OAuth: redirect ──
    public static function googleRedirect(): void {
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        $params = http_build_query([
            'client_id'     => env('GOOGLE_CLIENT_ID'),
            'redirect_uri'  => env('GOOGLE_REDIRECT_URI'),
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
        if(!OtpModel::verify($email, $code, $purpose)){
            flash('otp','Invalid or expired OTP. Please try again.','error'); redirect('/auth/otp');
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

        unset($_SESSION['otp_email'],$_SESSION['otp_purpose'],$_SESSION['oauth_state']);
        self::loginSession($user);
        redirect(isAdmin() ? '/admin' : '/');
    }

    // ── Resend OTP ──
    public static function resendOtp(): void {
        $email   = $_SESSION['otp_email'] ?? '';
        $purpose = $_SESSION['otp_purpose'] ?? 'google_verify';
        if(!$email){ redirect('/auth/login'); }
        $user = User::findByEmail($email);
        $otp  = OtpModel::generate($email, $user['id']??null, $purpose);
        Mailer::sendOtp($email, $user['name']??'User', $otp, $purpose);
        flash('otp','A new OTP has been sent to your email.','success');
        redirect('/auth/otp');
    }

    // ── Logout ──
    public static function logout(): void {
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
    }

    private static function googleTokenExchange(string $code): array {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch,[
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_POST=>true,
            CURLOPT_POSTFIELDS=>http_build_query([
                'code'=>$code,'client_id'=>env('GOOGLE_CLIENT_ID'),
                'client_secret'=>env('GOOGLE_CLIENT_SECRET'),
                'redirect_uri'=>env('GOOGLE_REDIRECT_URI'),
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
