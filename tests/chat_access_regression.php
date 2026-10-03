<?php
// CLI-only: run native module/menu code with isolated permission fixtures, no database writes.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
define('FCPATH', dirname(__DIR__) . '/');
$root = dirname(__DIR__);
$options = ['pusher_chat_enabled' => '1', 'aside_menu_active' => '{}'];
$permissions = [];
$administrator = false;
$registeredCapabilities = [];
function get_option($name) { return $GLOBALS['options'][$name] ?? ''; }
function db_prefix() { return 'tbl'; }
function get_staff_user_id() { return 7; }
function staff_can($capability, $feature = null, $id = '') {
    return $GLOBALS['administrator'] || in_array($capability, $GLOBALS['permissions'][$feature] ?? [], true);
}
function _l($key, ...$args) { return $key; }
function admin_url($path = '') { return '/admin/' . $path; }
function module_dir_path($name, $path = '') { return FCPATH . 'modules/' . $name . '/' . $path; }
function register_activation_hook(...$args) {}
function register_language_files(...$args) {}
function register_staff_capabilities($feature, $capabilities, $label) {
    $GLOBALS['registeredCapabilities'][$feature] = $capabilities;
}
function app_fill_empty_common_attributes($item) { return $item + ['position' => 0, 'children' => []]; }
function app_sort_by_position($items, $children = false) {
    uasort($items, static function ($a, $b) { return ($a['position'] ?? 0) <=> ($b['position'] ?? 0); });
    return $items;
}
class ChatAccessHooks {
    public $actions = [];
    public function add_action($hook, $callback, ...$rest) { $this->actions[$hook][] = $callback; }
    public function apply_filters($hook, $value, ...$rest) { return $value; }
    public function run($hook) {
        foreach ($this->actions[$hook] ?? [] as $callback) {
            if (in_array($callback, ['prchat_register_admin_menu', 'chat_register_staff_permissions'], true)) {
                $callback();
            }
        }
    }
}
class ChatAccessLoader { public function helper(...$args) {} }
$hooksFixture = new ChatAccessHooks();
function hooks() { return $GLOBALS['hooksFixture']; }
$ci = (object) ['load' => new ChatAccessLoader(), 'lang' => new class {
    public function line($name, $log = false) { return false; }
}];
function &get_instance() { return $GLOBALS['ci']; }
require $root . '/application/libraries/App_menu.php';
require $root . '/application/services/utilities/Arr.php';
require $root . '/modules/menu_setup/helpers/menu_setup_helper.php';
$ci->app_menu = new App_menu();
require $root . '/modules/prchat/prchat.php';
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
check(!isset($ci->app_menu->get_sidebar_menu_items()['prchat']), 'Menu must not be evaluated before admin_init');
$cases = [
    'no rights' => [[], false, false, false],
    'view' => [['prchat' => ['view']], false, true, false],
    'view and settings' => [['prchat' => ['view'], 'settings' => ['view']], false, true, false],
    'chatbot support only' => [['prchat' => ['chatbot_support']], false, false, true],
    'chatbot manage only' => [['prchat' => ['chatbot_manage']], false, false, true],
    'create only' => [['prchat' => ['create']], false, false, false],
    'edit only' => [['prchat' => ['edit']], false, false, false],
    'delete only' => [['prchat' => ['delete']], false, false, false],
    'ai assist only' => [['prchat' => ['ai_assist']], false, false, false],
    'own only' => [['prchat' => ['view_own']], false, true, false],
    'admin' => [[], true, true, true],
];
foreach ($cases as $name => [$permissions, $administrator, $expectChat, $expectBot]) {
    foreach (['1', '0'] as $enabled) {
        $options['pusher_chat_enabled'] = $enabled;
        $ci->app_menu = new App_menu();
        $hooksFixture->run('admin_init');
        $items = $ci->app_menu->get_sidebar_menu_items();
        check(isset($items['prchat']) === ($expectChat && $enabled === '1'), "$name: chat visibility");
        check(isset($items['prchat-chatbot']) === $expectBot, "$name: chatbot visibility independent of staff-chat switch");
        if (isset($items['prchat'])) {
            // Use actual menu_setup transformation, including a saved parent without saved children.
            $options['aside_menu_active'] = '{"prchat":{"id":"prchat","disabled":"false","position":"10"}}';
            $items = app_admin_sidebar_custom_options(app_admin_sidebar_custom_positions($items));
            $children = array_column($items['prchat']['children'], null, 'slug');
            check(isset($children['prchat-conversations']), "$name: saved menu must retain new conversations link");
            check($children['prchat-conversations']['href'] === '/admin/prchat/Prchat_Controller/chat_full_view', 'Native chat destination');
            check(isset($children['prchat-settings']) === ($administrator || in_array('view', $permissions['settings'] ?? [], true)), 'Settings remain restricted');
            $options['aside_menu_active'] = '{"prchat":{"id":"prchat","disabled":"true","position":"10"}}';
            check(!isset(app_admin_sidebar_custom_options($items)['prchat']), 'Respect deliberate menu hiding');
        }
        if (isset($items['prchat-chatbot'])) {
            $children = array_column($items['prchat-chatbot']['children'], null, 'slug');
            check(isset($children['chatbot-support']), 'Support destination present');
            check(isset($children['chatbot-settings']) === ($administrator || in_array('chatbot_manage', $permissions['prchat'] ?? [], true)), 'Chatbot manage remains separate');
        }
    }
}
check(isset($registeredCapabilities['prchat']['before']), 'Access requirements visible beside permissions');
check(strpos($registeredCapabilities['prchat']['before'], 'View own') !== false, 'Missing translations receive real English explanation');
// Verify call denial executes before any Pusher initialization; no external services are called.
class ChatAccessOutput {
    public $status;
    public $payload;
    public function set_status_header($status) { $this->status = $status; return $this; }
    public function set_content_type(...$args) { return $this; }
    public function set_output($payload) { $this->payload = $payload; return $this; }
    public function _display() { throw new RuntimeException('Call denied ' . $this->status); }
}
class AdminController {
    public $router;
    public $input;
    public $output;
    public $app_modules;
    public $load;
    public function __construct() {
        $this->router = new class { public function fetch_method() { return $GLOBALS['chatAccessMethod'] ?? 'users'; } };
        $this->input = new class { public function get_post($key) { return null; } public function get($key) { return null; } public function post($key) { return null; } };
        $this->output = new ChatAccessOutput();
        $this->app_modules = new class { public function is_active($name) { return true; } };
        $this->load = new class {
            public $libraries = [];
            public function model(...$args) {}
            public function helper(...$args) {}
            public function library($name) { $this->libraries[] = $name; }
        };
    }
}
function log_message(...$args) {}
require $root . '/modules/prchat/controllers/Calls_Controller.php';
foreach ($cases as $name => [$permissions, $administrator, $expectChat, $expectBot]) {
    $options['pusher_chat_enabled'] = '1';
    try {
        $controller = new Calls_Controller();
        check($expectChat, "$name: unprivileged call access was not denied");
        check($controller->load->libraries === ['App_pusher'], "$name: authorized call bootstrap retained");
    } catch (RuntimeException $e) {
        check(!$expectChat && $e->getMessage() === 'Call denied 403', $e->getMessage());
    }
}
echo "PASS 22 menu cases and 11 call access cases; native saved-menu transforms and chatbot restrictions preserved\n";

function redirect($url) { throw new RuntimeException('Chat denied redirect'); }
require $root . '/modules/prchat/controllers/Prchat_Controller.php';
foreach ($cases as $name => [$permissions, $administrator, $expectChat, $expectBot]) {
    $options['pusher_chat_enabled'] = '1';
    $GLOBALS['chatAccessMethod'] = 'users';
    try { new Prchat_Controller(); check($expectChat, "$name: backend read guard"); }
    catch (RuntimeException $e) { check(!$expectChat && $e->getMessage() === 'Chat denied redirect', $e->getMessage()); }
    $GLOBALS['chatAccessMethod'] = 'get_call_token';
    try { new Prchat_Controller(); check($expectChat, "$name: token guard"); }
    catch (RuntimeException $e) { check(!$expectChat && $e->getMessage() === 'Call denied 403', $e->getMessage()); }
}
echo "PASS 11 backend chat bootstrap cases and 11 call-token cases\n";

class ClientsController extends AdminController {}
function is_client_logged_in() { return $GLOBALS['fixtureClientLoggedIn'] ?? false; }
function get_contact_user_id() { return is_client_logged_in() ? 7 : 0; }
require $root . '/modules/prchat/controllers/ClientCalls_Controller.php';
$fixtureClientLoggedIn = true;
$options['chat_client_enabled'] = '1';
$options['chat_client_calls_enabled'] = '1';
$GLOBALS['chatAccessMethod'] = 'get_call_token';
new ClientCalls_Controller();
foreach (['login', 'chat', 'calls'] as $disabled) {
    $fixtureClientLoggedIn = $disabled !== 'login';
    $options['chat_client_enabled'] = $disabled === 'chat' ? '0' : '1';
    $options['chat_client_calls_enabled'] = $disabled === 'calls' ? '0' : '1';
    try { new ClientCalls_Controller(); throw new RuntimeException('Client gate missing'); }
    catch (RuntimeException $e) { check($e->getMessage() === 'Call denied 403', $e->getMessage()); }
}
$fixtureClientLoggedIn = true;
$client = (new ReflectionClass(ClientCalls_Controller::class))->newInstanceWithoutConstructor();
$client->input = new class { public $channel; public function post($key) { return $key === 'channel_name' ? $this->channel : 'fixture-socket'; } };
$pusher = new class { public $signed=[]; public function socket_auth($channel,$socket) { $this->signed[]=$channel; return '{"auth":"fixture"}'; } };
$property = new ReflectionProperty(ClientCalls_Controller::class,'pusher'); $property->setAccessible(true); $property->setValue($client,$pusher);
$client->input->channel = CHAT_CALLS_CLIENT_CHANNEL_PREFIX . '7';
ob_start(); $client->pusherAuth(); $payload=ob_get_clean();
check(json_decode($payload,true)['auth']==='fixture','Own private call channel signed');
$client->input->channel = CHAT_CALLS_CLIENT_CHANNEL_PREFIX . '8';
ob_start(); $client->pusherAuth(); $payload=ob_get_clean();
check(strpos($payload, 'Unauthorized channel') !== false && count($pusher->signed)===1,'Another contact call channel denied');
check(strpos(CHAT_CALLS_STAFF_CHANNEL_PREFIX,'private-')===0 && strpos(CHAT_CALLS_CLIENT_CHANNEL_PREFIX,'private-')===0,'Call signaling is private');
echo "PASS client call login/settings gates and private channel ownership\n";
