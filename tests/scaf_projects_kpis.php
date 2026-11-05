<?php
define('BASEPATH',true);$canCost=false;$canInvoice=false;
function staff_can($a,$b){global $canCost,$canInvoice;return $b==='projects'?($a==='view'||($a==='create'&&$canCost)):($b==='invoices'&&$canInvoice);}
function db_prefix(){return 'tbl';}function get_staff_user_id(){return 8;}
function get_custom_field_value($id,$field,$type,$html){return $id===1?'Yes':'No';}
function get_decimal_places(){return 2;}
require __DIR__.'/../modules/sc_admin_fixes/includes/money.php';
function hooks(){return new class{function add_filter(...$a){}function add_action(...$a){}};}
class KpiDB{public $table,$invoiceReads=0;function select(...$a){return $this;}function where(...$a){return $this;}function where_not_in(...$a){return $this;}function get($t){$this->table=$t;if($t==='tblinvoices')$this->invoiceReads++;return $this;}function row_array(){return ['id'=>7];}function result_array(){if($this->table==='tblcurrencies')return [['id'=>1,'name'=>'USD']];return [['currency'=>1,'total'=>'12.25']];}}
$ci=(object)['db'=>new KpiDB,'load'=>new class{function model($n){}},'projects_model'=>new class{function get(...$a){$base=['deadline'=>'2020-01-01','billing_type'=>1,'project_cost'=>'100.05'];return [array_merge($base,['id'=>1,'status'=>2]),array_merge($base,['id'=>2,'status'=>4]),array_merge($base,['id'=>3,'status'=>5]),array_merge($base,['id'=>4,'status'=>18])];}function get_currency($id){return(object)['name'=>'USD'];}}];
function &get_instance(){global $ci;return $ci;}
require __DIR__.'/../modules/sc_admin_fixes/includes/projects.php';
function check_kpi($v,$m){if(!$v)throw new RuntimeException($m);}
$r=scaf_projects_kpis();check_kpi($r['active']===1&&$r['overdue']===1&&$r['permitted']===1,'Only status2 included; Yes permit actual');check_kpi($r['contracts']===[]&&$r['billed']===[]&&$ci->db->invoiceReads===0,'Viewprojects alone cannot expose money');
$canCost=true;$canInvoice=true;$r=scaf_projects_kpis();check_kpi($r['contracts']['USD']==='100.05'&&$r['billed']['USD']==='12.25','Native financial permission exposes exact actual amounts');
echo "PASS actual active status, overdue/permit counts and native cost/invoice permission gates\n";
