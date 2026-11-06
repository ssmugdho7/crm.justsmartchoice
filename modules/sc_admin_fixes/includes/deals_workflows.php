<?php
defined('BASEPATH') or exit('No direct script access allowed');
function scaf_deals_conversion_allowed($deal)
{
    return is_staff_member() && staff_can('create','projects') && staff_can('edit','deals')
        && (staff_can('view','deals') || (int)$deal->default_deal_owner === (int)get_staff_user_id());
}
/** A single actual customer is required; never pick one arbitrarily from a multi-client deal. */
function scaf_deals_customer($deal)
{
    $clients = json_decode((string)($deal->client_id ?? ''),true);
    if (!is_array($clients)) $clients = ctype_digit((string)($deal->client_id ?? '')) ? [(int)$deal->client_id] : [];
    $clients = array_unique(array_filter(array_map('intval',$clients),static function($id){return $id>0;}));
    return count($clients)===1 ? (int)reset($clients) : null;
}
function scaf_deals_seed_contractor_pipeline($db)
{
    $pipeline=$db->where('pipeline_name','Contractor Sales')->get('tbl_deals_pipelines')->row();
    if (!$pipeline) {$db->insert('tbl_deals_pipelines',['pipeline_name'=>'Contractor Sales','description'=>'Contractor opportunity stages','order'=>0]);$id=$db->insert_id();} else $id=$pipeline->pipeline_id;
    foreach (['Qualified','Site Audit','Proposal','Negotiation','Closed Won'] as $index=>$name) {
        if (!$db->where('pipeline_id',$id)->where('stage_name',$name)->get('tbl_deals_stages')->row()) $db->insert('tbl_deals_stages',['pipeline_id'=>$id,'stage_name'=>$name,'stage_order'=>$index+1]);
    }
    return (int)$id;
}

hooks()->add_action('app_admin_footer','scaf_deals_conversion_script');
function scaf_deals_conversion_script()
{
    if (strtolower(get_instance()->router->fetch_class()) !== 'deals') return;
    echo '<script src="'.e(module_dir_url('sc_admin_fixes','assets/js/deals_workflows.js')).'"></script>';
}
