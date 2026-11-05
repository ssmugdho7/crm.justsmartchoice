<?php
define('BASEPATH',__DIR__);
function get_decimal_places(){return 2;}
$registered=[];
function register_language_files(...$args){}
function register_activation_hook(...$args){}
function hooks(){return new class{function add_filter($name,$callback,...$args){$GLOBALS['registered'][$name][]=$callback;}function add_action($name,$callback,...$args){$GLOBALS['registered'][$name][]=$callback;}};}
require dirname(__DIR__).'/modules/sc_admin_fixes/sc_admin_fixes.php';
if(scaf_decimal_add('0.10','0.20')!=='0.30')throw new RuntimeException('Actual shared exactdecimal helpers unavailable');
if(!in_array('scaf_sales_view',$registered['app_view_data']??[],true))throw new RuntimeException('Sales override not registered through realmodule bootstrap');
if(!function_exists('scaf_sales_invoice_data')||!function_exists('scaf_head_assets'))throw new RuntimeException('Real common/domain includes missing');
try{scaf_decimal_add(0.1,'0.20');throw new RuntimeException('Float accepted');}catch(InvalidArgumentException $expected){}
echo "PASS real module bootstrap registersSales+common hooks,loadsactual exactmoney,floatrejected\n";
