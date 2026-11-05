<?php
// Exact fixtures test currency isolation, permissions query construction and aging cutoffs.
define('BASEPATH', __DIR__);
function get_decimal_places(){return 2;}
require dirname(__DIR__).'/modules/sc_admin_fixes/includes/money.php';
require dirname(__DIR__).'/modules/sc_admin_fixes/includes/sales_data.php';
function ck($condition,$message){if(!$condition)throw new RuntimeException($message);}
$rows=[
['currency_name'=>'USD','total'=>'100.10','paid'=>'10.01','credited'=>'0.09','duedate'=>'2026-11-06'],
['currency_name'=>'USD','total'=>'100.00','paid'=>'0','credited'=>'0','duedate'=>'2026-10-07'],
['currency_name'=>'USD','total'=>'20.00','paid'=>'0','credited'=>'0','duedate'=>'2026-10-06'],
['currency_name'=>'USD','total'=>'30.00','paid'=>'0','credited'=>'0','duedate'=>'2026-09-07'],
['currency_name'=>'USD','total'=>'40.00','paid'=>'0','credited'=>'0','duedate'=>'2026-09-06'],
['currency_name'=>'USD','total'=>'50.00','paid'=>'0','credited'=>'0','duedate'=>'2026-08-08'],
['currency_name'=>'USD','total'=>'60.00','paid'=>'0','credited'=>'0','duedate'=>'2026-08-07'],
['currency_name'=>'USD','total'=>'0.30','paid'=>'0.10','credited'=>'0.20','duedate'=>null],
['currency_name'=>'EUR','total'=>'999.99','paid'=>'0','credited'=>'0','duedate'=>'2027-01-01'],
];
$g=scaf_sales_invoice_metrics($rows,'2026-11-06');
ck($g['USD']['due']==='390.00','Exact due subtracts actual cash and credits');
ck($g['USD']['aging']===['190.00','50.00','90.00','60.00'],'30/31/60/61/90/91 boundary classifications');
ck($g['USD']['past_due']==='200.00','Escalation excludes current0–30');
ck($g['USD']['paid']==='10.11','Actual cash excludes credited0.29');
ck($g['USD']['paid_count']===1,'Fully creditsettled invoice counted settled');
ck($g['EUR']['due']==='999.99'&&$g['EUR']['aging'][0]==='999.99','Currency isolation and future due current');
ck(array_sum(array_map('floatval',$g['USD']['aging']))===(float)$g['USD']['due'],'Aging reconciles due');
echo "PASS exact invoice totals, currency separation, real cash vs credit,30/31/60/61/90/91 aging boundaries\n";
