<?php
// Isolated route and business aggregation checks, without production config or DB.
define('BASEPATH', __DIR__);
function check_hub($ok, $message) { if (!$ok) { throw new RuntimeException($message); } }
function close_setup_menu() {}
function is_staff_member() { return true; }
function is_gdpr() { return false; }
function get_option($key) { return $key === 'default_pipeline' ? 999 : ''; }
function db_prefix() { return 'tbl'; }
function _l($key) { return $key; }
function hooks() { return new class { function add_action(...$args) {} }; }
function get_deals_result($table, $where = null, $type = null) { global $pipelines; return $table === 'tbl_deals_pipelines' ? $pipelines : []; }
class HubDB { public $ready = true; function table_exists($table) { return $this->ready; } }
class HubSession { function userdata($key) { return ''; } function has_userdata($key) { return false; } }
class HubLoad {
    public $helperLoaded = false; public $view; public $data;
    function helper($helper) { $this->helperLoaded = $helper === 'deals/deals'; }
    function model($name) {}
    function view($view, $data = []) { $this->view = $view; $this->data = $data; }
}
class AdminController {
    public $load; public $db; public $session; public $staff_model; public $app_modules; public $input;
    function __construct() {
        $this->app_modules = new class { function is_active($name) { return true; } }; $this->input = new class { function get($name) { return null; } };
        $this->load = new HubLoad; $this->db = new HubDB; $this->session = new HubSession;
        $this->staff_model = new class { function get(...$args) { return []; } };
    }
}
require __DIR__ . '/../modules/deals/controllers/Deals.php';
$route = new Deals;
check_hub($route->load->helperLoaded, 'Direct Deals route must explicitly load its helper');
$route->db->ready = false;
$route->index();
check_hub($route->load->view === 'setup_required', 'Missing schema must produce setup UI, no table query or DDL');
$route->db->ready = true;
$pipelines = [];
set_error_handler(function ($severity, $message) { throw new RuntimeException($message); });
$route->index();
check_hub($route->load->view === 'setup_required', 'Empty pipeline must not dereference row zero');
$pipelines = [['pipeline_id' => 4]];
$route->index();
check_hub($route->load->view === 'all_deals' && $route->load->data['default_pipeline'] === 4, 'Stale default must select an existing pipeline');
restore_error_handler();
require __DIR__ . '/../modules/commission/helpers/commission_helper.php';
check_hub(saleshub_commission_leaders([]) === [], 'Empty report must not fabricate leaders');
$leaders = saleshub_commission_leaders([
 ['staffid'=>1,'is_client'=>0,'amount'=>10], ['staffid'=>1,'is_client'=>0,'amount'=>20],
 ['staffid'=>1,'is_client'=>1,'amount'=>40], ['staffid'=>2,'is_client'=>0,'amount'=>15],
 ['staffid'=>3,'is_client'=>0,'amount'=>5],
]);
check_hub(count($leaders) === 3 && $leaders[0]['is_client'] === 1 && $leaders[0]['amount'] === 40.0, 'Rank filtered commission sums, separating client and staff identities');
check_hub($leaders[1]['amount'] === 30.0, 'Aggregate repeated recipient rows without counting invoice volume as commission');
echo "Sales Hub route and ranking regression checks passed\n";
class HubHistoryDB {
    public $linked = ''; public $deleted = false;
    function table_exists($table) { return true; }
    function where(...$args) { return $this; }
    function count_all_results($table) { return $table === $this->linked ? 1 : 0; }
    function delete($table) { $this->deleted = true; return $this; }
    function affected_rows() { return 1; }
}
class App_Model { public $db; function __construct() { $this->db = new HubHistoryDB; } }
function log_activity($message) {}
require __DIR__ . '/../modules/sales_center/models/Sales_center_model.php';
foreach (['sales_center_contracts', 'sales_center_commissions', 'sales_center_project_links'] as $history) {
    $model = new Sales_center_model;
    $model->db->linked = db_prefix() . $history;
    check_hub($model->delete(7) === false && !$model->db->deleted, 'Retain representative linked to ' . $history);
}
$model = new Sales_center_model;
check_hub($model->delete(8) === true && $model->db->deleted, 'Unlinked representative remains deletable');
echo "Sales representative history retention checks passed\n";
