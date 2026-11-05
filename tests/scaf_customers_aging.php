<?php
define('BASEPATH',__DIR__);
function staff_cant($action,$feature){return $GLOBALS['denied'];}
function staff_can($action,$resource){return !$GLOBALS['denied'];}
function is_customer_admin($id){return $id===7;}
function db_prefix(){return 'fixture_';}
function get_decimal_places(){return 2;}
function &get_instance(){return $GLOBALS['ci'];}
class payments_model{function get_invoice_payments($id){return $id===2?[['amount'=>'20.00']]:($id===15?[['amount'=>'200.00']]:[]);}}
class credit_notes_model{function get_applied_invoice_credits($id){return $id===2?[['amount'=>'15.00']]:[];}}
class AgingDB{public $filters=[],$calls=0;function select($v){$this->filters=[];return $this;}function where($key,$value){$this->filters[$key]=$value;return $this;}function where_in($key,$value){$this->filters[$key]=$value;return $this;}function get($table){$this->calls++;$filters=$this->filters;$rows=array_filter($GLOBALS['rows'],function($r)use($filters){return $r['clientid']===$filters['clientid'] && $r['currency']===$filters['currency'] && in_array($r['status'],$filters['status'],true) && $r['date']<=$filters['date <='];});return new class($rows){private $rows;function __construct($r){$this->rows=$r;}function result_array(){return $this->rows;}};}}
$denied=false;$ci=(object)['db'=>new AgingDB,'load'=>new class{function helper($x){}},'payments_model'=>new payments_model,'credit_notes_model'=>new credit_notes_model];
$rows=[];foreach([[1,'100.10','2026-11-10'],[2,'100.00','2026-11-06'],[3,'1.01','2026-11-05'],[4,'2.02','2026-10-07'],[5,'3.03','2026-10-06'],[6,'4.04','2026-09-07'],[7,'5.05','2026-09-06'],[8,'6.06',null],[9,'900','2026-01-01'],[10,'900','2026-01-01'],[11,'900','2026-01-01'],[12,'900','2026-01-01'],[13,'900','2026-01-01'],[14,'900','2026-01-01'],[15,'100','2026-01-01']] as $r)$rows[]=['id'=>$r[0],'total'=>$r[1],'duedate'=>$r[2],'status'=>1,'currency'=>2,'clientid'=>7,'date'=>'2026-01-01'];
$rows[8]['status']=2;$rows[9]['status']=5;$rows[10]['status']=6;$rows[11]['currency']=3;$rows[12]['clientid']=99;$rows[13]['date']='2026-11-07';
require dirname(__DIR__).'/application/helpers/invoices_helper.php';
require dirname(__DIR__).'/modules/sc_admin_fixes/includes/money.php';
require dirname(__DIR__).'/modules/sc_admin_fixes/includes/customer_data.php';
$result=scaf_customer_current_aging(7,2,'2026-11-06');
$expected=['current'=>'171.16','1_30'=>'3.03','31_60'=>'7.07','61_plus'=>'5.05'];
foreach($expected as $key=>$value){if(number_format((float)$result['buckets'][$key],2,'.','')!==$value)throw new RuntimeException($key.':'.json_encode($result));}
$calls=$ci->db->calls;$denied=true;if(scaf_customer_current_aging(7,2,'2026-11-06')!==null || $ci->db->calls!==$calls)throw new RuntimeException('Unauthorized query');
echo "PASS aging day boundaries, actual native payment/credit deductions, currency/customer/date/status isolation, overpayment and denied access\n";
