<?php
define('BASEPATH',__DIR__);
function get_decimal_places(){return 2;}
function db_prefix(){return 'tbl';}
$permission=false;
function staff_can($cap,$feature){global $permission;return $permission;}
function get_staff_user_id(){return 42;}
function get_option($key){return $key==='allow_staff_view_invoices_assigned'?1:null;}
function get_invoices_where_sql_for_staff($id){return 'tblinvoices.addedfrom='.$id.' OR sale_agent='.$id;}
class FakeDB {public $calls=[];public $rows=[];function __call($name,$args){$this->calls[]=[$name,$args];return $this;}function get(){return $this;}function result_array(){return $this->rows;}}
class FakeInput {public $post=[];function post($key){return $this->post[$key]??null;}}
$ci=(object)['db'=>new FakeDB,'input'=>new FakeInput,'load'=>new class{function model($v){}}];
$ci->payment_modes_model=new class{function get_payment_gateways($v){return [['id'=>'stripe','name'=>'Stripe configured gateway']];}};
function &get_instance(){global $ci;return $ci;}
function ck($condition,$message){if(!$condition)throw new RuntimeException($message);}
require dirname(__DIR__).'/modules/sc_admin_fixes/includes/money.php';
require dirname(__DIR__).'/modules/sc_admin_fixes/includes/sales_data.php';
$ci->input->post=['years'=>['2026','2026) OR 1=1'],'currency'=>'2'];scaf_sales_invoice_data();
$calls=json_encode($ci->db->calls);
ck(strpos($calls,'i.addedfrom=42 OR sale_agent=42')!==false,'Native own/assigned invoice scope retained');
ck(strpos($calls,'OR 1=1')===false,'Invalid fiscalyear discarded');ck(strpos($calls,'i.currency')!==false,'Valid native currency selector honored');
$permission=true;$ci->db->calls=[];scaf_sales_invoice_data();ck(strpos(json_encode($ci->db->calls),'addedfrom=42')===false,'Global view not artificially scoped');
$permission=false;$ci->db->calls=[];$ci->db->rows=[['currency_name'=>'EUR','amount'=>'0.10','mode_name'=>null,'paymentmode'=>'stripe','paymentmethod'=>''],['currency_name'=>'EUR','amount'=>'0.20','mode_name'=>null,'paymentmode'=>'stripe','paymentmethod'=>'']];
$g=scaf_sales_payment_data();ck($g['EUR']['total']==='0.30','Exact payment totals');ck($g['EUR']['modes']['Stripe configured gateway']==='0.30','Actual configured gateway name');ck(strpos(json_encode($ci->db->calls),'i.sale_agent=42')!==false,'Assigned payment scope retained');
$ci->db->calls=[];$ci->db->rows=[['currency_name'=>'USD','total'=>'100.10','applied'=>'20.01','refunded'=>'10.09','status'=>1],['currency_name'=>'USD','total'=>'50.00','applied'=>'49.99','refunded'=>'0','status'=>2]];
$g=scaf_sales_credit_data();ck($g['USD']['available']==='70.00','Refunded credit unavailable and closed residue unusable');ck($g['USD']['issued']==='150.10','Actual issued totals');ck(strpos(json_encode($ci->db->calls),'n.addedfrom')!==false,'Credit own scope retained');
echo "PASS native own/assigned/global queryscope,year filtering,currency selector,configured gateway,exactpayments,creditrefund/status\n";
