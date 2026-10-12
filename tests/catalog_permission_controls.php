<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH',__DIR__);$checks=0;$permissions=[];
function check($value,$why){global $checks;if(!$value){throw new RuntimeException($why);}++$checks;}
function has_permission($feature,$staff='',$cap='view'){return in_array($cap,$GLOBALS['permissions'],true);}
function _l($key){return $key;}
function admin_url($path=''){return $path;}
function db_prefix(){return 'tbl';}
function _d($value){return $value;}
function get_variation_values($id){return 'Large';}
function get_coupon_used_times($id){return 0;}
function html_escape($value){return htmlspecialchars($value,ENT_QUOTES,'UTF-8');}
function icon_btn($path,$icon,$classes,$attributes=[]){return '<a href="'.$path.'" class="'.$classes.' '.$icon.'">Action</a>';}
function data_tables_init(...$args){return ['output'=>['aaData'=>[]],'rResult'=>[['id'=>1,'name'=>'Fixture','code'=>'Code','type'=>'%','amount'=>10,'max_uses'=>5,'max_uses_per_client'=>1,'start_date'=>'2026-10-03','end_date'=>'2026-10-04','p_category_id'=>1,'p_category_name'=>'Category','p_category_description'=>'Description','channel'=>'email','trigger_event'=>'order','recipient'=>'staff','active'=>1,'title'=>'Popup','trigger_type'=>'delay','target_pages'=>'catalog','impressions'=>0,'clicks'=>0]]];}
function render_table($name){include dirname(__DIR__).'/modules/products/views/tables/'.$name.'.php';return implode(' ',array_map(fn($row)=>implode(' ',$row),$output['aaData']));}
for($mask=0;$mask<4;$mask++){
    $permissions=['view'];if($mask&1){$permissions[]='edit';}if($mask&2){$permissions[]='delete';}
    foreach(['variation','coupon','product_notification','exit_popup'] as $name){$html=render_table($name);
        check((strpos($html,'/edit/')!==false)===((bool)($mask&1)),$name.' Edit independent of Delete');
        check((strpos($html,'/delete/')!==false)===((bool)($mask&2)),$name.' Delete independent of Edit');
    }
    $html=render_table('product_category');check((strpos($html,'fa-pencil-square')!==false)===((bool)($mask&1)),'Category edit control matches permission');
    check((strpos($html,'delete_category/')!==false)===((bool)($mask&2)),'Category delete control matches permission');
}
echo "PASS: $checks native catalog control checks across View/Edit/Delete combinations\n";
