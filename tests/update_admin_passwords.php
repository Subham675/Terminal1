<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/config/config.php';
require_once APP_ROOT . '/app/config/Database.php';

$hash = password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]);

// 1. Update User #1 (admin@terminal1.in)
Database::query("UPDATE users SET password = ?, role = 'admin', is_verified = 1 WHERE email = 'admin@terminal1.in'", [$hash]);

// 2. Also ensure User #12 (karmakarsubham194@gmail.com) has admin role and password Admin@1234
Database::query("UPDATE users SET password = ?, role = 'admin', is_verified = 1 WHERE email = 'karmakarsubham194@gmail.com'", [$hash]);

echo "Admin accounts updated successfully!\n";
$users = Database::rows("SELECT id, name, email, role FROM users WHERE role = 'admin'");
foreach ($users as $u) {
    echo "ID: {$u['id']} | Email: {$u['email']} | Role: {$u['role']}\n";
}
