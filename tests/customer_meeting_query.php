<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
class App_Model { public $db; function __construct() {} }
function db_prefix(){return 'tbl';}
function log_message($level,$message){}
require dirname(__DIR__).'/modules/google_meet/models/Google_meet_model.php';
class MeetingQueryFixture {
 public $calls=[];public $reset=false;public $failure=false;
 function table_exists($table){return true;}
 function field_exists($field,$table){return true;}
 function __call($name,$args){$this->calls[]=[$name,$args];return $this;}
 function get($table=null){
  if($table==='tblcontacts') return new class {function result_array(){return [['id'=>10,'email'=>'customer@example.test']];}};
  if($this->failure) throw new RuntimeException('Simulated query failure');
  return new class {function result_array(){return [['id'=>7]];}};
 }
 function reset_query(){$this->reset=true;}
}
foreach([false,true] as $failure){
 $model=new Google_meet_model();$model->db=new MeetingQueryFixture();$model->db->failure=$failure;
 $result=$model->get_client_meetings(42);
 if($result!==($failure?[]:[['id'=>7]]) || !$model->db->reset)throw new RuntimeException('Result/cleanup regression');
 $calls=$model->db->calls;
 if(!in_array(['where',['userid',42]],$calls,true)||!in_array(['where_in',['a.contact_id',[10]]],$calls,true))throw new RuntimeException('Ownership filters changed');
 if(!in_array(['where_in',['LOWER(a.email)',['customer@example.test']]],$calls,true))throw new RuntimeException('Email values bypass escaping');
}
echo "PASS: ownership filters retained, email values escaped, query state cleared on success and failure\n";
