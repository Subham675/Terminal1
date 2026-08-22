<?php
function loadEnv(string $path): void {
    if(!file_exists($path)) throw new RuntimeException(".env not found at: $path");
    foreach(file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){
        if(str_starts_with(trim($line),'#') || !str_contains($line,'=')) continue;
        [$k,$v] = explode('=',$line,2);
        $k=trim($k); $v=trim($v," \t\n\r\0\x0B\"'");
        $_ENV[$k]=$v; putenv("$k=$v");
    }
}
loadEnv(dirname(__DIR__,2).'/.env');
function env(string $k, mixed $d=null): mixed { return $_ENV[$k] ?? getenv($k) ?: $d; }
if(session_status()===PHP_SESSION_NONE){
    ini_set('session.cookie_httponly','1');
    ini_set('session.cookie_secure', env('APP_ENV')==='production'?'1':'0');
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
function sanitize(string $s): string { return trim(strip_tags($s)); }
function flash(string $k, string $msg='', string $type='success'): ?array {
    if($msg){ $_SESSION['flash'][$k]=['message'=>$msg,'type'=>$type]; return null; }
    $f=$_SESSION['flash'][$k]??null; unset($_SESSION['flash'][$k]); return $f;
}
function authUser(): ?array  { return $_SESSION['user']??null; }
function isLoggedIn(): bool  { return isset($_SESSION['user']); }
function isAdmin(): bool     { return ($_SESSION['user']['role']??'')==='admin'; }
function requireLogin(): void{ if(!isLoggedIn()){ header('Location: /auth/login'); exit; } }
function requireAdmin(): void{ if(!isAdmin()){ http_response_code(403); die('403 Forbidden'); } }
function redirect(string $u): void { header("Location: $u"); exit; }
