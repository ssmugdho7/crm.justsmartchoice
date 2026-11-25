<?php

define('BASEPATH', dirname(__DIR__) . '/system/');
error_reporting(E_ALL & ~E_DEPRECATED);

function log_message($level, $message) {}
function show_error($message, $status = 500) { throw new RuntimeException($message, $status); }
function remove_invisible_characters($value, $urlDecode = true) { return $value; }
function db_prefix() { return 'tbl'; }
function staff_cant($capability, $feature) { return !$GLOBALS['canDelete']; }
function is_admin() { return false; }
function staff_can($capability, $feature) { return false; }
function get_staff_user_id() { return 7; }
function ajax_access_denied() { show_error('Access denied', 403); }
function delete_tracked_emails($id, $type) {}
function log_activity($message) {}
function hooks() {
    return new class {
        public function do_action($name, $id) { $GLOBALS['events'][] = [$name, $id]; }
    };
}
function &get_instance() { return $GLOBALS['app']; }

class App_Model
{
    public $db;
}

class AdminController
{
    public $input;
    public $load;
    public $output;
    public $proposals_model;
}

require BASEPATH . 'database/DB.php';
require dirname(__DIR__) . '/application/helpers/signature_backup_helper.php';
require dirname(__DIR__) . '/application/models/Proposals_model.php';
require dirname(__DIR__) . '/application/controllers/admin/Smart_choice_bulk.php';

$model = new class extends Proposals_model {
    public function __construct() {}
    public function get($id = '', $where = [], $for_editor = false) {
        return $this->db->get_where('tblproposals', ['id' => $id])->row();
    }
    public function get_attachments($proposal_id, $id = '') { return []; }
};
$model->db = DB(['dbdriver' => 'sqlite3', 'database' => ':memory:', 'db_debug' => true], true);
$GLOBALS['app'] = $model;
$model->db->conn_id->exec("
CREATE TABLE tblproposals (id INTEGER, signature TEXT, short_link TEXT, addedfrom INT);
CREATE TABLE tblsignature_images (rel_id INT, rel_type TEXT);
CREATE TABLE tblproposal_comments (proposalid INT);
CREATE TABLE tblcustomfieldsvalues (relid INT, fieldto TEXT);
CREATE TABLE tblsc_sales_meta (rel_id INT, rel_type TEXT);
INSERT INTO tblproposals VALUES (11,NULL,NULL,7),(12,NULL,NULL,7),(13,NULL,NULL,9);
INSERT INTO tblproposal_comments VALUES (11),(12),(13);
INSERT INTO tblsignature_images VALUES (12,'proposal'),(13,'proposal');
INSERT INTO tblsc_sales_meta VALUES (11,'proposal'),(12,'proposal'),(13,'proposal');
");
foreach (['tasks', 'notes', 'itemable', 'item_tax', 'taggables', 'reminders', 'views_tracking'] as $table) {
    $model->db->conn_id->exec("CREATE TABLE tbl$table (id INT, rel_id INT, rel_type TEXT)");
}

$controller = new Smart_choice_bulk();
$controller->proposals_model = $model;
$controller->load = new class { public function model($model) {} };
$controller->input = new class {
    public $post;
    public function is_ajax_request() { return true; }
    public function method($upper) { return 'POST'; }
    public function post($key) { return $this->post[$key] ?? null; }
};
$controller->output = new class {
    public $json;
    public function set_content_type($type) { return $this; }
    public function set_output($json) { $this->json = json_decode($json, true); }
};

function expect($actual, $expected) {
    if ($actual !== $expected) {
        throw new RuntimeException(json_encode(['expected' => $expected, 'actual' => $actual]));
    }
}
function submit($controller, $inputs, $status = null) {
    $controller->input->post = $inputs;
    try {
        $controller->delete('proposals');
    } catch (RuntimeException $error) {
        if ($status === null) {
            throw $error;
        }
        expect($error->getCode(), $status);
        return;
    }
    expect($status, null);
}

$GLOBALS['canDelete'] = true;
$GLOBALS['events'] = [];
submit($controller, ['ids' => [11, 12]], 422);
submit($controller, ['ids' => [11, 12], 'delete_confirmation' => 'delete'], 422);
submit($controller, ['ids' => [11, 13], 'delete_confirmation' => 'DELETE'], 403);
$GLOBALS['canDelete'] = false;
submit($controller, ['ids' => [11, 12], 'delete_confirmation' => 'DELETE'], 403);
expect($model->db->count_all('tblproposals'), 3);

$GLOBALS['canDelete'] = true;
submit($controller, ['ids' => [11, 12], 'delete_confirmation' => 'DELETE']);
expect($controller->output->json, ['success' => true, 'deleted' => 2, 'failed' => []]);
expect((int) $model->db->get('tblproposals')->row()->id, 13);
expect((int) $model->db->get('tblproposal_comments')->row()->proposalid, 13);
expect((int) $model->db->get('tblsignature_images')->row()->rel_id, 13);
expect((int) $model->db->get('tblsc_sales_meta')->row()->rel_id, 13);
expect($GLOBALS['events'], [
    ['before_proposal_deleted', 11], ['after_proposal_deleted', 11],
    ['before_proposal_deleted', 12], ['after_proposal_deleted', 12],
]);
echo "PASS proposal bulk deletion requires DELETE and authorized records. Two proposals and their related rows were removed.\n";
