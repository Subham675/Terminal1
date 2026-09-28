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
    if (!empty($customUrl)) return $customUrl;
    $lower = strtolower($name);
    if (str_contains($lower, 'pasta') || str_contains($lower, 'spaghetti')) return asset('images/aveline_plate.webp');
    if (str_contains($lower, 'dhonkami') || str_contains($lower, 'chicken')) return asset('images/dhonkami_chicken.webp');
    if (str_contains($lower, 'starter') || str_contains($lower, 'platter') || str_contains($lower, 'tandoor') || str_contains($lower, 'kabab') || str_contains($lower, 'kebab')) return asset('images/starters_platter.webp');
    if (str_contains($lower, 'jiaozi') || str_contains($lower, 'dumpling') || str_contains($lower, 'momo')) return asset('images/jiaozi_hero.webp');
    if (str_contains($lower, 'bao') || str_contains($lower, 'dimsum') || str_contains($lower, 'dim sum')) return asset('images/bao_dimsum.webp');
    if (str_contains($lower, 'biryani')) return asset('images/biryani.webp');
    if (str_contains($lower, 'polao')) return asset('images/polao.webp');
    if (str_contains($lower, 'feast') || str_contains($lower, 'thali')) return asset('images/feast.webp');
    if (str_contains($lower, 'fried rice') || str_contains($lower, 'rice')) return asset('images/fried_rice.webp');
    if (str_contains($lower, 'noodle') || str_contains($lower, 'hakka') || str_contains($lower, 'chow')) return asset('images/noodles.webp');
    return asset('images/aveline_plate.webp');
}

$user = authUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Terminal 1 — A New Expression of Fine Dining | Cooch Behar</title>
  <meta name="description" content="Terminal 1 is an artisanal fine dining destination in Cooch Behar adjacent to the historic Rajbari. Experience curated seasonal tasting menus, clay oven roasts, and bespoke table reservations."/>
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
