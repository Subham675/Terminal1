<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/middleware/EmailValidator.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/OtpModel.php';

echo "=== Registration Validation & Security Test ===\n\n";

$shortGmail = 'xyz@gmail.com';
[$isValid, $error, $cleaned] = EmailValidator::validate($shortGmail);

if (!$isValid && str_contains($error, 'at least 6 characters')) {
    echo "[PASS] xyz@gmail.com correctly blocked before account creation:\n       '{$error}'\n";
} else {
    echo "[FAIL] xyz@gmail.com was not blocked as expected.\n";
    exit(1);
}

$typoEmail = 'test@gmai.com';
[$isValidTypo, $errorTypo] = EmailValidator::validate($typoEmail);
if (!$isValidTypo && str_contains($errorTypo, 'Did you mean')) {
    echo "[PASS] test@gmai.com correctly blocked with typo suggestion:\n       '{$errorTypo}'\n";
} else {
    echo "[FAIL] test@gmai.com was not caught as typo.\n";
    exit(1);
}

$dispEmail = 'throwaway@mailinator.com';
[$isValidDisp, $errorDisp] = EmailValidator::validate($dispEmail);
if (!$isValidDisp && str_contains($errorDisp, 'disposable')) {
    echo "[PASS] throwaway@mailinator.com correctly blocked as disposable:\n       '{$errorDisp}'\n";
} else {
    echo "[FAIL] throwaway@mailinator.com was not caught.\n";
    exit(1);
}

$badDomain = 'randomuser@nonexistentdomain999888777111.org';
[$isValidDomain, $errorDomain] = EmailValidator::validate($badDomain);
if (!$isValidDomain && str_contains($errorDomain, 'does not exist')) {
    echo "[PASS] Non-existent domain correctly caught via DNS MX/A check:\n       '{$errorDomain}'\n";
} else {
    echo "[FAIL] Non-existent domain was not caught.\n";
    exit(1);
}

// Verify that xyz@gmail.com does NOT exist in users table
$found = User::findByEmail($shortGmail);
if ($found === null) {
    echo "[PASS] Database confirmed clean: No ghost record for {$shortGmail}.\n";
} else {
    echo "[FAIL] Found unexpected user record for {$shortGmail}.\n";
    exit(1);
}

echo "\nAll registration validation tests PASSED successfully!\n";
