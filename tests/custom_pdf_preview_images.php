<?php
// php tests/custom_pdf_preview_images.php — synthetic temporary artwork only.
if (PHP_SAPI !== 'cli') { exit(1); }
define('BASEPATH', __DIR__);
$tmp = sys_get_temp_dir() . '/custom-pdf-preview-' . bin2hex(random_bytes(6));
define('FCPATH', $tmp . '/');
function get_option($key) { return $GLOBALS['options'][$key] ?? ''; }
function base_url($path) { return 'https://example.test/' . $path; }
require __DIR__ . '/../modules/custom_pdf/helpers/custom_pdf_helper.php';
function checkImage($value, $label) { if (!$value) { throw new RuntimeException($label); } echo "PASS $label\n"; }
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a4X0AAAAASUVORK5CYII=');
try {
    mkdir($tmp . '/modules/custom_pdf/assets/bookends', 0700, true);
    foreach (['invoice-cover.png', 'estimate-cover.png', 'closing-page.png'] as $file) { file_put_contents($tmp . '/modules/custom_pdf/assets/bookends/' . $file, $png); }
    foreach (['invoice', 'estimate'] as $type) {
        $GLOBALS['options'][$type . '_pdf_settings'] = json_encode(['cover_page' => ['image' => 'cover_page.png']]);
        checkImage(strpos(custom_pdf_section_image_url($type, 'cover_page'), $type . '-cover.png?v=') !== false, "$type missing upload previews matching branded artwork");
    }
    mkdir($tmp . '/uploads/custom_pdf/invoice', 0700, true);
    file_put_contents($tmp . '/uploads/custom_pdf/invoice/cover_page.png', $png);
    checkImage(strpos(custom_pdf_section_image_url('invoice', 'cover_page'), 'uploads/custom_pdf/invoice/cover_page.png') !== false, 'existing uploaded artwork takes priority');
    $GLOBALS['options']['estimate_pdf_settings'] = json_encode(['cover_page' => ['image' => 'absent.png', 'text' => '<h1>Custom cover</h1>']]);
    checkImage(custom_pdf_section_image_url('estimate', 'cover_page') === '', 'text-only custom cover is preserved');
    checkImage(custom_pdf_section_image_url('estimate', 'header') === '', 'missing header never links to a missing file');
    checkImage(custom_pdf_section_image_url('../invoice', 'cover_page') === '' && custom_pdf_section_image_url('invoice', '../cover_page') === '', 'invalid types and sections rejected');
    $GLOBALS['options']['invoice_pdf_settings'] = json_encode(['header' => ['image' => '../outside.png']]);
    checkImage(custom_pdf_section_image_url('invoice', 'header') === '', 'traversal rejected');
    file_put_contents($tmp . '/outside.png', $png);
    symlink($tmp . '/outside.png', $tmp . '/uploads/custom_pdf/invoice/header.png');
    $GLOBALS['options']['invoice_pdf_settings'] = json_encode(['header' => ['image' => 'header.png']]);
    checkImage(custom_pdf_section_image_url('invoice', 'header') === '', 'symlink outside upload folder rejected');
} finally {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) { $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname()); }
    rmdir($tmp);
}
