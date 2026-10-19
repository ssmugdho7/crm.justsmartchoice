<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
$checks = 0;
function check_edit($condition, $message) { if (!$condition) { throw new RuntimeException($message); } $GLOBALS['checks']++; }
function get_staff_user_id() { return 17; }
function has_permission($module, $unused, $permission) { return in_array($permission, $GLOBALS['permissions'], true); }
function smart_daily_productivity_label($key) { return $key; }
function admin_url($path) { return $path; }
function set_alert($type, $message) { $GLOBALS['alertType'] = $type; }
function redirect($url) { throw new RedirectEditFixture($url); }
function access_denied($message) { throw new RuntimeException('denied'); }
class RedirectEditFixture extends RuntimeException {}
class AdminController {}
class InputEditFixture {
    public $body = [];
    function post($key = null, $clean = true) { return $key === null ? $this->body : ($this->body[$key] ?? null); }
}
class EntryEditFixture {
    public $entry, $updated = [], $updateResult = true;
    function get_entry($id) { return $this->entry; }
    function update_entry($id, $data) { $this->updated[] = [$id, $data]; return $this->updateResult; }
}
require dirname(__DIR__).'/modules/smart_daily_productivity/controllers/Smart_daily_productivity.php';
#[AllowDynamicProperties]
class EditControllerFixture extends Smart_daily_productivity { function __construct() {} }
$controller = new EditControllerFixture();
$controller->input = new InputEditFixture();
$controller->Smart_daily_productivity_model = new EntryEditFixture();
$controller->Smart_daily_productivity_model->entry = (object)['staff_id'=>17, 'source_type'=>'manual'];
$controller->input->body = ['entry_date'=>'2026-10-16','title'=>'Updated title','description'=>'Updated notes','minutes_spent'=>'45','category'=>'Project Task'];
$permissions = ['edit', 'view_own'];
try { $controller->update_entry(4); throw new RuntimeException('Expected redirect'); }
catch (RedirectEditFixture $redirect) {
    check_edit(strpos($redirect->getMessage(), 'staff_id=17') !== false && strpos($redirect->getMessage(), 'date=2026-10-16') !== false, 'Successful edit returns to entry date and staff');
}
check_edit(count($controller->Smart_daily_productivity_model->updated) === 1, 'Own manual entry is updated');
check_edit($controller->Smart_daily_productivity_model->updated[0][1]['minutes_spent'] === 45, 'Submitted entry fields are passed to update');

$controller->Smart_daily_productivity_model->entry = (object)['staff_id'=>18, 'source_type'=>'manual'];
$permissions = ['edit', 'view_own'];
try { $controller->update_entry(5); throw new RuntimeException('Expected denial'); }
catch (RuntimeException $error) { check_edit($error->getMessage() === 'denied', 'Own-only editor cannot change another staff member entry'); }
check_edit(count($controller->Smart_daily_productivity_model->updated) === 1, 'Denied cross-staff edit does not update');

$permissions = ['edit', 'view'];
try { $controller->update_entry(5); throw new RuntimeException('Expected redirect'); }
catch (RedirectEditFixture $redirect) { check_edit(strpos($redirect->getMessage(), 'staff_id=18') !== false, 'Global editor can update another staff member entry'); }

$controller->Smart_daily_productivity_model->entry = (object)['staff_id'=>17, 'source_type'=>'task'];
try { $controller->update_entry(6); throw new RuntimeException('Expected redirect'); }
catch (RedirectEditFixture $redirect) { check_edit($GLOBALS['alertType'] === 'danger', 'System-generated entry cannot be edited as a manual entry'); }
check_edit(count($controller->Smart_daily_productivity_model->updated) === 2, 'System-generated entry is not passed to update');

$controller->Smart_daily_productivity_model->entry = (object)['staff_id'=>17, 'source_type'=>'manual'];
$controller->input->body['category'] = 'unrecognized category';
try { $controller->update_entry(7); throw new RuntimeException('Expected redirect'); }
catch (RedirectEditFixture $redirect) { check_edit($GLOBALS['alertType'] === 'danger', 'Invalid category fails with an error alert'); }
check_edit(count($controller->Smart_daily_productivity_model->updated) === 2, 'Invalid category does not reach model update');

$permissions = ['view_own'];
try { $controller->update_entry(4); throw new RuntimeException('Expected denial'); }
catch (RuntimeException $error) { check_edit($error->getMessage() === 'denied', 'Edit permission is enforced by controller'); }
echo "Daily productivity edit: {$checks} checks passed.\n";
