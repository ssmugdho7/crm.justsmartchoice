<?php
define('BASEPATH', true);
require __DIR__ . '/../application/helpers/lead_assignments_helper.php';
class AssignmentTestDb {
    public function select($v) { return $this; }
    public function where_in($k,$v) { return $this; }
    public function where($k,$v) { return $this; }
    public function get($v) { return $this; }
    public function result_array() { return [['staffid'=>1],['staffid'=>2],['staffid'=>3]]; }
}
function &get_instance() { static $ci; if (!$ci) $ci=(object)['db'=>new AssignmentTestDb]; return $ci; }
function db_prefix() { return 'tbl'; }
function get_staff_user_id() { return 1; }
function is_admin() { return empty($GLOBALS['restricted']); }
function staff_cant($a,$b) { return !empty($GLOBALS['restricted']); }
function verify($condition,$message) { if (!$condition) throw new Exception($message); }
$old=['assigned'=>2,'additional_assigned_staff'=>'1,3','addedfrom'=>1];
verify(sc_lead_assignee_ids($old)===[2,1,3], 'Read all saved assignees');
$data=sc_lead_prepare_assignees(['assigned_staff_present'=>1,'assigned_staff'=>['1','2','3','3','999','bad']]);
verify($data===['assigned'=>1,'additional_assigned_staff'=>'2,3'], 'Validate, deduplicate and save');
$data=sc_lead_prepare_assignees(['assigned_staff_present'=>1,'assigned_staff'=>[1,2]],$old);
verify($data['assigned']===2 && $data['additional_assigned_staff']==='1','Keep current primary when still selected');
verify(sc_lead_assignee_ids($data)===[2,1],'Round trip multi-selection');
verify(sc_lead_prepare_assignees(['assigned_staff_present'=>1],$old)===['assigned'=>0,'additional_assigned_staff'=>''],'Clear all');
verify(sc_lead_prepare_assignees(['name'=>'New'],$old)===['name'=>'New'],'Unrelated edits preserve assignments');
verify(sc_lead_prepare_assignees(['assigned'=>3],$old)===['assigned'=>3,'additional_assigned_staff'=>''],'Legacy reassignment');
$GLOBALS['restricted']=true;
verify(sc_lead_prepare_assignees(['assigned_staff_present'=>1,'assigned_staff'=>[3]],['assigned'=>1,'addedfrom'=>2])===[], 'Respect assignment edit restriction');
verify(strpos(sc_lead_assigned_sql('2 OR 1=1'),'2 OR 1=1')===false,'Numeric SQL identifiers');
verify(strpos(sc_lead_assigned_sql(3),'FIND_IN_SET(3, tblleads.additional_assigned_staff)')!==false,'Secondary assignment visibility');
echo "PASS: multi-assignee persistence, validation, legacy input, clearing, access and edit restriction\n";
