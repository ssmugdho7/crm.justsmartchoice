<?php
// Native controller/view tests with synthetic modules; no CRM database or migrations.
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__) . '/application/');
$alerts = [];
function _l($key) { return $key; }
function admin_url($path = '') { return '/admin/' . $path; }
function set_alert($type, $message) { $GLOBALS['alerts'][$type] = $message; }
function redirect($url) { throw new RuntimeException('redirect:' . $url); }
function show_error($message, $status = 500) { throw new RuntimeException($message, $status); }
function log_message($level, $message) {}
function is_admin() { return $GLOBALS['fixture_admin'] ?? true; }
class AdminController { public $app_modules; public $load; public $input; public $output; public $session; public function __construct() {} }
require APPPATH . 'controllers/admin/Mods.php';
function check($pass, $message) { if (!$pass) { throw new RuntimeException('FAIL ' . $message); } echo 'PASS ' . $message . PHP_EOL; }
function module_fixture($name, $installed = '1.0.0', $version = '1.0.1') {
    return ['system_name' => $name, 'installed_version' => $installed, 'activated' => 1,
        'headers' => ['module_name' => $name, 'version' => $version, 'requires_at_least' => '3.0.0']];
}
class ModuleRegistryFixture {
    public $modules = []; public $failCheck = []; public $incompatible = []; public $results = []; public $calls = [];
    public function get($name = null) { return $name === null ? array_values($this->modules) : ($this->modules[$name] ?? null); }
    public function is_minimum_version_requirement_met($name) { if (($this->failCheck[$name] ?? '') === 'compatibility') { throw new Error('private internal detail'); } return !in_array($name, $this->incompatible, true); }
    public function is_database_upgrade_required($name) { if (($this->failCheck[$name] ?? '') === 'database') { throw new Error('private database detail'); } $m = $this->modules[$name]; return $m['installed_version'] !== false && $m['installed_version'] !== $m['headers']['version']; }
    public function upgrade_database($name) { $this->calls[] = $name; $result = array_key_exists($name, $this->results) ? $this->results[$name] : true; if ($result instanceof Throwable) { throw $result; } if ($result === true) { $this->modules[$name]['installed_version'] = $this->modules[$name]['headers']['version']; } return $result; }
}
function controller_fixture() {
    $c = (new ReflectionClass(Mods::class))->newInstanceWithoutConstructor();
    $c->app_modules = new ModuleRegistryFixture();
    $c->input = new class { public $values = []; public $method = 'get'; function method() { return $this->method; } function post($key) { return $this->values[$key] ?? null; } };
    $c->session = new class { public $values = []; function userdata($key) { return $this->values[$key] ?? null; } function set_userdata($key, $value) { $this->values[$key] = $value; } };
    $c->output = new class { public $data; public $status = 200; function set_content_type($type) { return $this; } function set_status_header($status) { $this->status = $status; return $this; } function set_output($json) { $this->data = json_decode($json, true); return $this; } };
    $c->load = new class { public $data; public $view; function view($view, $data) { $this->data = $data; $this->view = $view; } };
    return $c;
}
$c = controller_fixture();
$c->app_modules->modules['false_result'] = module_fixture('false_result');
$c->app_modules->results['false_result'] = false;
$c->input->method = 'post';
$c->input->values = ['module' => 'false_result', 'version' => '1.0.1', 'installed_version' => '1.0.0'];
try { $c->upgrade_all_database(); } catch (RuntimeException $e) { if (strpos($e->getMessage(), 'redirect:') !== 0) { throw $e; } }
check(($c->output->data['status'] ?? '') === 'error' && empty($GLOBALS['alerts']['success']), 'a false migration result must never be reported as success');
$c->index();
check($c->load->data['modules'][0]['maintenance']['errors'] !== [], 'last failed migration is included in Errors for the same module versions');

foreach (['database', 'compatibility'] as $failure) {
    $c = controller_fixture();
    $c->app_modules->modules['broken'] = module_fixture('broken');
    $c->app_modules->failCheck['broken'] = $failure;
    $c->index();
    $status = $c->load->data['modules'][0]['maintenance'];
    check($status['errors'] && !$status['can_upgrade'], $failure . ' check exceptions are visible and block an unsafe upgrade');
    check(strpos(json_encode($status), 'private') === false, 'diagnostics do not disclose raw exception payloads');
}
$c = controller_fixture();
foreach ([module_fixture('pending'), module_fixture('current', '1.0.1'), module_fixture('new', false),
    module_fixture('downgrade', '2.0.0', '1.0.0'), module_fixture('incompatible')] as $module) {
    $c->app_modules->modules[$module['system_name']] = $module;
}
$c->app_modules->incompatible = ['incompatible'];
$c->upgrade_all_database();
check($c->load->view === 'admin/modules/upgrades' && !$c->app_modules->calls, 'GET only reviews upgrades and never runs migrations');
$statuses = array_column($c->load->data['modules'], 'maintenance', 'system_name');
check($statuses['pending']['can_upgrade'] && !$statuses['current']['can_upgrade'] && !$statuses['new']['can_upgrade'], 'only pending installed modules are eligible');
check(!$statuses['downgrade']['can_upgrade'] && $statuses['downgrade']['errors'], 'bulk action cannot silently downgrade a module database');
check(!$statuses['incompatible']['can_upgrade'] && $statuses['incompatible']['errors'], 'minimum CRM version requirement blocks bulk migration');
$c->input->method = 'post';
foreach (['current', 'new', 'downgrade', 'incompatible'] as $name) {
    $m = $c->app_modules->modules[$name];
    $c->input->values = ['module' => $name, 'version' => (string) $m['headers']['version'], 'installed_version' => (string) $m['installed_version']];
    $c->upgrade_all_database();
}
check(!$c->app_modules->calls, 'ineligible POSTs do not invoke the migration library');
$c->input->values = ['module' => 'missing']; $c->upgrade_all_database();
check($c->output->status === 404, 'unknown module is rejected');
$c->input->values = ['module' => 'pending', 'version' => 'stale', 'installed_version' => '1.0.0']; $c->upgrade_all_database();
check($c->output->status === 409 && !$c->app_modules->calls, 'stale review cannot execute a changed module version');
$c->input->values = ['module' => ['pending']]; $c->upgrade_all_database();
check($c->output->status === 404 && !$c->app_modules->calls, 'non-scalar module identifiers cannot reach the registry');
$c->input->values = ['module' => 'pending', 'version' => '1.0.1', 'installed_version' => '1.0.0']; $c->upgrade_all_database();
check($c->output->data['status'] === 'upgraded' && $c->app_modules->calls === ['pending'], 'POST migrates exactly the selected eligible module');
$c->upgrade_all_database();
check($c->output->status === 409 && $c->app_modules->calls === ['pending'], 'repeated stale POST does not rerun the migration');
foreach ([null, '', 'Migration file missing', new Error('private SQL password')] as $result) {
    $c = controller_fixture(); $c->app_modules->modules['bad'] = module_fixture('bad');
    $c->app_modules->results['bad'] = $result; $c->input->method = 'post';
    $c->input->values = ['module' => 'bad', 'version' => '1.0.1', 'installed_version' => '1.0.0']; $c->upgrade_all_database();
    check($c->output->data['status'] === 'error' && strpos(json_encode($c->output->data), 'private SQL') === false, 'migration failure is explicit and exception payload remains private');
    $c->app_modules->results['bad'] = true; $c->upgrade_all_database();
    check($c->output->data['status'] === 'upgraded' && !$c->session->values['module_upgrade_failures'], 'successful explicit retry clears the previous error');
}
$GLOBALS['fixture_admin'] = false;
try { new Mods(); check(false, 'non-admin must be denied'); }
catch (RuntimeException $e) { check($e->getMessage() === 'redirect:/admin/', 'native constructor still denies non-admin staff'); }

// Render the real views with synthetic data, including hostile diagnostic text.
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function init_head() {} function init_tail() {}
function form_open($action, $attrs) { return '<form method="post" action="' . e($action) . '" id="' . e($attrs['id']) . '"><input type="hidden" name="csrf_fixture" value="qa-token">'; }
function form_open_multipart($action, $attrs) { return form_open($action, $attrs); }
function form_close() { return '</form>'; }
function uninstallable_modules() { return []; }
class ModuleViewFixture {
    public $load;
    function __construct() { $this->load = new class($this) { private $renderer; function __construct($renderer) { $this->renderer = $renderer; } function view($name, $data = []) { echo $this->renderer->view($name, $data); } }; }
    function view($name, $data = []) { extract($data); ob_start(); require APPPATH . 'views/' . $name . '.php'; return ob_get_clean(); }
}
$c = controller_fixture(); $m = module_fixture('render'); $m['headers']['description'] = 'Fixture module'; $c->app_modules->modules['render'] = $m;
$c->app_modules->incompatible = ['render']; $c->index(); $data = $c->load->data;
$data['modules'][0]['maintenance']['errors'][] = '<script>fixture_attack()</script>';
$renderer = new ModuleViewFixture();
foreach (['admin/modules/list', 'admin/modules_list'] as $view) {
    $html = $renderer->view($view, $data);
    check(strpos($html, 'data-error="1"') !== false && strpos($html, 'id="sc_module_empty"') !== false, $view . ' renders error rows and empty-state feedback');
    check(strpos($html, '<script>fixture_attack()') === false && strpos($html, '&lt;script&gt;fixture_attack()') !== false, $view . ' escapes diagnostic messages');
    $doc = new DOMDocument(); @$doc->loadHTML($html); $xpath = new DOMXPath($doc);
    check($xpath->query('//*[@id="sc_module_empty" and contains(concat(" ",normalize-space(@class)," ")," alert ")]')->length === 0, 'empty-state feedback is not converted into a spurious global alert toast');
}
$c = controller_fixture(); $c->upgrade_all_database();
$html = $renderer->view('admin/modules/upgrades', $c->load->data);
check(strpos($html, 'No eligible module database upgrades are pending.') !== false && strpos($html, 'disabled') !== false, 'empty review explains there is nothing to upgrade and disables execution');
echo "PASS module maintenance controller and rendering checks\n";
