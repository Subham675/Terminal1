<?php
/**
 * Test reservation submission end-to-end to verify that:
 * 1. POST /book and POST /bookings both succeed and return valid JSON
 * 2. Unauthenticated request correctly returns require_login JSON
 * 3. Authenticated request creates a booking record with tracking_token
 * 4. GET /track/{token} and GET /bookings/view?id=...&token=... load properly
 */

$baseUrl = 'http://localhost/terminal1/public';

echo "=== Testing Table Reservation Flow ===\n\n";

// Test 1: Verify unauthenticated POST to /bookings returns JSON with require_login
$ch = curl_init("$baseUrl/bookings");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(['guests' => 2]),
    CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']
]);
$res1 = curl_exec($ch);
$http1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data1 = json_decode($res1, true);
if ($http1 === 401 && !empty($data1['require_login'])) {
    echo "[PASS] Unauthenticated POST to /bookings returns 401 with require_login JSON.\n";
} else {
    echo "[FAIL] Expected 401 require_login, got HTTP $http1: $res1\n";
    exit(1);
}

// Test 2: Verify unauthenticated POST to /book also returns JSON with require_login (no 404 HTML!)
$ch = curl_init("$baseUrl/book");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(['guests' => 2]),
    CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']
]);
$res2 = curl_exec($ch);
$http2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data2 = json_decode($res2, true);
if ($http2 === 401 && !empty($data2['require_login'])) {
    echo "[PASS] Unauthenticated POST to /book returns 401 with require_login JSON (No 404!).\n";
} else {
    echo "[FAIL] /book failed with HTTP $http2: $res2\n";
    exit(1);
}

// Test 3: Authenticated booking via internal script
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/config/config.php';
require_once APP_ROOT . '/app/config/Database.php';
require_once APP_ROOT . '/app/models/Booking.php';

// Fetch User #16 (karmakarsubham194@gmail.com)
$user = Database::row("SELECT id, name, email FROM users WHERE email = 'karmakarsubham194@gmail.com'");
if (!$user) {
    echo "[FAIL] Test user not found in database.\n";
    exit(1);
}

// Simulate session and CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user'] = $user;
$token = csrfToken();

$_POST = [
    'csrf_token'       => $token,
    'guests'           => 2,
    'name'             => $user['name'],
    'phone'            => '9876543210',
    'email'            => $user['email'],
    'booking_date'     => date('Y-m-d', strtotime('+1 day')),
    'booking_time'     => '19:00',
    'occasion'         => 'Casual Fine Dining',
    'special_requests' => 'Window seat please'
];

require_once APP_ROOT . '/app/controllers/BookingController.php';

ob_start();
BookingController::store();
$out = ob_get_clean();

$bookingRes = json_decode($out, true);
if (!empty($bookingRes['success']) && !empty($bookingRes['id']) && !empty($bookingRes['tracking_token'])) {
    echo "[PASS] Authenticated booking succeeded! Booking ID #{$bookingRes['id']} | Token: {$bookingRes['tracking_token']}\n";
} else {
    echo "[FAIL] Booking creation failed: $out\n";
    exit(1);
}

$createdId = $bookingRes['id'];
$trackingToken = $bookingRes['tracking_token'];

// Test 4: Verify booking detail page via URL
$detailHtml = file_get_contents("$baseUrl/bookings/view?id=$createdId&token=" . urlencode($trackingToken));
if (str_contains($detailHtml, "Reservation #$createdId") || str_contains($detailHtml, 'Casual Fine Dining')) {
    echo "[PASS] GET /bookings/view?id=$createdId&token=... rendered correctly.\n";
} else {
    echo "[FAIL] GET /bookings/view failed to render.\n";
    exit(1);
}

// Test 5: Verify dynamic /track/{token}
$trackHtml = file_get_contents("$baseUrl/track/$trackingToken");
if (str_contains($trackHtml, "Reservation #$createdId") || str_contains($trackHtml, 'Casual Fine Dining')) {
    echo "[PASS] GET /track/$trackingToken dynamically rendered booking details.\n";
} else {
    echo "[FAIL] GET /track/$trackingToken failed to render.\n";
    exit(1);
}

// Clean up test booking
Database::query("DELETE FROM bookings WHERE id = ?", [$createdId]);
echo "[PASS] Test booking cleaned up.\n";

echo "\nALL BOOKING SUBMISSION TESTS PASSED!\n";
