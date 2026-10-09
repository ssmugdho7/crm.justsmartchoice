<?php
// Synthetic attachment storage only; no application bootstrap, database or remote requests.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/application/language/english/english_lang.php';
require dirname(__DIR__) . '/application/helpers/smart_choice_sales_links_helper.php';
$base = sys_get_temp_dir() . '/sc-attachment-' . bin2hex(random_bytes(5));
$mappedPath = null;
$hookCalls = [];
function get_upload_path_by_type($type) { global $base; return $base . '/' . $type . '/'; }
function hooks() {
    return new class {
        function apply_filters($name, $path, $context) {
            global $mappedPath, $hookCalls;
            $hookCalls[] = [$name, $context];
            return $mappedPath ?? $path;
        }
    };
}
function html_escape($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
function _l($key) { global $lang; return $lang[$key] ?? $key; }
function site_url($path) { return 'https://example.test/' . $path; }
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
$paths = [];
try {
    foreach (['proposal', 'estimate', 'invoice'] as $type) {
        $row = ['id' => 206, 'rel_type' => $type, 'rel_id' => 2, 'file_name' => 'Example Quote.pdf', 'attachment_key' => 'fixture-key', 'visible_to_customer' => 1];
        check(!sc_sales_attachment_available($row), 'Missing file was treated as available');
        $dir = get_upload_path_by_type($type) . '2';
        mkdir($dir, 0700, true);
        $path = $dir . '/' . $row['file_name'];
        file_put_contents($path, '%PDF-1.4 synthetic test');
        $paths[] = $path;
        check(sc_sales_attachment_available($row), 'Readable local file was hidden');
        check(sc_sales_attachment_preview_url($row) === 'https://example.test/download/file/sales_attachment/fixture-key?preview=1', 'Preview URL changed');
        check(sc_sales_attachment_download_url($row) === 'https://example.test/download/file/sales_attachment/fixture-key', 'Download URL changed');
        $missing = $row;
        $missing['file_name'] = '<script>alert(1)</script>.pdf';
        $html = sc_sales_attachment_unavailable_html($missing);
        check(strpos($html, '&lt;script&gt;') !== false && strpos($html, '<script>') === false, 'Filename not escaped');
        check(strpos($html, '<iframe') === false && strpos($html, 'href=') === false, 'Unavailable attachment contains broken embed/link');
        check(strpos($html, 'upload it again') !== false, 'Missing file message absent');
    }
    $mappedPath = $paths[0];
    check(sc_sales_attachment_available(['rel_type' => 'proposal', 'rel_id' => 99, 'file_name' => 'mapped.pdf', 'attachment_key' => 'mapped-key']), 'Custom storage hook not respected');
    $last = end($hookCalls);
    check($last === ['download_file_path', ['folder' => 'sales_attachment', 'attachmentid' => 'mapped-key']], 'Download hook context changed');
    $mappedPath = null;
    check(sc_sales_attachment_available(['external' => 'gdrive', 'external_link' => 'https://example.test/document']), 'Existing external link hidden');
    check(!sc_sales_attachment_available(['external' => 'gdrive', 'external_link' => '']), 'Empty external link shown');
    check(!sc_sales_attachment_available([]), 'Incomplete row accepted');
    check(!sc_sales_attachment_available(['rel_type' => 'staff', 'rel_id' => 2, 'file_name' => 'Example Quote.pdf']), 'Unsupported document type accepted');
    foreach (['perfex', 'smartchoice'] as $theme) {
        foreach (['viewproposal', 'estimatehtml', 'invoicehtml'] as $view) {
            $source = file_get_contents(dirname(__DIR__) . '/application/views/themes/' . $theme . '/views/' . $view . '.php');
            $guard = strpos($source, 'if (!sc_sales_attachment_available($attachment))');
            $url = strpos($source, '$attachment_url = sc_sales_attachment_download_url($attachment);');
            check($guard !== false && $guard < $url, 'Customer view lacks availability guard');
            $branch = substr($source, $guard, $url - $guard);
            check(strpos($branch, 'sc_sales_attachment_unavailable_html($attachment)') !== false && strpos($branch, 'continue;') !== false, 'Missing file still falls through to embed');
            check(strpos($source, "if ($" . "attachment['visible_to_customer'] == 0)") < $guard, 'Visibility check moved after availability guard');
        }
    }
    echo "PASS customer attachments: missing/readable files, external links, storage hooks, escaped fallback and both themes; no data changed\n";
} finally {
    foreach ($paths as $path) { unlink($path); rmdir(dirname($path)); rmdir(dirname(dirname($path))); }
    if (is_dir($base)) { rmdir($base); }
}
