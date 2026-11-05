<?php
// Run directly: php tests/board_appointments.php
// No CRM records, mail, or external services are touched.
define('BASEPATH', true);
$permissions = ['estimates:create' => true, 'appointments:view' => false];
function staff_can($action, $resource) { global $permissions; return $permissions[$resource . ':' . $action] ?? false; }
function get_staff_user_id() { return 8; }
function db_prefix() { return 'tbl'; }
class BoardAppointmentsDb {
    public $rows = [];
    public $queries = 0;
    function where(...$args) { return $this; }
    function get($table) { $this->queries++; return $this; }
    function row_array() { return array_shift($this->rows); }
}
$ci = (object) ['db' => new BoardAppointmentsDb];
function &get_instance() { global $ci; return $ci; }
function expect($test, $message) { if (!$test) throw new RuntimeException($message); }
require __DIR__ . '/../modules/appointly/helpers/appointly_helper.php';
$cards = appointly_dispatch_cards([
 ['date'=>'2026-11-05','status'=>'pending'],
 ['date'=>'2026-11-05','status'=>'cancelled'],
 ['date'=>'2026-11-08','status'=>'in-progress'],
 ['date'=>'2026-11-09','status'=>'pending'],
 ['date'=>'2026-11-01','status'=>'completed'],
 ['date'=>'2026-10-31','status'=>'completed'],
 ['date'=>'2026-11-06','status'=>'completed'],
 ['date'=>'2026-11-05','status'=>'no-show'],
], '2026-11-05');
expect($cards === ['today'=>1,'week'=>2,'pending'=>2,'completed'=>1], 'KPI period/status boundaries failed');
expect(appointly_estimate_prefill('7 OR 1=1') === null && $ci->db->queries === 0, 'Unsafe appointment ID queried');
$permissions['estimates:create'] = false;
expect(appointly_estimate_prefill('7') === null && $ci->db->queries === 0, 'Missing estimate create permission queried');
$permissions['estimates:create'] = true;
$appointment = ['id'=>7,'created_by'=>9,'source'=>'internal','contact_id'=>15,'service_id'=>2,'address'=>'123 Oak St','subject'=>'Review','description'=>'Scope'];
$ci->db->rows = [$appointment, null];
expect(appointly_estimate_prefill('7') === null, 'Unrelated staff obtained prefill');
$ci->db->rows = [$appointment, ['staff_id'=>8], ['userid'=>32], ['name'=>'Inspection']];
$prefill = appointly_estimate_prefill('7');
expect($prefill['customer_id'] === 32 && $prefill['address'] === '123 Oak St' && $prefill['service'] === 'Inspection', 'Authorized attendee handoff lost real relationship/service/address');
$appointment['created_by'] = 8;
$appointment['source'] = 'external';
$ci->db->rows = [$appointment];
expect(appointly_estimate_prefill('7') === null, 'External appointment created an unlinked customer draft');
echo "PASS appointment KPI boundaries and estimate handoff authorization/data\n";
