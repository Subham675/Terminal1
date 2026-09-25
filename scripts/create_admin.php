<?php
/**
 * Terminal 1 — Secure Admin Account Setup (CLI Only)
 * Usage:
 *   php scripts/create_admin.php
 *   or:
 *   php scripts/create_admin.php "Admin Name" "admin@example.com" "SecurePassword123"
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Forbidden: This script can only be run from the command line.\n");
}

define('APP_ROOT', dirname(__DIR__));

// Autoload core application classes
spl_autoload_register(function (string $class): void {
    $dirs = [
        APP_ROOT . '/app/config/',
        APP_ROOT . '/app/controllers/',
        APP_ROOT . '/app/models/',
        APP_ROOT . '/app/middleware/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once APP_ROOT . '/app/config/config.php';

echo "=========================================\n";
echo "   Terminal 1 — Create Admin Account     \n";
echo "=========================================\n\n";

// Support both CLI arguments and interactive input
$name     = $argv[1] ?? null;
$email    = $argv[2] ?? null;
$password = $argv[3] ?? null;

if (!$name) {
    echo "Enter Admin Full Name: ";
    $name = trim(fgets(STDIN));
}

if (!$email) {
    echo "Enter Admin Email: ";
    $email = trim(fgets(STDIN));
}

if (!$password) {
    echo "Enter Admin Password (min 8 characters): ";
    $password = trim(fgets(STDIN));
}

if (empty($name) || empty($email) || empty($password)) {
    die("Error: Name, email, and password are all required.\n");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: Invalid email format: {$email}\n");
}

if (strlen($password) < 8) {
    die("Error: Password must be at least 8 characters long.\n");
}

try {
    $db = Database::connect();
    $existing = User::findByEmail($email);

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    if ($existing) {
        echo "\nA user with email [{$email}] already exists (ID: {$existing['id']}, Role: {$existing['role']}).\n";
        echo "Promote this user to admin and update password? (y/N): ";
        $ans = strtolower(trim(fgets(STDIN)));
        if ($ans === 'y' || $ans === 'yes') {
            User::update((int)$existing['id'], [
                'name'        => $name,
                'password'    => $hash,
                'role'        => 'admin',
                'is_verified' => 1,
            ]);
            echo "Success: Existing user updated to Administrator!\n";
        } else {
            echo "Operation cancelled.\n";
        }
    } else {
        $id = User::create([
            'name'        => $name,
            'email'       => $email,
            'password'    => $hash,
            'role'        => 'admin',
            'is_verified' => 1,
        ]);
        echo "\nSuccess! Admin account created successfully.\n";
        echo "Admin ID:    {$id}\n";
        echo "Admin Name:  {$name}\n";
        echo "Admin Email: {$email}\n";
        echo "Role:        admin\n";
    }
    echo "\nYou can now log in at /auth/login\n";
} catch (\Throwable $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
