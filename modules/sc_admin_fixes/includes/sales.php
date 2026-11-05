<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__ . '/sales_data.php';
hooks()->add_filter('app_view_data', 'scaf_sales_view', 40);
function scaf_sales_view($view)
{
    $CI=&get_instance();
    if (!is_staff_logged_in() || !in_array(strtolower($CI->router->fetch_class()),['proposals','invoices','payments','credit_notes'],true)) { return $view; }
    $paths=[
        'admin/proposals/proposals_preview_template'=>'proposal',
        'admin/invoices/invoices_total_template'=>'invoices',
        'admin/invoices/record_payment_template'=>'record_payment',
        'admin/invoices/invoice_preview_template'=>'invoice_preview',
        'admin/payments/manage'=>'payments',
        'admin/payments/payment'=>'receipt',
        'admin/credit_notes/manage'=>'credits',
        'admin/credit_notes/credit_note_preview_template'=>'credit_preview',
        'admin/credit_notes/apply_credits_to_invoices'=>'allocation',
    ];
    $path=preg_replace('/\.php$/','',$view['path']);
    if(!isset($paths[$path])) {return $view;}
    // Fixed path whitelist; never include an input-supplied template.
    $view['data']['scaf_sales_native']=$path;
    $view['data']['scaf_sales_kind']=$paths[$path];
    if($paths[$path]==='invoices') {$view['data']['scaf_sales_metrics']=scaf_sales_invoice_data();}
    if($paths[$path]==='payments') {$view['data']['scaf_sales_metrics']=scaf_sales_payment_data();}
    if($paths[$path]==='credits') {$view['data']['scaf_sales_metrics']=scaf_sales_credit_data();}
    $view['path']='../../modules/sc_admin_fixes/views/sales/wrapper';
    return $view;
}
function scaf_sales_label($english,$spanish)
{
    $CI=&get_instance();
    $language=function_exists('get_staff_default_language')?get_staff_default_language():get_option('active_language');
    return $language==='spanish'?$spanish:$english;
}
function scaf_sales_percent($part,$whole)
{
    return scaf_decimal_cmp((string)$whole,'0')>0 ? min(100,max(0,round((float)$part/(float)$whole*100,1))) : 0;
}

hooks()->add_action('app_admin_head', 'scaf_sales_status_config');
function scaf_sales_status_config()
{
    $CI=&get_instance();
    if(strtolower($CI->router->fetch_class())!=='credit_notes') {return;}
    $CI->load->model('credit_notes_model');
    $statuses=[];
    foreach($CI->credit_notes_model->get_statuses() as $status){$statuses[(string)$status['id']]=$status['name'];}
    echo '<script>window.scafCreditStatuses='.json_encode($statuses,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';</script>';
}
