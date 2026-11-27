<?php
// Render real menu templates with deterministic record/permission fixtures, without a database.
if (PHP_SAPI !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) { http_response_code(404); exit; }
define('BASEPATH', true);
$root = dirname(__DIR__, 2);
$lang=[];
require $root.'/application/language/english/english_lang.php';
require $root.'/modules/sc_admin_fixes/language/english/sc_admin_fixes_lang.php';
function _l($key, $arg='') { $s=$GLOBALS['lang'][$key]??$key; return $arg!==''?sprintf($s,$arg):$s; }
function e($s){return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');}
function site_url($p){return 'https://crm.example/'.$p;}
function admin_url($p=''){return site_url('admin/'.$p);}
function staff_can($cap,$module){return ($GLOBALS['permission']??'admin')==='admin';}
function get_option($s){return 0;}
function is_last_estimate($id){return true;}
function is_invoice_overdue($i){return false;}
function is_invoices_overdue_reminders_enabled(){return false;}
function is_proposals_expiry_reminders_enabled(){return false;}
function is_estimates_expiry_reminders_enabled(){return false;}
function format_proposal_status($s,...$args){return [1=>'Open',2=>'Declined',3=>'Accepted',4=>'Sent',5=>'Revised',6=>'Draft'][$s];}
function format_estimate_status($s,...$args){return [1=>'Draft',2=>'Sent',3=>'Declined',4=>'Accepted',5=>'Expired'][$s];}
class SalesMenuHooks {function do_action(...$args){}}
function hooks(){return new SalesMenuHooks;}
class Invoices_model {const STATUS_UNPAID=1,STATUS_PAID=2,STATUS_PARTIALLY=3,STATUS_OVERDUE=4,STATUS_CANCELLED=5,STATUS_DRAFT=6;}
$files=['proposals'=>'proposals/proposals_preview_template.php','estimates'=>'estimates/estimate_preview_template.php','invoices'=>'invoices/invoice_preview_template.php','credit_notes'=>'credit_notes/credit_note_preview_template.php','payments'=>'payments/payment.php','contracts'=>'contracts/contract.php'];
$kind=$argv[1]??$_GET['kind']??'proposals';
$permission=$argv[2]??$_GET['permission']??'admin';
if(!isset($files[$kind]))exit(1);
$record=(object)['id'=>17,'paymentid'=>17,'invoiceid'=>null,'hash'=>'fixturehash','status'=>1,'signature'=>'','open_till'=>'','expirydate'=>'','project_id'=>0,'clientid'=>4,'sent'=>0,'duedate'=>'','credits_used'=>0,'total_refunds'=>0,'signed'=>0,'marked_as_signed'=>0];
$proposal=$estimate=$invoice=$credit_note=$payment=$contract=$record;
$proposal_statuses=[1,2,3,4,5,6];$estimate_statuses=[1,2,3,4,5];$isSignedOrMarkedSigned=false;
$s=file_get_contents($root.'/application/views/admin/'.$files[$kind]);
$start=strpos($s,'<ul class="dropdown-menu dropdown-menu-right">',strpos($s,"_l('more')"));
$end=strpos($s,'</ul>',$start)+5;
ob_start();eval('?>'.substr($s,$start,$end-$start));$menu=ob_get_clean();
if(PHP_SAPI==='cli'){echo $menu;exit;}
?>
<!doctype html><html class="sc-sales-ui"><head><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="/modules/sales_center/assets/css/sales-actions.css"></head><body style="padding:30px"><div id="wrapper"><h1><?=e($kind)?></h1><div class="btn-group"><button class="btn btn-default dropdown-toggle" data-toggle="dropdown">More <span class="caret"></span></button><?=$menu?></div></div><script src="/assets/plugins/jquery/jquery.min.js"></script><script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script><script src="/modules/sales_center/assets/js/sales-actions.js"></script></body></html>
