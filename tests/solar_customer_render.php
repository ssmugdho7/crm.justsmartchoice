<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
$loggedIn = true;
$GLOBALS['contact'] = (object) ['userid' => 42, 'email' => 'fixture@example.test'];
function is_client_logged_in() { return $GLOBALS['loggedIn']; }
function site_url($path) { return 'https://portal.example/' . $path; }
function db_prefix() { return 'tbl'; }
function _l($key) { return $key; }
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { throw new RuntimeException($url); }
class ClientsController {
    public $db, $payload, $rendered, $layouts = 0;
    function __construct() {
        $this->db = new class {
            public $calls = [];
            function __call($name, $args) { $this->calls[] = [$name, $args]; return $this; }
            function result_array() { return [['address' => '<script>unsafe</script>', 'system_kw' => 4, 'panel_count' => 10, 'annual_production_kwh' => 5000, 'public_token' => 'token/?']]; }
        };
    }
    function data($data) { $this->payload = $data; }
    function view($view) { $this->rendered = $view; }
    function layout() { $this->layouts++; }
}
require dirname(__DIR__) . '/modules/solar_pro/controllers/Solar_customer.php';
$controller = new Solar_customer();
$controller->index();
if ($controller->rendered !== 'public/my' || $controller->layouts !== 1) throw new RuntimeException('Shared customer layout is required');
foreach ([['where', ['client_id', 42]], ['or_where', ['email', 'fixture@example.test']], ['group_start', []], ['group_end', []]] as $call) {
    if (!in_array($call, $controller->db->calls, true)) throw new RuntimeException('Customer scope changed');
}
extract($controller->payload);
ob_start(); require dirname(__DIR__) . '/modules/solar_pro/views/public/my.php'; $html = ob_get_clean();
if (strpos($html, '<html') !== false || strpos($html, '<script>unsafe') !== false || strpos($html, 'token%2F%3F') === false) throw new RuntimeException('Embedded Solar view or escaping is invalid');
$loggedIn = false;
try { $controller->index(); throw new LogicException('Guests must redirect'); }
catch (RuntimeException $error) { if ($error->getMessage() !== site_url('authentication/login')) throw $error; }
echo "PASS Solar customer layout, account/email scope, escaped report links and guest redirect\n";
