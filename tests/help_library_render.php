<?php
// Isolated presentation check: the controller and permission queries are untouched.
define('BASEPATH', __DIR__);
define('TRAINING_MANUAL_ASSETS_PATH', 'modules/training_manual/assets');
function base_url($path) { return '/' . $path; }
function site_url($path) { return '/' . $path; }
function html_escape($text) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
function training_manual_customer_image_url($value = '', $title = '') { return '/guide-cover.svg'; }
function _l($key) { return $key; }
$view = dirname(__DIR__) . '/modules/training_manual/views/client_portal_books.php';
function render_library($books) { global $view; ob_start(); require $view; return ob_get_clean(); }
$books = [
 ['name'=>'Our Project Process', 'short_description'=>'Process', 'articles'=>[
  ['id'=>57,'title'=>'Employee training','description'=>'Trains employees in contracts'],
  ['id'=>47,'title'=>'How Our Project Process Works','description'=>'Plan <b>your</b> project']]],
 ['name'=>'How to Use the Client Portal','articles'=>[['id'=>32,'title'=>'Using your portal','description'=>'Documents and files']]],
 ['name'=>'Future & <script>alert(1)</script>', 'articles'=>[['id'=>100,'title'=>'A <script>bad()</script> title','description'=>'Quote " & <b>notes</b>']]],
];
$html = render_library($books);
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
];
foreach ($checks as $name=>$pass) { echo ($pass ? 'PASS ' : 'FAIL ') . $name . PHP_EOL; }
exit(in_array(false, $checks, true) ? 1 : 0);
