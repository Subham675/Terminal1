<?php
/**
 * Run this ONCE after upload to map your uploaded images to named paths.
 * Visit: https://yourdomain.com/setup_images.php  (then delete it!)
 */
$imgDir = __DIR__ . '/images/';
if(!is_dir($imgDir)) mkdir($imgDir, 0755, true);

$map = [
    'exterior.png'    => '1772534211481_image.png',
    'signboard.png'   => '1772534251141_image.png',
    'food_decor.png'  => '1772534268387_image.png',
    'celebration1.png'=> '1772534293807_image.png',
    'menu_board.png'  => '1772534312453_image.png',
    'vibe.png'        => '1772534319650_image.png',
    'interior.png'    => '1772534340975_image.png',
    'interior2.png'   => '1772534350887_image.png',
    'counter.png'     => '1772534361244_image.png',
    'dining.png'      => '1772534379227_image.png',
    'biryani.png'     => '1772534388023_image.png',
    'feast.png'       => '1772534426899_image.png',
    'flowers.png'     => '1772534436913_image.png',
    'couple.png'      => '1772534455611_image.png',
    'couple2.png'     => '1772534463947_image.png',
    'bowl.png'        => '1772534476585_image.png',
    'food1.png'       => '1772534268387_image.png',
];

$srcDir = __DIR__ . '/images/uploads/';
echo '<pre>';
foreach($map as $alias => $original){
    $src = $srcDir . $original;
    $dst = $imgDir . $alias;
    if(file_exists($src)){
        copy($src, $dst);
        echo "✓ $alias\n";
    } else {
        echo "✗ $original not found (upload it to public/images/uploads/)\n";
    }
}
echo "\nDone! Delete this file after running.</pre>";
