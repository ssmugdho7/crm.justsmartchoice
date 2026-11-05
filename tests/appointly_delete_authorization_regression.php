<?php
define('BASEPATH',true);
class App_Model { public $db; }
$canDelete=false;$admin=false;$actor=8;
function staff_can($a,$b){global $canDelete;return $canDelete;}
function is_admin(){global $admin;return $admin;}
function get_staff_user_id(){global $actor;return $actor;}
function db_prefix(){return 'tbl';}
function _l($key){return $key;}
class DeleteGuardDb {
 public $reads=0;
 function where(...$args){return $this;}
 function get($table){$this->reads++;return $this;}
 function row_array(){return ['id'=>99,'created_by'=>7,'google_event_id'=>'external-event'];}
 function delete($table){throw new Exception('Unauthorized mutation: '.$table);}
}
require __DIR__.'/../modules/appointly/models/Appointly_model.php';
$m=(new ReflectionClass('Appointly_model'))->newInstanceWithoutConstructor();$m->db=new DeleteGuardDb;
$r=$m->delete_appointment(99);
if($r['success']!==false||$m->db->reads!==0)throw new Exception('Missing permission must deny before database access');
$canDelete=true;$r=$m->delete_appointment(99);
if($r['success']!==false||$r['message']!=='access_denied')throw new Exception('Other staff appointment must deny before related/external deletes');
echo "PASS missing permission and cross-owner deletion are rejected before side effects\n";
