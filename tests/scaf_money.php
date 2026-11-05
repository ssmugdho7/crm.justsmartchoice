<?php
define('BASEPATH',__DIR__);function get_decimal_places(){return 2;}
require dirname(__DIR__).'/modules/sc_admin_fixes/includes/money.php';
function check($v,$m){if(!$v)throw new RuntimeException($m);}
check(scaf_decimal_add('0.10','0.20')==='0.30','Exact cents');
check(scaf_decimal_sub('0.30','0.10')==='0.20','Exact subtraction');
check(scaf_decimal_add('999999999999999999999.99','0.01')==='1000000000000000000000.00','Arbitrary size');
check(scaf_decimal_sub('1','2')==='-1.00','Negative balance');
check(scaf_decimal_add('-1.25','0.15')==='-1.10','Signed additions');
check(scaf_decimal_cmp('0.10','0.100')===0,'Equal scales');
check(scaf_decimal_cmp('-2','-1')===-1,'Signed comparison');
check(scaf_decimal_normalize('1.005')==='1.01' && scaf_decimal_normalize('-1.005')==='-1.01','Half up final display');
check(scaf_decimal_add('0.12345678','0.00000001',8)==='0.12345679','Eight decimal scale');
check(scaf_decimal_normalize('1.5',0)==='2','Zero decimal currencies');
foreach(['1e3','NaN','abc',''] as $bad){try{scaf_decimal_normalize($bad);throw new RuntimeException('Accepted invalid');}catch(InvalidArgumentException $e){}}
try{scaf_decimal_add(0.1,'0.2');throw new RuntimeException('Accepted float');}catch(InvalidArgumentException $e){}
echo "PASS exact money arithmetic, large balances, signed/zero/eight decimal scales, rounding and rejected floats/invalid values\n";
