<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
function module_dir_path($module, $path) {
    if ($path === 'uploads') return $GLOBALS['uploadFixture'];
    return dirname(__DIR__) . '/modules/' . $module . '/' . $path;
}
function module_dir_url($module, $path) { return '/modules/' . $module . '/' . $path; }
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
require dirname(__DIR__) . '/modules/products/helpers/products_helper.php';
$uploadFixture = sys_get_temp_dir() . '/catalog-artwork-' . bin2hex(random_bytes(8));
mkdir($uploadFixture);
try {
    $clean = ['id' => 3, 'product_name' => 'Home Cleaning Service', 'product_image' => 'sc-default-service-1.jpg'];
    $door = ['id' => 17, 'product_name' => 'Interior door replacement', 'product_image' => 'sc-default-service-1.jpg'];
    $window = ['id' => 6, 'product_name' => 'Window Cleaning', 'product_image' => 'sc-window-cleaning.jpg'];
    $urls = [products_catalog_artwork_urls($clean)[0], products_catalog_artwork_urls($door)[0], products_catalog_artwork_urls($window)[0]];
    check(count(array_unique($urls)) === 3, 'Different services must recover different original artwork');
    foreach ($urls as $url) check(is_file(dirname(__DIR__) . $url), 'Artwork must ship in Git');
    file_put_contents($uploadFixture . '/custom.jpg', 'fixture');
    file_put_contents($uploadFixture . '/gallery.webp', 'fixture');
    $custom = $clean; $custom['product_image'] = 'custom.jpg';
    check(products_catalog_artwork_urls($custom, [['image' => 'gallery.webp'], ['image' => 'custom.jpg']]) === ['/modules/products/uploads/custom.jpg', '/modules/products/uploads/gallery.webp'], 'Real uploads take priority and retain a deduplicated gallery');
    symlink(__FILE__, $uploadFixture . '/outside.jpg');
    foreach (['../private.jpg', 'https://tracker.example/a.jpg', 'outside.jpg', 'image-not-available.png', 'sc-default-service-2.jpg', 'missing.jpg'] as $bad) {
        $custom['product_image'] = $bad;
        check(products_catalog_artwork_urls($custom) === [$urls[0]], 'Unsafe/missing/template filename must fall back: ' . $bad);
    }
    foreach (['Flooring' => 'flooring', 'Roofing' => 'roofing', 'Concrete' => 'concrete'] as $category => $file) {
        check(products_catalog_artwork_urls(['product_name' => 'Special service', 'p_category_name' => $category]) === ['/modules/products/assets/images/original-services/' . $file . '.svg'], 'Fallback illustration must match service type');
    }
    echo "PASS distinct recovered artwork, real galleries, shipped assets, related-service fallback and safe paths\n";
} finally {
    foreach (glob($uploadFixture . '/*') as $file) unlink($file);
    rmdir($uploadFixture);
}
