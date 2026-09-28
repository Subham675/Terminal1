<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/Review.php';

echo "=== Running Terminal 1 Guest Reviews & Feedback Tests ===\n\n";

$passCount = 0;

function assertTest(bool $condition, string $msg): void {
    global $passCount;
    if (!$condition) {
        echo "  [FAIL] $msg\n";
        exit(1);
    }
    echo "  [PASS] $msg\n";
    $passCount++;
}

// 1. Create a Compliment
$compTitle = 'Test Compliment ' . time();
$compId = Review::create([
    'name'       => 'Test Gourmet Diner',
    'email'      => 'gourmet@test.com',
    'type'       => 'compliment',
    'rating'     => 5,
    'title'      => $compTitle,
    'content'    => 'The Dhonkami chicken was smoky, rich and exquisite beyond words.',
    'visit_date' => date('Y-m-d'),
    'status'     => 'approved',
]);
assertTest($compId > 0, 'Compliment review created with ID #' . $compId);

// 2. Create a Complaint
$critTitle = 'Test Complaint ' . time();
$critId = Review::create([
    'name'       => 'Test Critical Diner',
    'email'      => 'critic@test.com',
    'type'       => 'complaint',
    'rating'     => 2,
    'title'      => $critTitle,
    'content'    => 'Table water was not refilled promptly during peak Saturday dinner service.',
    'visit_date' => date('Y-m-d'),
    'status'     => 'approved',
]);
assertTest($critId > 0, 'Complaint review created with ID #' . $critId);

// 3. Verify retrieval by type
$compliments = Review::all('compliment', 'approved');
$complimentFound = false;
foreach ($compliments as $c) {
    if ($c['id'] == $compId && $c['type'] === 'compliment') {
        $complimentFound = true;
        break;
    }
}
assertTest($complimentFound, 'Compliments filter correctly returns the newly created compliment');

$complaints = Review::all('complaint', 'approved');
$complaintFound = false;
foreach ($complaints as $c) {
    if ($c['id'] == $critId && $c['type'] === 'complaint') {
        $complaintFound = true;
        break;
    }
}
assertTest($complaintFound, 'Complaints filter correctly returns the newly created complaint');

// 4. Verify Stats calculation
$stats = Review::stats();
assertTest($stats['total'] >= 2, 'Stats total count is accurate (' . $stats['total'] . ')');
assertTest($stats['compliments'] >= 1, 'Stats compliments count is accurate (' . $stats['compliments'] . ')');
assertTest($stats['complaints'] >= 1, 'Stats complaints count is accurate (' . $stats['complaints'] . ')');
assertTest($stats['avg_rating'] > 0 && $stats['avg_rating'] <= 5.0, 'Stats average rating is within 1-5 range (' . $stats['avg_rating'] . ')');

// 5. Test Management Reply
$replyText = 'Thank you for your constructive critique. Our floor manager has addressed table beverage pacing.';
Review::updateReply($critId, $replyText);
$updatedCrit = Review::findById($critId);
assertTest($updatedCrit['admin_reply'] === $replyText, 'Official management reply saved successfully');

// 6. Test Status Moderation
Review::updateStatus($critId, 'hidden');
$hiddenCrit = Review::findById($critId);
assertTest($hiddenCrit['status'] === 'hidden', 'Review status updated to hidden');

$approvedList = Review::all(null, 'approved');
$hiddenFoundInApproved = false;
foreach ($approvedList as $a) {
    if ($a['id'] == $critId) {
        $hiddenFoundInApproved = true;
        break;
    }
}
assertTest(!$hiddenFoundInApproved, 'Hidden review is properly filtered out of the approved public list');

// 7. Cleanup test records
Review::delete($compId);
Review::delete($critId);
assertTest(Review::findById($compId) === null, 'Test compliment cleaned up');
assertTest(Review::findById($critId) === null, 'Test complaint cleaned up');

echo "\nSummary: $passCount of $passCount tests passed.\n";
echo "Result: ALL GUEST REVIEW TESTS PASSED!\n";
