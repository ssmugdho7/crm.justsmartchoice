<?php
// Exercise the real language loader when an inactive module is absent from HMVC lookup.
define('FCPATH', dirname(__DIR__) . '/');
define('APPPATH', FCPATH . 'application/');
define('BASEPATH', FCPATH . 'system/');
define('EXT', '.php');
function log_message(...$args) {}
function show_error($message) { throw new RuntimeException($message); }
function is_admin() { return true; }
function _l($key) { return $key; }
function module_dir_path($name) { return FCPATH . 'modules/' . $name . '/'; }
class CI { public static $APP; }
class Modules { static function find(...$args) { return [false, 'theme_style_lang']; } }
CI::$APP = (object) [
    'config' => new class { function item($name) { return 'english'; } },
    'router' => new class { function fetch_module() { return 'theme_style'; } },
];
require BASEPATH . 'core/Lang.php';
require APPPATH . 'core/App_Lang.php';
class AdminController {
    public $lang;
    public $load;
    function __construct() {
        $this->lang = new App_Lang();
        $this->load = new class {
            function helper($name) {}
            function view($name, $data) { $GLOBALS['viewLoaded'] = $name; }
        };
    }
}
require FCPATH . 'modules/theme_style/controllers/Theme_style.php';
foreach (['english', 'spanish', 'unavailable-language'] as $language) {
    $controller = new Theme_style();
    $controller->lang->last_loaded = $language;
    $controller->index();
    $expected = $language === 'spanish' ? 'Estilo del tema' : 'Theme Style';
    if ($controller->lang->line('theme_style') !== $expected || $GLOBALS['viewLoaded'] !== 'theme_style') {
        throw new RuntimeException('Inactive theme settings did not load translations and view');
    }
}
echo "PASS real framework language loading for inactive Theme Style, English/Spanish and missing-language fallback\n";
