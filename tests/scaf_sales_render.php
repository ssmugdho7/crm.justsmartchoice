<?php
define('BASEPATH',__DIR__);
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function _l($s){return $s;}
function app_format_money($v,$c){return $c.' '.$v;}
function scaf_sales_label($a,$b){return $a;}
function scaf_sales_percent($a,$b){return (float)$b>0?round((float)$a/(float)$b*100,1):0;}
function ck($v,$m){if(!$v)throw new RuntimeException($m);}
$scaf_sales_kind='invoices';
$scaf_sales_metrics=['years'=>['2026'],'groups'=>['EUR'=>['paid'=>'12.34','past_due'=>'56.78','due'=>'99.90','issued'=>'200.00','paid_count'=>3,'due_count'=>2,'aging'=>['43.12','56.78','0.00','0.00']]]];
ob_start();include dirname(__DIR__).'/modules/sc_admin_fixes/views/sales/metrics.php';$html=ob_get_clean();
foreach(['2026','EUR 12.34','EUR 56.78','EUR 99.90','6.2%','43.2%'] as $needle){ck(strpos($html,$needle)!==false,'Actual financial value missing:'.$needle);}
ck(preg_match_all('/class="scaf-aging-[0-3]"/', $html)===8,'Four accessible labeled aging buckets and matching visual segments');
$scaf_sales_kind='payments';$scaf_sales_metrics=['USD'=>['total'=>'71.30','count'=>2,'modes'=>['<Untrusted mode>'=>'71.30']],'JPY'=>['total'=>'1000','count'=>1,'modes'=>['Cash'=>'1000']]];
ob_start();include dirname(__DIR__).'/modules/sc_admin_fixes/views/sales/metrics.php';$html=ob_get_clean();
ck(strpos($html,'USD 71.30')!==false&&strpos($html,'JPY 1000')!==false,'Separate currency totals');ck(strpos($html,'&lt;Untrusted mode&gt;')!==false,'Configured mode escaped');ck(strpos($html,'58,709.87')===false,'No screenshot totals');
$scaf_sales_kind='credits';$scaf_sales_metrics=['GBP'=>['issued'=>'200.00','applied'=>'30.00','available'=>'150.00','refunded'=>'20.00']];
ob_start();include dirname(__DIR__).'/modules/sc_admin_fixes/views/sales/metrics.php';$html=ob_get_clean();ck(strpos($html,'GBP 150.00')!==false&&strpos($html,'GBP 20.00')!==false,'Actual available and refund amounts preserved');
echo "PASS rendered fiscal/currency values, four aging segments,mode escaping,credit/refund ledger,no sample totals\n";
