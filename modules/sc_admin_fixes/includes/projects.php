<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__.'/project_data.php';
hooks()->add_filter('projects_table_row_data','scaf_projects_row',20,2);
hooks()->add_action('before_render_project_view','scaf_projects_execution');
hooks()->add_action('app_admin_footer','scaf_projects_footer');
function scaf_projects_row($row,$project) {
    $id=(int)$project['id'];
    $row[0]='<input type="checkbox" class="scaf-project-select" value="'.$id.'" aria-label="Select project '.$id.'"> '.$row[0];
    if(strpos($row[7]??'','[Status Not Found]')!==false || trim($row[7]??'')==='')$row[7]='<span class="label label-default">Unknown / Unassigned</span>';
    return $row;
}
function scaf_projects_execution($id) {
    $CI=&get_instance();$group=$CI->input->get('group');
    if($group!=='project_tasks' && $group!=='project_gantt')return;
    $CI->load->view('sc_admin_fixes/projects/execution',['project_id'=>(int)$id,'group'=>$group]);
}
function scaf_projects_footer() {
    $CI=&get_instance();if($CI->uri->segment(2)!=='projects')return;
    $CI->load->model('projects_model');
    $data=['statuses'=>$CI->projects_model->get_project_statuses()];
    if(!$CI->uri->segment(3) || $CI->uri->segment(3)==='index')$data['kpis']=scaf_projects_kpis();
    $CI->load->view('sc_admin_fixes/projects/footer',$data);
}
function scaf_projects_kpis() {
    $CI=&get_instance();$CI->load->model('projects_model');
    $where=[];if(!staff_can('view','projects'))$where=db_prefix().'projects.id IN (SELECT project_id FROM '.db_prefix().'project_members WHERE staff_id='.(int)get_staff_user_id().')';
    $projects=$CI->projects_model->get('',$where);$currencies=[];foreach($CI->db->select('id,name')->get(db_prefix().'currencies')->result_array() as $currencyRow)$currencies[$currencyRow['id']]=$currencyRow['name'];$totals=['active'=>0,'overdue'=>0,'contracts'=>[],'billed'=>[],'permitted'=>0];
    $permit=$CI->db->where('fieldto','projects')->where('active',1)->where('slug','projects_permit_needed')->get(db_prefix().'customfields')->row_array();
    if(!$permit)$permit=$CI->db->where('fieldto','projects')->where('active',1)->where('name','Permit Needed:')->get(db_prefix().'customfields')->row_array();
    $totals['permit_configured']=(bool)$permit;
    foreach($projects as $project) {
        if((int)$project['status']!==2)continue;
        $totals['active']++;
        if(!empty($project['deadline']) && $project['deadline']<date('Y-m-d'))$totals['overdue']++;
        if($permit && in_array(strtolower(trim(get_custom_field_value($project['id'],$permit['id'],'projects',false))),['1','yes'],true))$totals['permitted']++;
        if(staff_can('create','projects')) {
            $currency=$CI->projects_model->get_currency($project['id']);$code=$currency ? $currency->name : 'Unknown currency';
            if((int)$project['billing_type']===1)$totals['contracts'][$code]=scaf_decimal_add($totals['contracts'][$code]??'0',$project['project_cost']??'0');
        }
        if(staff_can('view','invoices')) {
            $invoices=$CI->db->select('currency,total')->where('project_id',$project['id'])->where_not_in('status',[5,6])->get(db_prefix().'invoices')->result_array();
            foreach($invoices as $invoice){$code=$currencies[$invoice['currency']]??'Unknown currency';$totals['billed'][$code]=scaf_decimal_add($totals['billed'][$code]??'0',$invoice['total']);}
        }
    }
    return $totals;
}
