<?php
// Load menu from DB grouped by category
$menuGrouped = [];
try {
    $menuGrouped = MenuItem::byCategory();
} catch(Exception $e) {
    // DB not yet connected — will show empty menu gracefully
}

// Load guestbook reviews and statistics
$reviewsList = [];
$reviewStats = ['total' => 0, 'compliments' => 0, 'complaints' => 0, 'avg_rating' => 5.0];
try {
    $reviewsList = Review::all(null, 'approved', 24);
    $reviewStats = Review::stats();
} catch(\Throwable $e) {}

// Flat list of all items for interactive cards
$allItems = [];
if (!empty($menuGrouped)) {
    foreach ($menuGrouped as $catName => $items) {
        foreach ($items as $it) {
            $it['cat_name'] = $catName;
            $allItems[] = $it;
        }
    }
}

// Map dish names to real photos in /images/
function getDishImage($name, $customUrl = null) {
    if (!empty($customUrl)) {
        if (str_starts_with($customUrl, 'http://') || str_starts_with($customUrl, 'https://')) {
            return $customUrl;
        }
        return asset(ltrim($customUrl, '/'));
    }
    $lower = strtolower($name);
    if (str_contains($lower, 'biryani')) return asset('images/biryani.webp');
    if (str_contains($lower, 'pulao') || str_contains($lower, 'polao') || str_contains($lower, 'jeera rice') || str_contains($lower, 'steamed rice')) return asset('images/polao.webp');
    if (str_contains($lower, 'fried rice')) return asset('images/fried_rice.webp');
    if (str_contains($lower, 'noodle') || str_contains($lower, 'chowmein')) return asset('images/noodles.webp');
    if (str_contains($lower, 'tikka') || str_contains($lower, 'kebab') || str_contains($lower, 'tandoori') || str_contains($lower, 'lollipop') || str_contains($lower, 'starter') || str_contains($lower, 'wings') || str_contains($lower, 'pakoda')) return asset('images/starters_platter.webp');
    if (str_contains($lower, 'naan') || str_contains($lower, 'roti') || str_contains($lower, 'kulcha') || str_contains($lower, 'roll') || str_contains($lower, 'chole') || str_contains($lower, 'bhature')) return asset('images/food1.webp');
    if (str_contains($lower, 'mutton')) return asset('images/feast.webp');
    if (str_contains($lower, 'chicken')) return asset('images/dhonkami_chicken.webp');
    if (str_contains($lower, 'salad') || str_contains($lower, 'water') || str_contains($lower, 'bisleri')) return asset('images/aveline_interior.webp');
    return asset('images/aveline_plate.webp');
}

$user = authUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Terminal 1 — The Startup Canteen | Cooch Behar</title>
  <meta name="description" content="Terminal 1 The Startup Canteen in Cooch Behar adjacent to the historic Rajbari. Experience curated seasonal menus, clay oven roasts, and bespoke table reservations."/>
  <link rel="icon" type="image/svg+xml" href="<?= asset('favicon.svg') ?>"/>
  <link rel="alternate icon" href="<?= asset('favicon.ico') ?>"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>"/>
  <link rel="stylesheet" href="<?= asset('css/home.css') ?>"/>
</head>
<body>

<?php require APP_ROOT . '/app/views/partials/navbar.php'; ?>
<?php require APP_ROOT . '/app/views/partials/hero.php'; ?>
<?php require APP_ROOT . '/app/views/partials/experiences.php'; ?>
<?php require APP_ROOT . '/app/views/partials/menu.php'; ?>
<?php require APP_ROOT . '/app/views/partials/philosophy.php'; ?>
<?php require APP_ROOT . '/app/views/partials/booking.php'; ?>
<?php require APP_ROOT . '/app/views/partials/reviews.php'; ?>
<?php require APP_ROOT . '/app/views/partials/footer.php'; ?>
<?php require APP_ROOT . '/app/views/partials/modals.php'; ?>
<?php require APP_ROOT . '/app/views/partials/scripts.php'; ?>

</body>
</html>
