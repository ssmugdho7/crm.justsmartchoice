<?php
// Isolated presentation check: the controller and permission queries are untouched.
define('BASEPATH', __DIR__);
define('TRAINING_MANUAL_ASSETS_PATH', 'modules/training_manual/assets');
define('FCPATH', dirname(__DIR__) . '/');
function base_url($path) { return '/' . $path; }
function site_url($path) { return '/' . $path; }
function html_escape($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
require FCPATH . 'modules/training_manual/helpers/training_manual_helper.php';
function _l($key) { return $key; }
$view = dirname(__DIR__) . '/modules/training_manual/views/client_portal_books.php';
function render_library($books) { global $view; ob_start(); require $view; return ob_get_clean(); }
$books = [
 ['name'=>'Our Project Process', 'short_description'=>'Process', 'articles'=>[
  ['id'=>57,'title'=>'Employee training','description'=>'Trains employees in contracts'],
  ['id'=>47,'title'=>'How Our Project Process Works','description'=>'Plan <b>your</b> project']]],
 ['name'=>'How to Use the Client Portal','articles'=>[['id'=>32,'title'=>'Using your portal','description'=>'Documents and files','thumbnail'=>'https://cdn.example.test/broken-cover.png']]],
 ['name'=>'Future & <script>alert(1)</script>', 'articles'=>[['id'=>100,'title'=>'A <script>bad()</script> title','description'=>'Quote " & <b>notes</b>']]],
];
$html = render_library($books);
if (getenv('HELP_LIBRARY_RENDER_HTML')) { echo $html; exit; }
$doc = new DOMDocument(); @$doc->loadHTML($html);
$xpath = new DOMXPath($doc);
$checks = [
 'all articles retained'=> $xpath->query('//*[@data-guide]')->length === 4,
 'training separated'=> $xpath->query('//*[@data-guide and @data-category="training"]')->length === 1,
 'featured customer guide excludes training'=> $xpath->query('//a[contains(@class,"sc-help-guide-featured") and @href="/training_manual/customer-books/article/47"]')->length === 1,
 'original route retained'=> $xpath->query('//*[@data-guide and @href="/training_manual/customer-books/article/57"]')->length === 1,
 'dynamic titles escaped'=> strpos($html, '<script>bad()') === false && strpos($html, '&lt;script&gt;bad()') !== false,
 'fallback categories retain new content'=> $xpath->query('//*[@data-guide and @href="/training_manual/customer-books/article/100"]')->length === 1,
 'search progressive enhancement'=> $xpath->query('//*[contains(@class,"sc-help-controls") and @hidden]')->length === 1,
 'empty account has no inert controls'=> strpos(render_library([]), 'id="help-library-search"') === false,
 'every card has topic and generic image fallbacks'=> $xpath->query('//img[@data-fallback and @data-generic-fallback]')->length === 6,
 'every card has an offline category cover'=> $xpath->query('//*[contains(@class,"sc-help-cover-placeholder")]')->length === 6,
 'missing upload uses support cover'=> strpos(training_manual_customer_image_url('missing-cover.png', 'Using the Support Ticket System'), 'support-system.svg?v=2') !== false,
 'missing records cover uses document artwork'=> strpos(training_manual_customer_image_url('', 'Your Project Records'), 'documents-payments.svg?v=2') !== false,
 'unknown topic has generic artwork'=> strpos(training_manual_customer_image_url('', 'Unknown'), 'default-cover.svg?v=2') !== false,
 'valid custom cover retained'=> training_manual_customer_image_url('modules/training_manual/assets/img/customer-guides/client-portal.svg', 'Support') === '/modules/training_manual/assets/img/customer-guides/client-portal.svg',
 'remote cover retained for browser validation'=> training_manual_customer_image_url('https://example.com/cover.png', 'Support') === 'https://example.com/cover.png',
];
foreach (glob(FCPATH . TRAINING_MANUAL_ASSETS_PATH . '/img/customer-guides/*.svg') as $asset) {
 $svg = new DOMDocument();
 $checks['valid SVG: ' . basename($asset)] = @$svg->loadXML(file_get_contents($asset));
}
foreach ($checks as $name=>$pass) { echo ($pass ? 'PASS ' : 'FAIL ') . $name . PHP_EOL; }
exit(in_array(false, $checks, true) ? 1 : 0);
