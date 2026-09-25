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
sendSecurityHeaders();

$rawUri = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
$scriptDir = str_replace('\\', '/', rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\'));
$uri = $rawUri;
if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($rawUri, $scriptDir)) {
    $uri = substr($rawUri, strlen($scriptDir)) ?: '/';
} elseif (str_starts_with($rawUri, '/terminal1/public')) {
    $uri = substr($rawUri, strlen('/terminal1/public')) ?: '/';
} elseif (str_starts_with($rawUri, '/terminal1')) {
    $uri = substr($rawUri, strlen('/terminal1')) ?: '/';
}
$uri = rtrim($uri, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$routes = [
    'GET' => [
        '/'                    => fn() => require APP_ROOT.'/app/views/home.php',
        '/my-bookings'         => [BookingController::class, 'myBookings'],
        '/bookings/view'       => [BookingController::class, 'viewBooking'],
        '/order'               => [BookingController::class, 'viewBooking'],
        '/auth/login'          => [AuthController::class, 'showLogin'],
        '/auth/register'       => [AuthController::class, 'showRegister'],
        '/auth/google'         => [AuthController::class, 'googleRedirect'],
        '/auth/google/callback'=> [AuthController::class, 'googleCallback'],
        '/auth/otp'            => [AuthController::class, 'showOtp'],
        '/auth/otp/resend'     => [AuthController::class, 'resendOtp'],
        '/admin'               => [AdminController::class, 'dashboard'],
        '/admin/bookings'      => [AdminController::class, 'bookings'],
        '/admin/users'         => [AdminController::class, 'users'],
        '/admin/menu'          => [AdminController::class, 'menu'],
        '/track/stream'        => [TrackingController::class, 'customerStream'],
        '/admin/track/stream'  => [TrackingController::class, 'adminStream'],
    ],
    'POST' => [
        '/auth/login'              => [AuthController::class, 'login'],
        '/auth/register'           => [AuthController::class, 'register'],
        '/auth/otp/verify'         => [AuthController::class, 'verifyOtp'],
        '/auth/logout'             => [AuthController::class, 'logout'],
        '/bookings'                => [BookingController::class, 'store'],
        '/payments/create-order'   => [PaymentController::class, 'createOrder'],
        '/payments/verify'         => [PaymentController::class, 'verify'],
        '/payments/webhook'        => [PaymentController::class, 'webhook'],
        '/admin/bookings/status'   => [AdminController::class, 'updateBookingStatus'],
        '/admin/bookings/delete'   => [AdminController::class, 'deleteBooking'],
        '/admin/bookings/refund'   => [PaymentController::class, 'adminRefund'],
        '/admin/users/role'        => [AdminController::class, 'updateUserRole'],
        '/admin/users/delete'      => [AdminController::class, 'deleteUser'],
        '/admin/menu/create'       => [AdminController::class, 'createMenuItem'],
        '/admin/menu/update'       => [AdminController::class, 'updateMenuItem'],
        '/admin/menu/delete'       => [AdminController::class, 'deleteMenuItem'],
    ],
];

// ── Server-Side URL ID Tampering & IDOR Guard ──
// If a user manually specifies or changes an order/booking ID (?id=.. or ?booking_id=..) in the URL
// on any customer-facing page, the server strictly validates that the visitor owns that order.
// If an attacker changes id=2 to id=3 (another user's order), verifyBookingOwnership immediately
// terminates execution with HTTP 403 Forbidden and logs the security violation!
$urlQueryId = (int)($_GET['booking_id'] ?? ($_GET['id'] ?? 0));
if ($urlQueryId > 0 && !str_starts_with($uri, '/admin') && !in_array($uri, ['/track/stream', '/bookings/view', '/order'])) {
    $tokenParam = $_GET['tracking_token'] ?? ($_GET['token'] ?? null);
    verifyBookingOwnership($urlQueryId, $tokenParam);
}

$handler = $routes[$method][$uri] ?? null;
if($handler){
    if(is_array($handler)){
        [$class, $action] = $handler;
        $class::$action();
    } else {
        $handler();
    }
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html><body style="background:#0F0E0B;color:#C8860A;font-family:sans-serif;text-align:center;padding:100px"><h1 style="font-size:4rem">404</h1><p>Page not found. <a style="color:#E8A820" href="/">← Go home</a></p></body></html>';
}
