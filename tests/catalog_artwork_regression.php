<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
function module_dir_path($module, $path) {
    if ($path === 'uploads') return $GLOBALS['uploadFixture'];
    return dirname(__DIR__) . '/modules/' . $module . '/' . $path;
}
function module_dir_url($module, $path) { return '/modules/' . $module . '/' . $path; }
function &get_instance() { return $GLOBALS['upgradeCI']; }
function db_prefix() { return 'tbl'; }
function add_option($name, $value) {}
function update_option($name, $value) {}
function site_url($path) { return '/' . $path; }
class App_module_migration {}
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
    foreach (['Air Handler Platform', 'Accent Wall Painting', 'Luxury Vinyl Plank Installation', 'Special service'] as $name) {
        check(products_catalog_artwork_urls(['product_name' => $name]) === [], 'Unmatched services must not borrow unrelated original artwork');
    }
    $manifest = json_decode(file_get_contents(dirname(__DIR__) . '/modules/products/assets/images/original-services/manifest.json'), true);
    check(count($manifest) === 27, 'Recover exactly the 27 original services');
    foreach ($manifest as $name => $file) {
        check(products_catalog_artwork_urls(['product_name' => $name, 'product_image' => 'sc-' . $name . '.jpg']) === ['/modules/products/assets/images/original-services/' . $file], 'Recover every verified original: ' . $name);
        check(is_file(dirname(__DIR__) . '/modules/products/assets/images/original-services/' . $file), 'Every original ships in Git');
    }
    // Regression for the upgrade that originally overwrote every configured image.
    $upgradeCI = (object) ['db' => new class {
        public $updates = [];
        public $selectedId;
        public function table_exists($table) { return true; }
        public function get($table) { return $this; }
        public function result() { return [(object) ['id' => 3, 'product_name' => 'Home Cleaning Service', 'product_image' => 'product_3.png'], (object) ['id' => 100, 'product_name' => 'Custom service', 'product_image' => 'custom.jpg'], (object) ['id' => 200, 'product_name' => 'Empty service', 'product_image' => null]]; }
        public function where($field, $id) { $this->selectedId = $id; return $this; }
        public function update($table, $data) { $this->updates[$this->selectedId] = $data; }
    }];
    require dirname(__DIR__) . '/modules/products/migrations/203_version_203.php';
    products_smartchoice_catalog_seed_203();
    check(array_keys($upgradeCI->db->updates) === [200], 'Catalog upgrade preserves original and custom image assignments');
    echo "PASS 27 verified original assignments, no guessed matches, real galleries and safe paths\n";
} finally {
    foreach (glob($uploadFixture . '/*') as $file) unlink($file);
    rmdir($uploadFixture);
}
