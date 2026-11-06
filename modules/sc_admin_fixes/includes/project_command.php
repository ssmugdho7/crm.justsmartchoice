<?php
defined('BASEPATH') or exit('No direct script access allowed');
hooks()->add_filter('app_view_data','scaf_project_command_view',45);
hooks()->add_action('before_render_project_view','scaf_project_command',5);
hooks()->add_action('app_admin_head','scaf_project_command_assets');
function scaf_project_command_assets(){if(scaf_page()==='projects'){scaf_asset('assets/css/project_command.css');scaf_asset('assets/js/project_command.js');}}
function scaf_project_command_view($view){if(is_staff_logged_in()&&scaf_page()==='projects'&&preg_replace('/\.php$/','',$view['path'])==='admin/projects/project_tabs')$view['path']='../../modules/sc_admin_fixes/views/projects/grouped_tabs';return $view;}
function scaf_project_command($id){
    if(!scaf_project_allowed($id))return;$CI=&get_instance();$CI->load->model('projects_model');$project=$CI->projects_model->get($id);$data=scaf_project_command_data($project);$CI->load->view('sc_admin_fixes/projects/command',['command_project'=>$project,'command'=>$data]);
}
function scaf_project_command_data($project){
    $CI=&get_instance();$currency=$CI->projects_model->get_currency($project->id);$code=$currency?$currency->name:get_base_currency()->name;
    $data=['currency'=>$code,'contract'=>null,'changes'=>null,'billed'=>[],'margin'=>null,'margin_percent'=>null,'expenses'=>null,'permits'=>[],'steps'=>[]];
    if(staff_can('create','projects')&&(int)$project->billing_type===1&&trim((string)$project->project_cost)!=='')$data['contract']=scaf_decimal_normalize((string)$project->project_cost);
    $enterprise=is_admin()||staff_can('view_project_controls','smart_choice_enterprise_core');
    if($enterprise&&$CI->db->table_exists(db_prefix().'sce_change_orders')){
        $data['changes']='0';foreach($CI->db->select('amount')->where('project_id',(int)$project->id)->where('status','approved')->get(db_prefix().'sce_change_orders')->result_array() as $row)$data['changes']=scaf_decimal_add($data['changes'],$row['amount']);
    }
    if($enterprise&&$CI->db->table_exists(db_prefix().'sce_permits'))$data['permits']=$CI->db->select('permit_type,permit_number,jurisdiction,status,inspection_status')->where('project_id',(int)$project->id)->limit(100)->get(db_prefix().'sce_permits')->result_array();
    if(staff_can('view','invoices')||staff_can('view_own','invoices')){
        $CI->load->helper('invoices');
        $CI->db->select(db_prefix().'invoices.id,total,currency')->where('project_id',(int)$project->id)->where_not_in('status',[5,6]);
        if(!staff_can('view','invoices'))$CI->db->where(get_invoices_where_sql_for_staff(get_staff_user_id()),null,false);
        $rows=$CI->db->get(db_prefix().'invoices')->result_array();$codes=[];foreach($CI->db->select('id,name')->get(db_prefix().'currencies')->result_array() as $row)$codes[$row['id']]=$row['name'];foreach($rows as $row){$c=$codes[$row['currency']]??'Unknown';$data['billed'][$c]=scaf_decimal_add($data['billed'][$c]??'0',$row['total']);}
    }
    // A full cost view is required; an own-only expense subset cannot support a margin claim.
    if(staff_can('view','expenses')){
        $data['expenses']='0';$compatible=true;foreach($CI->db->select('amount,currency')->where('project_id',(int)$project->id)->get(db_prefix().'expenses')->result_array() as $row){$c=get_currency($row['currency']);if(!$c||$c->name!==$code){$compatible=false;break;}$data['expenses']=scaf_decimal_add($data['expenses'],$row['amount']);}
        if(!$compatible)$data['expenses']=null;
    }
    if($data['contract']!==null&&$data['changes']!==null&&$data['expenses']!==null){$revenue=scaf_decimal_add($data['contract'],$data['changes']);$data['margin']=scaf_decimal_sub($revenue,$data['expenses']);if(scaf_decimal_cmp($revenue,'0')>0)$data['margin_percent']=scaf_decimal_div(scaf_decimal_mul($data['margin'],'100'),$revenue,2);}
    $scope=scaf_project_scope($project);try{$sections=scaf_scope_tasks($scope,$project->start_date);}catch(InvalidArgumentException $e){$sections=[];}
    $tasks=$CI->projects_model->get_tasks((int)$project->id,[],true);
    foreach($sections as $section){$section['task']=null;foreach($tasks as $task){if(mb_strtolower(trim($task['name']))===mb_strtolower(trim($section['name']))||strpos((string)($task['description']??''),$section['marker'])!==false){$section['task']=$task;break;}}$data['steps'][]=$section;}
    $data['scope_text']=trim(html_entity_decode(strip_tags(preg_replace('/<br\s*\/?\s*>|<\/(?:p|div|li)>/i',"\n",(string)$scope)),ENT_QUOTES | ENT_HTML5,'UTF-8'));
    return $data;
}
function scaf_project_tab_groups($tabs){
    $out=['overview'=>[],'execution'=>[],'permits'=>[],'financials'=>[],'collaboration'=>[]];
    foreach($tabs as $key=>$tab){if(isset($tab['visible'])&&!$tab['visible'])continue;$items=!empty($tab['children'])?$tab['children']:[$key=>$tab];foreach($items as $childKey=>$child){if(isset($child['visible'])&&!$child['visible'])continue;$slug=$child['slug']??(is_string($childKey)?$childKey:$key);$child['slug']=$slug;$group=$slug==='project_overview'?'overview':(preg_match('/permit|inspection/i',$slug)?'permits':(preg_match('/task|gantt|milestone|timesheet/i',$slug)?'execution':(preg_match('/invoice|estimate|expense|proposal|contract|sales/i',$slug)?'financials':'collaboration')));$out[$group][]=$child;}}
    return $out;
}
