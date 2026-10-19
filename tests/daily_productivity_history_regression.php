<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
date_default_timezone_set('UTC');
$checks = 0;
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } $GLOBALS['checks']++; }
function get_staff_user_id() { return 17; }
function has_permission($module, $unused, $permission) { return in_array($permission, $GLOBALS['permissions'], true); }
function to_sql_date($date) { if (strpos($date, '/') !== false) { return DateTime::createFromFormat('m/d/Y', $date)->format('Y-m-d'); } return $date; }
function smart_daily_productivity_label($key) { return $key; }
function admin_url($path) { return $path; }
function set_alert($type, $message) { $GLOBALS['alertType'] = $type; }
function redirect($url) { throw new RedirectFixture($url); }
function access_denied($message) { throw new RuntimeException('denied'); }
class RedirectFixture extends RuntimeException {}
class AdminController {}
class InputFixture {
    public $query = [], $body = [];
    function get($key, $clean = true) { return $this->query[$key] ?? null; }
    function post($key = null, $clean = true) { return $key === null ? $this->body : ($this->body[$key] ?? null); }
}
class LoaderFixture { public $data; function view($view, $data) { $this->data = $data; } }
class StaffFixture { function get(...$args) { return []; } }
class EntriesFixture { public $filters, $saved; public $saveResult = 1; function get_entries($filters) { $this->filters = $filters; return []; } function add_entry($data) { $this->saved = $data; return $this->saveResult; } }
require dirname(__DIR__).'/modules/smart_daily_productivity/controllers/Smart_daily_productivity.php';
#[AllowDynamicProperties]
class ControllerFixture extends Smart_daily_productivity { function __construct() {} }
$c = new ControllerFixture(); $c->input = new InputFixture(); $c->load = new LoaderFixture(); $c->staff_model = new StaffFixture(); $c->Smart_daily_productivity_model = new EntriesFixture();
$permissions = ['view_own', 'create'];
$c->my_day();
check($c->Smart_daily_productivity_model->filters === ['staff_id'=>17,'date_from'=>null,'date_to'=>null], 'Default history includes earlier reports and remains scoped to current staff');
$c->input->query = ['staff_id'=>99,'date'=>'10/16/2026']; $c->my_day();
check($c->Smart_daily_productivity_model->filters === ['staff_id'=>17,'date_from'=>'2026-10-16','date_to'=>'2026-10-16'], 'Explicit localized date filters own reports; staff override ignored');
$permissions[] = 'view'; $c->my_day();
check($c->Smart_daily_productivity_model->filters['staff_id'] === 99, 'Global viewer can select another staff member');
$c->input->query = []; $c->input->body = ['entry_date'=>'10/16/2026','title'=>'Fixture','description'=>'Fixture','category'=>'Project Task'];
foreach ([1=>'success', 0=>'danger'] as $result=>$expected) {
    $c->Smart_daily_productivity_model->saveResult = $result;
    try { $c->my_day(); throw new RuntimeException('Expected redirect'); } catch (RedirectFixture $e) { check(strpos($e->getMessage(),'date=10%2F16%2F2026')!==false, 'Save returns to entry date'); }
    check($alertType === $expected, 'Failed save does not report success');
}
$permissions = ['view_own'];
try { $c->my_day(); throw new RuntimeException('Expected denial'); } catch (RuntimeException $e) { check($e->getMessage()==='denied', 'Create permission enforced'); }
class App_Model {}
require dirname(__DIR__).'/modules/smart_daily_productivity/models/Smart_daily_productivity_model.php';
$model = (new ReflectionClass('Smart_daily_productivity_model'))->newInstanceWithoutConstructor();
$clean = new ReflectionMethod($model, 'clean_date');
check($clean->invoke($model, '10/16/2026') === '2026-10-16', 'Localized entry date normalized');
check($clean->invoke($model, '2026-10-16') === '2026-10-16', 'SQL entry date preserved');
echo "Daily productivity history: {$checks} checks passed.\n";
