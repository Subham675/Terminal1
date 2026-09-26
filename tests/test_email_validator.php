<?php
require_once __DIR__ . '/../app/middleware/EmailValidator.php';

$testCases = [
    // [Email, Expected Valid, Description]
    ['xyz@gmail.com', false, 'Gmail username too short (< 6 chars)'],
    ['abc@gmail.com', false, 'Gmail username too short (< 6 chars)'],
    ['test.1@gmail.com', false, 'Gmail username with dots too short (5 chars)'],
    ['subham.karmakar@gmail.com', true, 'Valid Gmail address (14 chars)'],
    ['karmakarsubham194@gmail.com', true, 'Valid Gmail address with numbers'],
    ['user@gmai.com', false, 'Common typo: gmai.com'],
    ['user@gmial.com', false, 'Common typo: gmial.com'],
    ['tempuser@mailinator.com', false, 'Disposable email blocked'],
    ['random@temp-mail.org', false, 'Disposable email blocked'],
    ['test@nonexistentdomain123456789xyzabc.com', false, 'Non-existent domain (no MX/A records)'],
    ['user@outlook.com', true, 'Valid Outlook domain'],
    ['valid.user@yahoo.com', true, 'Valid Yahoo domain'],
    ['.leadingdot@gmail.com', false, 'Gmail starting with dot'],
    ['trailingdot.@gmail.com', false, 'Gmail ending with dot'],
    ['two..dots@gmail.com', false, 'Gmail consecutive dots'],
    ['invalid_char@gmail.com', false, 'Gmail invalid character underscore'],
    ['xyznotrealemail12345@gmail.com', false, 'Non-existent Gmail mailbox (> 6 chars)'],
    ['subhamkarmakar99999999fake@gmail.com', false, 'Non-existent Gmail mailbox with numbers'],
];

$allPassed = true;
echo "Running EmailValidator tests...\n\n";

foreach ($testCases as [$email, $expectedValid, $desc]) {
    [$isValid, $message, $cleaned] = EmailValidator::validate($email);
    $status = ($isValid === $expectedValid) ? 'PASS' : 'FAIL';
    if ($status === 'FAIL') {
        $allPassed = false;
        echo "[FAIL] {$desc}: {$email}\n";
        echo "       Expected: " . ($expectedValid ? 'VALID' : 'INVALID') . ", got: " . ($isValid ? 'VALID' : 'INVALID') . "\n";
        echo "       Message:  {$message}\n\n";
    } else {
        echo "[PASS] {$desc} -> " . ($isValid ? 'Valid' : 'Blocked: ' . $message) . "\n";
    }
}

if ($allPassed) {
    echo "\nAll test cases PASSED successfully!\n";
    exit(0);
} else {
    echo "\nSome test cases FAILED.\n";
    exit(1);
}
