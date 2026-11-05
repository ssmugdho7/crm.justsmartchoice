<?php
define('BASEPATH', __DIR__);
define('TRAINING_MANUAL_ASSETS_PATH', 'modules/training_manual/assets');
function init_head() {} function init_tail() {}
function html_escape($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function base_url($path) { return 'https://example.invalid/' . $path; }
function admin_url($path) { return base_url('admin/' . $path); }
function _l($key) { return $key; }
function has_permission(...$args) { return $GLOBALS['canManage']; }
function training_manual_unserialize($value, $default) { return unserialize($value); }
function form_open(...$args) { return '<form>'; } function form_close() { return '</form>'; }
class TrainingView {
 public $uri;
 function __construct() { $this->uri = new class { function uri_string() { return 'admin/training_manual/books'; } }; }
 function render($canManage) {
  $GLOBALS['canManage'] = $canManage;
  $title = 'Manuals'; $filter_query = '"><img src=x onerror=alert(1)>';
  $books = [
   ['id'=>1,'name'=>'<script>bad()</script>','short_description'=>'<img src=x onerror=alert(1)>','customer_visible'=>0,'articles_total'=>3,'assign_ids'=>serialize([1,2])],
   ['id'=>2,'name'=>'Customer guide','short_description'=>'Portal help','customer_visible'=>1,'articles_total'=>4,'assign_ids'=>serialize([])],
  ];
  ob_start(); require __DIR__ . '/../modules/training_manual/views/books_manage.php'; return ob_get_clean();
 }
}
function training_check($ok,$message) { if (!$ok) throw new RuntimeException($message); }
$view = new TrainingView;
$html = $view->render(false);
training_check(strpos($html,'>7</strong>') !== false, 'Article metric uses actual visible results');
training_check(substr_count($html, 'class="sc-training-metric"') === 5, 'Five actual metrics');
training_check(strpos($html,'<script>bad()') === false && strpos($html,'<img src=x') === false, 'Search, book name and description escaped');
training_check(strpos($html,'/books/book') === false && strpos($html,'/books/delete/') === false, 'No create/edit/delete controls without original permissions');
training_check(strpos($html,'data-training-book-audience="customer"') !== false && strpos($html,'data-training-book-audience="internal"') !== false, 'Existing audiences exposed to filter');
$html = $view->render(true);
training_check(strpos($html,'/books/book/1') !== false && strpos($html,'/books/delete/1') !== false, 'Authorized original actions retained');
echo "PASS Training real metrics, escaped content, original permission controls and audiences\n";
