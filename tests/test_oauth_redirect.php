<?php
$_SERVER['HTTP_HOST'] = 'zack-nonfebrile-fran.ngrok-free.dev';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
$_SERVER['SCRIPT_NAME'] = '/terminal1/public/index.php';
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$uri = AuthController::getGoogleRedirectUri();
echo "Generated URI: " . $uri . "\n";
assert($uri === 'https://zack-nonfebrile-fran.ngrok-free.dev/terminal1/public/auth/google/callback');

$_SERVER['HTTP_HOST'] = 'localhost';
unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
$localUri = AuthController::getGoogleRedirectUri();
echo "Local URI: " . $localUri . "\n";
assert($localUri === 'http://localhost/terminal1/public/auth/google/callback');

echo "All OAuth redirect URI tests passed!\n";
