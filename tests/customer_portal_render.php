<?php
// Isolated view regression checks; never bootstrap the app or connect to a database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
$scenario = $argv[1] ?? 'full';
if (!in_array($scenario, ['full', 'restricted', 'empty'], true)) { throw new RuntimeException('Unknown scenario'); }
$contact = (object) ['firstname' => '<img src=x onerror=alert(1)>'];
$project_statuses = [['id' => 2, 'name' => 'In Progress', 'color' => '#26835f']];
$countQueries = [];
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function _l($key) { return $key; }
function site_url($path = '') { return 'https://portal.example/' . $path; }
function current_full_url() { global $scenario; return site_url($scenario === 'full' ? 'clients/project/2' : 'clients/projects/2'); }
function has_contact_permission($permission) { global $scenario; return $scenario !== 'restricted' || $permission === 'support'; }
function get_client_user_id() { return 42; }
function get_contact_user_id() { return 7; }
function can_logged_in_contact_view_all_tickets() { return false; }
function db_prefix() { return 'tbl'; }
function get_option($key) { return $key === 'exclude_invoice_from_client_area_with_draft_status' ? '1' : ''; }
function is_knowledge_base_viewable($strict) { return true; }
function _d($date) { return $date; }
function _attributes_to_string($attributes) { return ''; }
function get_project_status_by_id($id) { return ['name' => 'In Progress']; }
function total_rows($table, $where) { global $scenario, $countQueries; $countQueries[] = [$table, $where]; return $scenario === 'empty' ? 0 : 1; }
function hooks() { return new class { function do_action($name) {} }; }
class Invoices_model { const STATUS_DRAFT = 6; }
class PortalTestQuery {
    public $scope = null;
    public $limit = null;
    public $reads = 0;
    function select($columns) { return $this; }
    function where($key, $value) { $this->scope = [$key, $value]; return $this; }
    function order_by($key, $direction) { return $this; }
    function limit($limit) { $this->limit = $limit; return $this; }
    function get($table) {
        if ($this->scope !== ['clientid', 42] || $this->limit !== 3 || $table !== 'tblprojects') throw new RuntimeException('Unsafe project query');
        $this->reads++;
        return $this;
    }
    function result_array() { global $scenario; return $scenario === 'empty' ? [] : [['id' => 3, 'name' => '<script>unsafe</script>', 'status' => 2, 'deadline' => null]]; }
}
function get_instance() {
    static $ci;
    if (!$ci) $ci = (object) ['db' => new PortalTestQuery(), 'projects_model' => new class { function calc_progress($id) { return 130; } }];
    return $ci;
}
function get_template_part($name) {
    global $project_statuses;
    include dirname(__DIR__) . '/application/views/themes/smartchoice/template_parts/' . $name . '.php';
}
function verify($condition, $message) { if (!$condition) throw new RuntimeException($message); }
ob_start();
include dirname(__DIR__) . '/application/views/themes/smartchoice/views/home.php';
$html = ob_get_clean();
verify(strpos($html, '<img src=x') === false && strpos($html, '&lt;img') !== false, 'Customer name must be escaped');
verify(strpos($html, '<script>unsafe') === false, 'Project name must be escaped');
foreach ($countQueries as [$table, $where]) {
    if ($table === 'tblcontracts') verify($where['not_visible_to_client'] === 0 && $where['trash'] === 0 && $where['client'] === 42, 'Hidden contract scope');
    if ($table === 'tbltickets') verify($where['contactid'] === 7 && $where['userid'] === 42, 'Contact ticket scope');
    if ($table === 'tblinvoices' && is_array($where) && !isset($where['status'])) verify($where['status !='] === 6 && $where['clientid'] === 42, 'Invoice draft scope');
}
if ($scenario === 'restricted') {
    verify(get_instance()->db->reads === 0, 'Restricted contact must not query projects');
    foreach (['clients/projects', 'clients/invoices', 'clients/contracts'] as $path) verify(strpos($html, $path) === false, 'Restricted dashboard link: ' . $path);
} elseif ($scenario === 'empty') {
    verify(strpos($html, 'No projects yet') !== false, 'Missing empty project state');
} else {
    verify(strpos($html, 'aria-valuenow="100"') !== false, 'Project progress must be clamped');
}
$menu = [];
foreach (['projects', 'invoices', 'contracts'] as $permission) {
    if (has_contact_permission($permission)) $menu[$permission] = ['name' => $permission, 'href' => site_url('clients/' . $permission)];
}
ob_start();
include dirname(__DIR__) . '/application/views/themes/smartchoice/template_parts/customer_sidebar.php';
$sidebar = ob_get_clean();
if ($scenario === 'restricted') verify(strpos($sidebar, 'Finance &amp; Legal') === false, 'Empty finance group must be omitted');
else verify(strpos($sidebar, 'aria-current="page"') !== false, 'Project status route must retain active sidebar link');
verify(strpos($sidebar, 'aria-label="dashboard_string"') !== false, 'Collapsed home link needs a name');
verify(strpos($sidebar, 'aria-label="Customer shortcuts"') !== false, 'Missing mobile shortcuts');
if ($scenario === 'restricted') {
    verify(strpos($sidebar, 'aria-label="Work"') === false && strpos($sidebar, 'aria-label="Money"') === false, 'Restricted mobile links must be omitted');
    verify(strpos($sidebar, 'aria-label="Support"') !== false, 'Support remains available');
}
echo 'PASS: ' . $scenario . " (escaping, permissions, customer scope, navigation)\n";
