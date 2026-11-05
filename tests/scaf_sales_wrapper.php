<?php
define('BASEPATH',__DIR__);
$fixture=sys_get_temp_dir().'/scaf-sales-view-'.bin2hex(random_bytes(5)).'/';
define('APPPATH',$fixture);
mkdir($fixture.'views/admin/proposals',0700,true);mkdir($fixture.'views/admin/payments',0700,true);
$native='<div id="wrapper"><div class="content"><form action="/native" method="post"><input name="csrf_hash" value="actual-token"><button>Native action</button></form></div></div>';
file_put_contents($fixture.'views/admin/proposals/proposals_preview_template.php',$native);
file_put_contents($fixture.'views/admin/payments/payment.php',$native);
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}function _l($s){return $s;}function _dt($s){return 'formatted '.$s;}function scaf_sales_label($a,$b){return $a;}function admin_url($s){return '/admin/'.$s;}
function ck($v,$m){if(!$v)throw new RuntimeException($m);}
function render($kind,$path,$proposal=null,$payment=null){$scaf_sales_kind=$kind;$scaf_sales_native=$path;ob_start();include dirname(__DIR__).'/modules/sc_admin_fixes/views/sales/wrapper.php';return ob_get_clean();}
try {
 $proposal=(object)['status'=>3,'signature'=>'actual.png','acceptance_firstname'=>'<Ana>','acceptance_lastname'=>'Rivera','acceptance_date'=>'2026-11-06 12:34:00','acceptance_ip'=>'192.0.2.7'];
 $html=render('proposal','admin/proposals/proposals_preview_template',$proposal);
 ck(strpos($html,'&lt;Ana&gt; Rivera')!==false&&strpos($html,'192.0.2.7')!==false&&strpos($html,'formatted 2026-11-06 12:34:00')!==false,'Actual signature proof escaped and rendered');ck(strpos($html,$native)!==false,'Native form/token preserved verbatim');
 $proposal->signature='';$html=render('proposal','admin/proposals/proposals_preview_template',$proposal);ck(strpos($html,'scaf-signature-ribbon')===false,'Never invent electronic signature for manual acceptance');
 $html=render('receipt','admin/payments/payment',null,(object)['paymentid'=>19]);ck(strpos($html,'/admin/payments/pdf/19?print=true')!==false,'Oneclick print uses actual native PDF endpoint');ck(strpos($html,'<div class="content"><div class="scaf-receipt-actions">')!==false,'Actions inserted inside native content');ck(strpos($html,'name="csrf_hash" value="actual-token"')!==false,'Receipt native token preserved');
 echo "PASS wrapper actual signature proof/no fabricated acceptance,nativeCSRFform retention,realreceiptprint route\n";
} finally {unlink($fixture.'views/admin/proposals/proposals_preview_template.php');unlink($fixture.'views/admin/payments/payment.php');rmdir($fixture.'views/admin/proposals');rmdir($fixture.'views/admin/payments');rmdir($fixture.'views/admin');rmdir($fixture.'views');rmdir($fixture);}
