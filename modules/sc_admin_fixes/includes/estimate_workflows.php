<?php
defined('BASEPATH') or exit('No direct script access allowed');
function scaf_estimate_fingerprint($estimate)
{
    return hash('sha256',json_encode([$estimate->clientid,$estimate->currency,$estimate->total,$estimate->subtotal,$estimate->discount_total,$estimate->discount_percent,$estimate->discount_type,$estimate->adjustment,$estimate->items]));
}
function scaf_estimate_draw($estimate, $percent, $target = null)
{
    if(!is_string($percent)||!preg_match('/^\d{1,3}(?:\.\d{1,2})?$/D',$percent)||scaf_decimal_cmp($percent,'0')<=0||scaf_decimal_cmp($percent,'100')>=0)throw new InvalidArgumentException('Choose a deposit percentage greater than 0 and less than 100.');
    $ratio=scaf_decimal_div($percent,'100',8);$items=[];$subtotal='0';$taxGroups=[];
    foreach($estimate->items as $item){
        if(!empty($item['is_optional'])&&empty($item['is_selected']))continue;
        $net=scaf_decimal_mul(scaf_decimal_mul((string)$item['qty'],(string)$item['rate'],8),$ratio);
        $taxes=get_estimate_item_taxes($item['id']);$names=[];
        foreach($taxes as $tax){$names[]=$tax['taxname'];$key=$tax['taxname'];$taxGroups[$key]=scaf_decimal_add($taxGroups[$key]??'0',scaf_decimal_div(scaf_decimal_mul($net,(string)$tax['taxrate'],8),'100',8),8);}
        $items[]=['description'=>$item['description'],'long_description'=>clear_textarea_breaks($item['long_description'])."\nDraw: ".$percent.'% of '.$item['qty'].' '.$item['unit'].' at '.$item['rate'],'qty'=>'1','rate'=>$net,'unit'=>'draw','taxname'=>$names,'order'=>$item['item_order']];$subtotal=scaf_decimal_add($subtotal,$net);
    }
    if(!$items)throw new InvalidArgumentException('This estimate has no billable items.');
    $discount=scaf_decimal_cmp((string)$estimate->discount_percent,'0')>0?scaf_decimal_div(scaf_decimal_mul($subtotal,(string)$estimate->discount_percent,8),'100'):scaf_decimal_mul((string)$estimate->discount_total,$ratio);
    $taxTotal='0';foreach($taxGroups as $amount){if($estimate->discount_type==='before_tax'&&scaf_decimal_cmp($subtotal,'0')>0)$amount=scaf_decimal_mul($amount,scaf_decimal_sub('1',scaf_decimal_div($discount,$subtotal,8),8),8);$taxTotal=scaf_decimal_add($taxTotal,$amount,8);}
    $taxTotal=scaf_decimal_normalize($taxTotal);$target=$target??scaf_decimal_mul((string)$estimate->total,$ratio);
    $adjustment=scaf_decimal_sub($target,scaf_decimal_add(scaf_decimal_sub($subtotal,$discount),$taxTotal));
    return ['items'=>$items,'subtotal'=>$subtotal,'total'=>$target,'discount_total'=>$discount,'discount_percent'=>(string)$estimate->discount_percent,'discount_type'=>$estimate->discount_type,'adjustment'=>$adjustment,'total_tax'=>$taxTotal];
}
hooks()->add_filter('before_invoice_added','scaf_estimate_draw_invoice',5);
function scaf_estimate_draw_invoice($hook)
{
    $draw=$GLOBALS['scaf_estimate_draw_context']??null;if(!$draw)return $hook;
    unset($GLOBALS['scaf_estimate_draw_context']);
    foreach($draw['items'] as &$item){foreach($hook['items'] as $original){if((string)$original['order']===(string)$item['order']&&!empty($original['custom_fields'])){$item['custom_fields']=$original['custom_fields'];break;}}}unset($item);
    $hook['items']=$draw['items'];foreach(['subtotal','total','discount_total','discount_percent','discount_type','adjustment','total_tax'] as $field)$hook['data'][$field]=$draw[$field];
    $hook['data']['adminnote'].="\nPercentage draw draft. Source estimate #".$draw['source'].'. Remaining unbilled: '.$draw['remaining'].'. Review tax allocation and rounding adjustment before issuing.';
    return $hook;
}
hooks()->add_action('app_admin_head','scaf_estimate_assets');
function scaf_estimate_assets(){if(strtolower(get_instance()->router->fetch_class())!=='estimates')return;foreach(['css','js'] as $type){$path='assets/'.$type.'/estimate_workflows.'.$type;$url=e(module_dir_url('sc_admin_fixes',$path).'?v='.filemtime(dirname(__DIR__).'/'.$path));echo $type==='css'?'<link rel="stylesheet" href="'.$url.'">':'<script defer src="'.$url.'"></script>';}}
hooks()->add_filter('app_view_data','scaf_estimate_view',60);
function scaf_estimate_view($view)
{
    if(!is_staff_logged_in()||strtolower(get_instance()->router->fetch_class())!=='estimates')return $view;
    if($view['path']==='admin/estimates/estimate_preview_template'){$view['path']='../../modules/sc_admin_fixes/views/estimates/preview';}
    return $view;
}
function scaf_estimate_dialog_data($estimate){
    $currency=get_currency($estimate->currency);$metadata=json_decode(get_option('scaf_estimate_draw_'.(int)$estimate->id)?:'{}',true)?:[];
    if(!$metadata&&!empty($estimate->invoiceid))$metadata=['mode'=>'full','invoice_id'=>(int)$estimate->invoiceid];
    return ['id'=>(int)$estimate->id,'number'=>format_estimate_number($estimate->id),'customer'=>get_company_name($estimate->clientid),'currency'=>$currency?$currency->name:'','total'=>scaf_decimal_normalize((string)$estimate->total),'version'=>scaf_estimate_fingerprint($estimate),'metadata'=>$metadata];
}
hooks()->add_filter('estimates_table_row_data','scaf_estimate_table_actions',30,2);
function scaf_estimate_table_actions($row,$estimate){
    $expiry=$estimate['expirydate']??'';$status=(int)($estimate[db_prefix().'estimates.status']??0);
    $days=$expiry&&$expiry!=='0000-00-00'?(int)(new DateTimeImmutable(date('Y-m-d')))->diff(new DateTimeImmutable($expiry))->format('%r%a'):null;
    if($days!==null&&$days>=0&&$days<=7&&in_array($status,[2,5],true))$row[0].='<span class="scaf-estimate-expiry">'.e($days.' days to expiry').'</span>';
    if(staff_can('create','invoices')&&staff_can('edit','estimates'))$row[0].='<button type="button" class="btn btn-default btn-xs scaf-estimate-convert" data-estimate="'.(int)$estimate['id'].'">'.e(_l('estimate_convert_to_invoice')).'</button>';
    return $row;
}
