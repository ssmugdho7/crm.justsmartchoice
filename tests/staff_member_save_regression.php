<?php

// Run with: php tests/staff_member_save_regression.php
if (PHP_SAPI !== 'cli') {
    exit(1);
}
define('BASEPATH', __DIR__);
define('FCPATH', realpath($argv[1] ?? dirname(__DIR__)) . '/');

class SaveRedirect extends RuntimeException {}
class SaveDenied extends RuntimeException {}

class SaveInput
{
    public $data = [];
    public function post($key = null, $clean = true) { return $key === null ? $this->data : ($this->data[$key] ?? null); }
}

class SaveDatabase
{
    public $conditions = [];
    public $queries = 0;
    public function where($key, $value) { $this->conditions[$key] = $value; return $this; }
    public function count_all_results($table)
    {
        $this->queries++;
        $count = 0;
        foreach ([1 => 'admin@example.test', 45 => 'staff@example.test', 46 => 'other@example.test'] as $id => $email) {
            if ($email === $this->conditions['email'] && $id !== (int) $this->conditions['staffid !=']) {
                $count++;
            }
        }
        return $count;
    }
    public function table_exists($table) { return false; }
}

class SaveLoader
{
    public function model($name) {}
}

class SaveModel
{
    public $saved;
    public $id;
    public function update($data, $id) { $this->saved = $data; $this->id = $id; return true; }
    public function add($data) { $this->saved = $data; $this->id = 47; return 47; }
}

class SaveHooks
{
    public function do_action($name, ...$args) {}
    public function add_action($name, ...$args) {}
    public function add_filter($name, ...$args) {}
}

class AdminController
{
    public $input;
    public $db;
    public $load;
    public $staff_model;
    public function __construct()
    {
        $this->input = new SaveInput();
        $this->db = new SaveDatabase();
        $this->load = new SaveLoader();
        $this->staff_model = new SaveModel();
    }
}

function hooks() { static $hooks; return $hooks ?? ($hooks = new SaveHooks()); }
function &get_instance() { return $GLOBALS['saveController']; }
function register_language_files(...$args) {}
function register_activation_hook(...$args) {}
function register_uninstall_hook(...$args) {}
function staff_cant($capability, $feature) { return in_array($capability, $GLOBALS['denied'] ?? [], true); }
function access_denied($feature) { throw new SaveDenied($feature); }
function db_prefix() { return 'tbl'; }
function _l($key, ...$args) { return $key; }
function set_alert($type, $message) { $GLOBALS['saveAlert'] = $message; }
function admin_url($path) { return '/admin/' . $path; }
function redirect($url) { throw new SaveRedirect($url); }
function handle_staff_profile_image_upload($id) {}
function nl2br_save_html($value) { return nl2br($value); }

require FCPATH . 'application/controllers/admin/Staff.php';
require FCPATH . 'modules/subcontractors/subcontractors.php';

function expectSave($condition, $description)
{
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $description);
    }
    echo 'PASS: ' . $description . PHP_EOL;
}

function saveStaff($id, $data, $denied = [])
{
    $GLOBALS['saveController'] = new Staff();
    $GLOBALS['saveController']->input->data = $data;
    $GLOBALS['denied'] = $denied;
    $GLOBALS['saveAlert'] = null;
    try {
        $GLOBALS['saveController']->member($id);
        throw new RuntimeException('Save did not redirect');
    } catch (SaveRedirect $result) {
        return [$GLOBALS['saveController'], $result->getMessage()];
    } catch (SaveDenied $result) {
        return [$GLOBALS['saveController'], 'denied'];
    }
}

$payload = [
    'firstname' => 'Updated', 'lastname' => 'Staff', 'email' => ' staff@example.test ',
    'administrator' => 'on', 'role' => '3', 'departments' => ['2'],
    'permissions' => ['projects' => ['view']], 'custom_fields' => ['staff' => ['1' => 'value']],
    'start_date' => '', 'probation_end_date' => '0000-00-00', 'contract_expiration_date' => '2027-01-15',
    'last_login' => 'untrusted',
];
[$controller, $redirect] = saveStaff('45', $payload);
expectSave($redirect === '/admin/staff/member/45' && $controller->staff_model->id === '45', 'unchanged email saves the target staff member');
expectSave($controller->staff_model->saved['email'] === 'staff@example.test', 'email is trimmed before lookup and persistence');
foreach (['administrator', 'role', 'departments', 'permissions', 'custom_fields'] as $field) {
    expectSave($controller->staff_model->saved[$field] === $payload[$field], $field . ' survives filtering');
}
expectSave(!array_key_exists('last_login', $controller->staff_model->saved), 'unapproved database fields remain filtered');
expectSave($controller->staff_model->saved['start_date'] === null && $controller->staff_model->saved['probation_end_date'] === null, 'empty and SQL zero-dates save as NULL');
expectSave($controller->staff_model->saved['contract_expiration_date'] === '2027-01-15', 'valid dates are retained');

$payload['email'] = 'other@example.test';
[$controller, $redirect] = saveStaff('45', $payload);
expectSave($redirect === '/admin/staff/member/45' && $controller->staff_model->saved === null, 'real duplicate email blocks saving and stays on the target page');
expectSave($GLOBALS['saveAlert'] === 'staff_email_already_exists', 'real duplicate displays the expected validation alert');

$payload['email'] = 'new@example.test';
[$controller, $redirect] = saveStaff('', $payload);
expectSave($redirect === '/admin/staff/member/47', 'creating a staff member with a unique email still succeeds');
$payload['email'] = 'staff@example.test';
[$controller, $redirect] = saveStaff('', $payload);
expectSave($redirect === '/admin/staff/member/' && $controller->staff_model->saved === null, 'creation rejects an existing staff email');
[$controller, $redirect] = saveStaff('45', $payload, ['edit']);
expectSave($redirect === 'denied' && $controller->db->queries === 0 && $controller->staff_model->saved === null, 'edit authorization runs before email lookup or writes');

$controller->input->data = [];
$existing = ['is_subcontractor' => 1, 'smartsource_subcontractor_id' => 12];
expectSave(smartsource_subcontractors_before_save_staff($existing, 45) === $existing, 'forms without module controls preserve subcontractor status and link');
$controller->input->data = ['smartsource_staff_fields_present' => '1', 'is_subcontractor' => '1', 'smartsource_subcontractor_id' => '12'];
expectSave(smartsource_subcontractors_before_save_staff([], 45) === $existing, 'checked subcontractor and existing link save together');
unset($controller->input->data['is_subcontractor']);
$updated = smartsource_subcontractors_before_save_staff($existing, 45);
expectSave($updated['is_subcontractor'] === 0 && $updated['smartsource_subcontractor_id'] === 12, 'explicit unchecking clears the flag while retaining the staff link');
