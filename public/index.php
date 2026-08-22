<?php
define('APP_ROOT', dirname(__DIR__));

spl_autoload_register(function(string $class): void {
    $dirs = [
        APP_ROOT.'/app/config/',
        APP_ROOT.'/app/controllers/',
        APP_ROOT.'/app/models/',
        APP_ROOT.'/app/middleware/',
    ];
    foreach($dirs as $dir){
        $file = $dir.$class.'.php';
        if(file_exists($file)){ require_once $file; return; }
    }
});

require_once APP_ROOT.'/app/config/config.php';

$uri    = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        '/'                    => fn() => require APP_ROOT.'/app/views/home.php',
        '/auth/login'          => [AuthController::class, 'showLogin'],
        '/auth/register'       => [AuthController::class, 'showRegister'],
        '/auth/google'         => [AuthController::class, 'googleRedirect'],
        '/auth/google/callback'=> [AuthController::class, 'googleCallback'],
        '/auth/otp'            => [AuthController::class, 'showOtp'],
        '/auth/otp/resend'     => [AuthController::class, 'resendOtp'],
        '/auth/logout'         => [AuthController::class, 'logout'],
        '/admin'               => [AdminController::class, 'dashboard'],
        '/admin/bookings'      => [AdminController::class, 'bookings'],
        '/admin/users'         => [AdminController::class, 'users'],
        '/admin/menu'          => [AdminController::class, 'menu'],
    ],
    'POST' => [
        '/auth/login'              => [AuthController::class, 'login'],
        '/auth/register'           => [AuthController::class, 'register'],
        '/auth/otp/verify'         => [AuthController::class, 'verifyOtp'],
        '/bookings'                => [BookingController::class, 'store'],
        '/admin/bookings/status'   => [AdminController::class, 'updateBookingStatus'],
        '/admin/bookings/delete'   => [AdminController::class, 'deleteBooking'],
        '/admin/users/role'        => [AdminController::class, 'updateUserRole'],
        '/admin/users/delete'      => [AdminController::class, 'deleteUser'],
        '/admin/menu/create'       => [AdminController::class, 'createMenuItem'],
        '/admin/menu/update'       => [AdminController::class, 'updateMenuItem'],
        '/admin/menu/delete'       => [AdminController::class, 'deleteMenuItem'],
    ],
];

$handler = $routes[$method][$uri] ?? null;
if($handler){
    is_array($handler) ? $handler[0]::$handler[1]() : $handler();
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html><body style="background:#0F0E0B;color:#C8860A;font-family:sans-serif;text-align:center;padding:100px"><h1 style="font-size:4rem">404</h1><p>Page not found. <a style="color:#E8A820" href="/">← Go home</a></p></body></html>';
}
