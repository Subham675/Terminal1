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
// Map dish names to real photos in /images/ with intelligent variety
function getDishImage($name, $customUrl = null) {
    if (!empty($customUrl) && !str_contains($customUrl, 'aveline_interior')) {
        if (str_starts_with($customUrl, 'http://') || str_starts_with($customUrl, 'https://')) {
            return $customUrl;
        }
        return asset(ltrim($customUrl, '/'));
    }
    $lower = strtolower($name);
    // Drinks & Water
    if (str_contains($lower, 'water') || str_contains($lower, 'bisleri') || str_contains($lower, 'drink') || str_contains($lower, 'beverage') || str_contains($lower, 'mojito') || str_contains($lower, 'soda')) {
        return asset('images/beverage.jpg');
    }
    // Salad
    if (str_contains($lower, 'salad')) return asset('images/salad.jpg');
    // Rolls
    if (str_contains($lower, 'roll') && !str_contains($lower, 'spring roll')) return asset('images/kathi_roll.jpg');
    // Breads
    if (str_contains($lower, 'naan') || str_contains($lower, 'kulcha') || str_contains($lower, 'paratha')) return asset('images/butter_naan.jpg');
    if (str_contains($lower, 'roti')) return asset('images/food1.webp');
    // Rice & Biryani
    if (str_contains($lower, 'biryani')) {
        return str_contains($lower, 'mutton') ? asset('images/feast.webp') : asset('images/biryani.webp');
    }
    if (str_contains($lower, 'pulao') || str_contains($lower, 'polao') || str_contains($lower, 'jeera rice')) return asset('images/polao.webp');
    if (str_contains($lower, 'fried rice')) return asset('images/fried_rice.webp');
    if (str_contains($lower, 'noodle') || str_contains($lower, 'chowmein')) return asset('images/noodles.webp');
    // Starters
    if (str_contains($lower, 'paneer tikka')) return asset('images/paneer_tikka.jpg');
    if (str_contains($lower, 'tandoori chicken')) return asset('images/tandoori_chicken.jpg');
    if (str_contains($lower, 'lollipop') || str_contains($lower, 'drums') || str_contains($lower, 'wings')) return asset('images/chicken_lollipop.jpg');
    if (str_contains($lower, 'chilli') || str_contains($lower, 'manchurian')) return asset('images/chilli_chicken.jpg');
    if (str_contains($lower, 'tikka') || str_contains($lower, 'kebab') || str_contains($lower, 'pakoda')) return asset('images/starters_platter.webp');
    // Main Course Curries
    if (str_contains($lower, 'dal')) return asset('images/dal_makhani.jpg');
    if (str_contains($lower, 'paneer') || str_contains($lower, 'kofta') || str_contains($lower, 'navaratna')) return asset('images/paneer_butter_masala.jpg');
    if (str_contains($lower, 'butter masala') || str_contains($lower, 'tikka masala') || str_contains($lower, 'bharta')) return asset('images/butter_chicken.jpg');
    if (str_contains($lower, 'mutton')) return asset('images/feast.webp');
    if (str_contains($lower, 'chicken')) return asset('images/dhonkami_chicken.webp');
    return asset('images/bowl.webp');
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
