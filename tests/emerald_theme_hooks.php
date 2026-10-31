<?php
// Standalone integration check against Perfex's asset registry.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
define('FCPATH', dirname(__DIR__) . '/');
function base_url($path) { return 'https://dev.justsmartchoice.com/' . $path; }
function html_escape($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
class ThemeTestHooks {
    public $actions = [];
    public function add_filter(...$args) {}
    public function apply_filters($name, $value) { return $value; }
    public function add_action($event, $callback) { $this->actions[$event][] = $callback; }
    public function run($event) { foreach ($this->actions[$event] ?? [] as $callback) $callback(); }
}
function get_option($key) { return ''; }
function hooks() { static $hooks; return $hooks ?? ($hooks = new ThemeTestHooks()); }
require FCPATH . 'application/libraries/assets/App_css.php';
$ci = (object) ['app_css' => new App_css()];
function &get_instance() { global $ci; return $ci; }
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
require FCPATH . 'application/helpers/my_functions_helper.php';
$ci->app_css->add('app-css', 'assets/css/style.css');
$ci->app_css->add('custom-css', 'assets/css/custom.css');
$ci->app_css->add('custom-css', 'assets/css/custom.css', 'customers-area-default');
hooks()->run('app_admin_assets_added');
$assets = $ci->app_css->get();
check(!isset($assets['admin']['custom-css']), 'Native admin CSS must not be loaded twice');
check(isset($assets['admin']['app-css']), 'Core styles must remain registered');
check(isset($assets['customers-area-default']['custom-css']), 'Customer CSS registration must be preserved');
ob_start(); hooks()->run('app_admin_head'); $head = ob_get_clean();
ob_start(); hooks()->run('app_admin_footer'); $footer = ob_get_clean();
check(substr_count($head, 'assets/css/custom.css?v=') === 1, 'Head must load one stylesheet');
check(substr_count($footer, 'assets/js/custom.js?v=') === 1, 'Footer must load one script');
foreach (['assets/css/custom.css', 'assets/js/custom.js'] as $path) {
    check(is_file(FCPATH . $path), 'Missing asset ' . $path);
    check(smartchoice_theme_asset_url($path) === base_url($path . '?v=' . filemtime(FCPATH . $path)), 'Stable asset version');
}
ob_start(); hooks()->run('after_body_start'); $restore = ob_get_clean();
check(strpos($restore, "classList.contains('admin')") !== false, 'Early restore must be admin-only');
echo "PASS: hooks, asset paths, one admin load, core/customer preservation, stable cache versions\n";
