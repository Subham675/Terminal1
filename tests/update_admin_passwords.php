<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/config/config.php';
require_once APP_ROOT . '/app/config/Database.php';

$password = $argv[1] ?? getenv('ADMIN_PASSWORD') ?: null;
$targetEmail = $argv[2] ?? getenv('ADMIN_EMAIL') ?: 'admin@terminal1.in';

if (!$password) {
    echo "Usage: php tests/update_admin_passwords.php <password> [email]\n";
    echo "Example: php tests/update_admin_passwords.php SecurePass123! admin@terminal1.in\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

Database::query(
    "UPDATE users SET password = ?, role = 'admin', is_verified = 1 WHERE email = ?",
    [$hash, $targetEmail]
);

echo "Admin account for '{$targetEmail}' updated securely.\n";
