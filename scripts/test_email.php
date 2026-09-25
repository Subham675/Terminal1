<?php
/**
 * Terminal 1 — SMTP Email Diagnostics Script
 * Usage:
 *   php scripts/test_email.php [recipient@example.com]
 */

if (php_sapi_name() !== 'cli') {
    die("CLI only.\n");
}

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/app/config/config.php';
require_once APP_ROOT . '/app/config/Mailer.php';

$recipient = $argv[1] ?? env('MAIL_USERNAME');

echo "=========================================\n";
echo "   Terminal 1 — SMTP Email Test          \n";
echo "=========================================\n\n";

echo "SMTP Host:     " . env('MAIL_HOST', 'smtp.gmail.com') . "\n";
echo "SMTP Port:     " . env('MAIL_PORT', 587) . "\n";
echo "SMTP User:     " . env('MAIL_USERNAME') . "\n";
echo "From Address:  " . env('MAIL_FROM_ADDRESS') . "\n";
echo "Sending to:    " . $recipient . "\n\n";

if (empty(env('MAIL_USERNAME')) || env('MAIL_USERNAME') === 'your@gmail.com') {
    echo "❌ Error: MAIL_USERNAME is not configured in .env.\n";
    exit(1);
}

if (empty(env('MAIL_PASSWORD')) || env('MAIL_PASSWORD') === 'your_app_password') {
    echo "❌ Error: MAIL_PASSWORD is not configured in .env (16-character Google App Password needed).\n";
    exit(1);
}

echo "Attempting to send test OTP email via PHPMailer...\n";

$testOtp = (string)random_int(100000, 999999);
$ok = Mailer::sendOtp($recipient, 'Test User', $testOtp, 'google_verify');

if ($ok) {
    echo "✅ SUCCESS! Test OTP email sent successfully.\n";
    echo "Check your inbox (and spam folder) for OTP code: {$testOtp}\n";
} else {
    echo "❌ Failed to send email. Check your SMTP credentials in .env.\n";
    echo "Note: If using Gmail, you MUST use a 16-character Google 'App Password', not your standard account password.\n";
}
