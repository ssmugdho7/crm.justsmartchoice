<?php
// CLI fixtures only: switches never touch the application's actual index.php or database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
define('ENVIRONMENT', $argv[1] ?? 'production');
$fixture = sys_get_temp_dir() . '/crm-debug-runtime-' . bin2hex(random_bytes(8));
mkdir($fixture, 0700);
define('FCPATH', $fixture . '/');
$options = ['debug_mode_enabled' => '1', 'debug_mode_show_admin_banner' => '1'];
$admin = true;
$alerts = [];
$assertions = 0;
class FixtureHooks {
    public $filters = [];
    public function add_action($name, $callback) {}
    public function add_filter($name, $callback) { $this->filters[$name] = $callback; }
}
function hooks() { static $hooks; return $hooks ?? ($hooks = new FixtureHooks()); }
function register_activation_hook($name, $callback) {}
function register_deactivation_hook($name, $callback) {}
function register_language_files($name, $files) {}
function get_option($name) { global $options; return $options[$name] ?? ''; }
function update_option($name, $value) { global $options; $options[$name] = $value; return true; }
function is_admin() { global $admin; return $admin; }
function has_permission($feature, $staff, $permission) { return true; }
function set_alert($type, $message) { global $alerts; $alerts[] = [$type, $message]; }
function admin_url($route) { return '/admin/' . $route; }
class FixtureRedirect extends RuntimeException {}
class FixtureDenied extends RuntimeException {}
function redirect($route) { throw new FixtureRedirect($route); }
function access_denied($feature) { throw new FixtureDenied($feature); }
class AdminController { public function __construct() {} }
function verify($condition, $message) {
    global $assertions;
    if (!$condition) { throw new RuntimeException($message); }
    $assertions++;
}
function toggle($controller, $mode) {
    global $alerts;
    $alerts = [];
    try { $controller->toggle_debug($mode); }
    catch (FixtureRedirect $e) { verify($e->getMessage() === '/admin/debug_mode', 'Return to utility screen'); }
}
$root = dirname(__DIR__);
require $root . '/modules/debug_mode/debug_mode.php';
require $root . '/modules/debug_mode/controllers/Debug_mode.php';
$index = FCPATH . 'index.php';
$modern = "<?php\ndefined('ENVIRONMENT') || define('ENVIRONMENT', \$_SERVER['CI_ENV'] ?? 'production');\n// preserve everything else\n";
try {
    verify(isset(hooks()->filters['before_settings_updated']), 'Native settings saves must use the switch validation filter');
    verify(is_string(debug_mode_environment_source(file_get_contents($root . '/index.php'), 'production')), 'Current application entry point must be supported');
    foreach (['production', 'development', 'testing'] as $old) {
        foreach (["define('ENVIRONMENT', '$old');", 'define("ENVIRONMENT", "' . $old . '");', "defined('ENVIRONMENT') || define('ENVIRONMENT', \$_SERVER['CI_ENV'] ?? '$old');"] as $declaration) {
            $source = "<?php\r\n/* define('ENVIRONMENT', 'development'); */\r\n$declaration\r\n\$unrelated = 'keep me';\r\n";
            foreach (['production', 'development'] as $desired) {
                $result = debug_mode_environment_source($source, $desired);
                verify(is_string($result), 'Legacy and CI_ENV declarations must be supported');
                verify(strpos($result, "/* define('ENVIRONMENT', 'development'); */") !== false, 'Do not rewrite comments');
                verify(strpos($result, "\$unrelated = 'keep me';\r\n") !== false, 'Preserve unrelated code and CRLF');
                file_put_contents($index, $result);
                $runtime = shell_exec(escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg("require " . var_export($index, true) . "; echo ENVIRONMENT;"));
                verify($runtime === $desired, 'Rewritten declaration must actually run in the requested mode');
            }
        }
    }
    foreach ([
        "<?php // define('ENVIRONMENT', 'production');\n",
        '<?php $example = "define(\'ENVIRONMENT\', \'production\');";',
        "<?php define('ENVIRONMENT', getenv('CI_ENV'));",
        "<?php define('ENVIRONMENT', 'production'); define('ENVIRONMENT', 'testing');",
        "<?php \$obj->define('ENVIRONMENT', 'production');",
        "<?php Debug::define('ENVIRONMENT', 'production');",
    ] as $unsupported) {
        file_put_contents($index, $unsupported);
        verify(debug_mode_enable_environment('production') === false, 'Unsupported and ambiguous declarations must fail safely');
        verify(file_get_contents($index) === $unsupported, 'Failure must leave entry point unchanged');
    }
    verify(debug_mode_environment_source($modern, 'invalid') === false, 'Reject unknown requested environments');
    file_put_contents($index, $modern);
    chmod($index, 0640);
    verify(debug_mode_enable_environment('production'), 'Disable supported CI_ENV fallback');
    $productionSource = file_get_contents($index);
    verify((fileperms($index) & 0777) === 0640, 'Atomic replacement must preserve entry point permissions');
    verify(debug_mode_enable_environment('production'), 'An already disabled switch must succeed');
    verify(file_get_contents($index) === $productionSource, 'Idempotent switch must preserve contents');
    verify(glob($fixture . '/.debug-env-*') === [], 'No temporary PHP files may be left behind');
    verify(debug_mode_is_enabled() === (ENVIRONMENT === 'development'), 'Runtime, not saved setting, determines status');
    foreach (['0', '1'] as $saved) {
        $options['debug_mode_enabled'] = $saved;
        ob_start(); debug_mode_smartchoice_footer_notice(); $notice = ob_get_clean();
        verify((strpos($notice, 'Debug Mode is active') !== false) === (ENVIRONMENT === 'development'), 'Notice must reflect runtime despite stale database value');
    }
    $options['debug_mode_show_admin_banner'] = '0';
    ob_start(); debug_mode_smartchoice_footer_notice(); $notice = ob_get_clean();
    verify($notice === '', 'Preserve banner visibility preference');
    $controller = new Debug_mode();
    $options['debug_mode_enabled'] = '0';
    toggle($controller, 'on');
    verify($options['debug_mode_enabled'] === '1' && $alerts[0][0] === 'success', 'Successful activation saves flag and reports success');
    verify(strpos(file_get_contents($index), "define('ENVIRONMENT', 'development')") !== false, 'Controller activates actual environment');
    toggle($controller, 'off');
    verify($options['debug_mode_enabled'] === '0' && $alerts[0][0] === 'success', 'Successful deactivation saves flag and reports success');
    toggle($controller, 'unknown');
    verify($options['debug_mode_enabled'] === '0' && $alerts[0][0] === 'danger', 'Invalid action must not change saved state');
    unlink($index);
    toggle($controller, 'on');
    verify($options['debug_mode_enabled'] === '0' && $alerts[0][0] === 'danger', 'Failed activation must not claim success or set flag');
    $options['debug_mode_enabled'] = '1';
    toggle($controller, 'off');
    verify($options['debug_mode_enabled'] === '1' && $alerts[0][0] === 'danger', 'Failed deactivation must retain saved state');
    debug_mode_smartchoice_deactivation_hook();
    verify($options['debug_mode_enabled'] === '1', 'Module deactivation must not falsely mark an unsuccessful switch as off');
    $data = ['settings' => ['debug_mode_enabled' => '0', 'unrelated' => 'keep']];
    $result = debug_mode_smartchoice_before_settings_updated($data);
    verify($result === ['settings' => ['unrelated' => 'keep']], 'Failed native settings switch must omit only debug flag');
    verify(debug_mode_smartchoice_before_settings_updated(['settings' => ['unrelated' => 'keep']]) === ['settings' => ['unrelated' => 'keep']], 'Other settings saves must not change environment');
    file_put_contents($index, $modern);
    verify(debug_mode_smartchoice_before_settings_updated($data) === $data, 'Unchecked checkbox applies production and saves zero');
    $admin = false;
    try { $controller->toggle_debug('on'); verify(false, 'Non-admin must be denied'); }
    catch (FixtureDenied $e) { verify(true, 'Admin-only toggle guard preserved'); }
    $before = file_get_contents($index);
    $result = debug_mode_smartchoice_before_settings_updated(['settings' => ['debug_mode_enabled' => '1', 'unrelated' => 'keep']]);
    verify(!isset($result['settings']['debug_mode_enabled']) && $result['settings']['unrelated'] === 'keep', 'Settings-edit permission alone must not grant environment control');
    verify(file_get_contents($index) === $before, 'Non-admin settings save must leave environment untouched');
    $admin = true;
    foreach (['unexpected', ['1']] as $invalid) {
        $result = debug_mode_smartchoice_before_settings_updated(['settings' => ['debug_mode_enabled' => $invalid]]);
        verify(!isset($result['settings']['debug_mode_enabled']) && file_get_contents($index) === $before, 'Reject malformed settings without changing entry point');
    }
    // A writable entry point in a read-only directory must fail without a partial rewrite.
    chmod($fixture, 0500);
    verify(debug_mode_enable_environment('development') === false, 'Atomic replacement must fail safely when directory is not writable');
    verify(file_get_contents($index) === $before, 'Write failure must preserve original PHP');
    chmod($fixture, 0700);
    foreach (['settings.php', 'debug_mode/settings.php'] as $view) {
        $source = file_get_contents($root . '/modules/debug_mode/views/' . $view);
        verify(strpos($source, 'name="settings[debug_mode_enabled]" value="0"') !== false, 'Unchecked native checkbox must submit off');
        verify(strpos($source, "debug_mode_is_enabled() ? 'checked'") !== false, 'Checkbox status must reflect actual runtime');
    }
    echo "PASS $assertions assertions (environment=" . ENVIRONMENT . ")\n";
} finally {
    chmod($fixture, 0700);
    foreach (glob($fixture . '/{*,.*}', GLOB_BRACE) as $file) { if (is_file($file)) { unlink($file); } }
    rmdir($fixture);
}
