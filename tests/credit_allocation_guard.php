<?php
define('BASEPATH',__DIR__);
class App_Model{} class Invoices_model{const STATUS_DRAFT=6;}
function db_prefix(){return 'fixture_';} function get_decimal_places(){return 2;} function get_staff_user_id(){return 7;}
function invoices_statuses_available_for_credits(){return [1,3,4,6];}
function get_invoice_total_left_to_pay($id,$total){return $GLOBALS['db']->due;}
function format_invoice_number($id){return 'INV-'.$id;}function format_credit_note_number($id){return 'CN-'.$id;}
function app_format_money($v,$currency){return $v;}function log_activity($v){}
function hooks(){return new class{function do_action(...$args){}};}
require dirname(__DIR__).'/application/models/Credit_notes_model.php';
class AllocationDB{public $credit,$invoice,$remaining='100.00',$due='80.00',$inserts=[],$events=[],$failed=false,$failBegin=false,$failCommit=false;function __construct(){$this->credit=(object)['id'=>1,'clientid'=>7,'currency'=>1,'status'=>1];$this->invoice=(object)['id'=>2,'clientid'=>7,'currency'=>1,'status'=>1,'total'=>'80.00','currency_name'=>'USD'];}function trans_begin(){$this->events[]='begin';return !$this->failBegin;}function query($sql,$args){if(strpos($sql,'FOR UPDATE')===false)throw new RuntimeException('Missing lock');$this->events[]='lock';$row=strpos($sql,'creditnotes')!==false?$this->credit:$this->invoice;return new class($row){private $r;function __construct($r){$this->r=$r;}function row(){return $this->r;}};}function trans_rollback(){$this->events[]='rollback';}function trans_commit(){$this->events[]='commit';return !$this->failCommit;}function trans_status(){return !$this->failed;}function insert($table,$data){$this->inserts[]=$data;}function insert_id(){return $this->failed?0:11;}function select(...$args){return $this;}function where(...$args){return $this;}function join(...$args){return $this;}function get($table){$r=$this->invoice;return new class($r){private $r;function __construct($r){$this->r=$r;}function row(){return $this->r;}};}}
class AllocationFixture extends Credit_notes_model{public $db,$invoices_model,$updates=0;function __construct($db){$this->db=$db;$this->invoices_model=new class{function log_invoice_activity(...$args){}};}function total_remaining_credits_by_credit_note($id){return $this->db->remaining;}function update_credit_note_status($id){$this->updates++;}}
function runCase($amount,$change=null,$expected=false){$GLOBALS['db']=$db=new AllocationDB;if($change)$change($db);$m=new AllocationFixture($db);$v=$m->apply_credits(1,['invoice_id'=>2,'amount'=>$amount]);if((bool)$v!==$expected)throw new RuntimeException('Unexpected allocation '.json_encode($amount));if(!$expected && $db->inserts)throw new RuntimeException('Invalid allocation inserted');if(in_array('begin',$db->events,true) && end($db->events)!==($expected?'commit':'rollback'))throw new RuntimeException('Transaction left open');return $db;}
foreach(['0','-1','NaN','INF','1e3','1.001','100.01',[],null] as $v)runCase($v);
runCase('80.01');runCase('50',function($d){$d->remaining='49.99';});
runCase('1',function($d){$d->credit->clientid=8;});runCase('1',function($d){$d->invoice->currency=2;});runCase('1',function($d){$d->credit->status=3;});runCase('1',function($d){$d->invoice->status=5;});runCase('1',function($d){$d->credit=null;});runCase('1',function($d){$d->invoice=null;});
$d=runCase('80.00',null,true);if($d->inserts[0]['amount']!=='80.00' || $d->events!==['begin','lock','lock','commit'])throw new RuntimeException('Valid allocation/locks changed');
runCase('.50',null,true);runCase('50.01',function($d){$d->remaining='50.00';});
foreach (['failBegin','failCommit'] as $failure) {
    $GLOBALS['db']=$db=new AllocationDB;$db->$failure=true;$model=new AllocationFixture($db);
    if($model->apply_credits(1,['invoice_id'=>2,'amount'=>'10'])!==false || $model->updates!==0)throw new RuntimeException('Transaction failure reported success');
    if($failure==='failBegin' && $db->inserts)throw new RuntimeException('Begin failure inserted credit');
    if($failure==='failCommit' && end($db->events)!=='rollback')throw new RuntimeException('Commit failure did not rollback');
}
echo "PASS allocation precision, positive values, credit/invoice caps, customer/currency/status identity, missing records and lock/commit/rollback paths\n";
