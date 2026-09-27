<?php
// Test 1: Verify home page contains the login requirement for table reservation
$homeHtml = file_get_contents('http://localhost/terminal1/public/');
if (str_contains($homeHtml, 'Sign In to Reserve a Table')) {
    echo "[PASS] Home page presents 'Sign In to Reserve a Table' gate for guest visitors.\n";
} else {
    echo "[FAIL] Home page did not present login requirement.\n";
    exit(1);
}

if (str_contains($homeHtml, 'auth/login?redirect=')) {
    echo "[PASS] 'Reserve a Table' CTA correctly links to login with redirect parameter.\n";
} else {
    echo "[FAIL] 'Reserve a Table' CTA does not link to login redirect.\n";
    exit(1);
}

// Test 2: Verify login page renders reservation notice when redirected from #contact
$loginHtml = file_get_contents('http://localhost/terminal1/public/auth/login?redirect=' . urlencode('/#contact'));
if (str_contains($loginHtml, 'Please sign in to your account to reserve your table.')) {
    echo "[PASS] Login page displays table reservation banner when redirected from #contact.\n";
} else {
    echo "[FAIL] Login page did not display reservation banner.\n";
    exit(1);
}

// Test 3: Verify register page renders reservation notice when redirected from #contact
$regHtml = file_get_contents('http://localhost/terminal1/public/auth/register?redirect=' . urlencode('/#contact'));
if (str_contains($regHtml, 'Please create an account to proceed with your table reservation.')) {
    echo "[PASS] Register page displays reservation banner when redirected from #contact.\n";
} else {
    echo "[FAIL] Register page did not display reservation banner.\n";
    exit(1);
}

echo "\nAll Reserve-A-Table Login Gate tests PASSED successfully!\n";
