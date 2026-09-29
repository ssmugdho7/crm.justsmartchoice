<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'wshop_inspections.id as id',
    db_prefix() . 'wshop_inspections.id as id',
    db_prefix() . 'wshop_categories.name as inspection_type_name',
    db_prefix() . 'wshop_inspection_templates.name as inspection_template_name',
    db_prefix() . 'wshop_devices.name as device_name',
    db_prefix() . 'clients.company as client_name',
    db_prefix() . 'wshop_repair_jobs.name as repair_job_name',
    db_prefix() . 'wshop_inspections.start_date as start_date',
    db_prefix() . 'wshop_inspections.end_date as end_date',
    db_prefix() . 'wshop_intervals.name as interval_name',
    db_prefix() . 'wshop_inspections.next_inspection_date as next_inspection_date',
    db_prefix() . 'wshop_inspections.next_inspection_alert as next_inspection_alert',
    db_prefix() . 'wshop_inspections.status as status',
    db_prefix() . 'wshop_inspections.visible_to_customer as visible_to_customer',
    
];
$sIndexColumn = db_prefix() . 'wshop_inspections.id';
$sTable = db_prefix() . 'wshop_inspections';

$where = [];
$join= [];
$join         = [
    'LEFT JOIN '.db_prefix().'wshop_repair_jobs ON '.db_prefix().'wshop_repair_jobs.id = '.db_prefix().'wshop_inspections.repair_job_id',
    'LEFT JOIN '.db_prefix().'wshop_categories ON '.db_prefix().'wshop_categories.id = '.db_prefix().'wshop_inspections.inspection_type_id',
    'LEFT JOIN '.db_prefix().'wshop_inspection_templates ON '.db_prefix().'wshop_inspection_templates.id = '.db_prefix().'wshop_inspections.inspection_template_id',
    'LEFT JOIN '.db_prefix().'wshop_devices ON '.db_prefix().'wshop_devices.id = '.db_prefix().'wshop_inspections.device_id',
    'LEFT JOIN '.db_prefix().'clients ON '.db_prefix().'clients.userid = '.db_prefix().'wshop_inspections.client_id',
    'LEFT JOIN '.db_prefix().'wshop_intervals ON '.db_prefix().'wshop_intervals.id = '.db_prefix().'wshop_inspections.interval_id',
];

$from_date_filter = $this->ci->input->post('from_date_filter');
$to_date_filter = $this->ci->input->post('to_date_filter');
$client_filter = $this->ci->input->post('client_filter');
$inspection_type_filter = $this->ci->input->post('inspection_type_filter');
$device_filter = $this->ci->input->post('device_filter');
$status_filter = $this->ci->input->post('status_filter');
$repair_job_filter = $this->ci->input->post('repair_job_filter');
$device_filter = $this->ci->input->post('device_filter');

if($device_filter){
    $where[] = 'AND '.db_prefix().'wshop_inspections.device_id ='.$device_filter;
}

if($from_date_filter != '' &&  $to_date_filter != ''){
    $from_date_filter = to_sql_date($from_date_filter);
    $to_date_filter = to_sql_date($to_date_filter);

    array_push($where, ' AND ( (date_format('.db_prefix().'wshop_inspections.start_date,"%Y-%m-%d")  >= "'.$from_date_filter.'" AND date_format('.db_prefix().'wshop_inspections.end_date,"%Y-%m-%d")  <= "'.$to_date_filter.'") )');

}elseif( $from_date_filter != '' && $to_date_filter == ''){
    $from_date_filter = to_sql_date($from_date_filter);

    array_push($where, ' AND ( (date_format('.db_prefix().'wshop_inspections.start_date,"%Y-%m-%d") >= "'.$from_date_filter.'" ))');

}elseif($from_date_filter == '' && $to_date_filter != ''){
    $to_date_filter = to_sql_date($to_date_filter);

    array_push($where, ' AND ( (date_format('.db_prefix().'wshop_inspections.end_date,"%Y-%m-%d") <= "'.$to_date_filter.'" ))');
}

if($client_filter){
    $where[] = 'AND '.db_prefix().'wshop_inspections.client_id = '.$client_filter;
}
if($inspection_type_filter){
    $where[] = 'AND '.db_prefix().'wshop_inspections.inspection_type_id = '.$inspection_type_filter;
}
if($device_filter){
    $where[] = 'AND '.db_prefix().'wshop_inspections.device_id = '.$device_filter;
}
if($status_filter){
    $where[] = 'AND '.db_prefix().'wshop_inspections.status = "'.$status_filter.'"';
}
if($repair_job_filter){
    $where[] = 'AND '.db_prefix().'wshop_inspections.repair_job_id = '.$repair_job_filter;
}

if(!is_admin() && !has_permission('workshop_inspection','','view')){
      //View own
    $where[] = 'AND '.db_prefix().'wshop_inspections.staffid = '.get_staff_user_id() ;
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['repair_job_id', db_prefix().'wshop_repair_jobs.job_tracking_number as job_tracking_number','inspection_type_id']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['id'];

    if((has_permission('workshop_inspection', '', 'view') || has_permission('workshop_inspection', '', 'view_own') || is_admin())){
        $options = '<a href="'.admin_url('workshop/inspection_detail/'.$aRow['id'].'?tab=detail').'" >'.format_inspection_number($aRow['id']). '</a>';
    }else{
        $options = format_inspection_number($aRow['id']);
    }

    $options .= '<div class="row-options">';

    if((has_permission('workshop_inspection', '', 'view') || has_permission('workshop_inspection', '', 'view_own') || is_admin())){
        $options .= '<a href="'.admin_url('workshop/inspection_detail/'.$aRow['id']).'?tab=detail" >' . _l('view') . '</a>';
    }
    if((has_permission('workshop_inspection', '', 'edit') || has_permission('workshop_inspection', '', 'create') || is_admin()) && $aRow['status'] == 'Open'){
        $options .= ' | <a href="#" onclick="inspection_modal('.$aRow['id'].', '.$aRow['repair_job_id'].'); return false;" >' . _l('edit') . '</a>';
    }
    if (has_permission('workshop_inspection', '', 'delete')) {
        $options .= ' | <a href="#" onclick="delete_inspection('.$aRow['id'].'); return false;" class="text-danger">' . _l('delete') . '</a>';
    }
    $options .= '</div>';
    $row[] = $options;
    $row[] = $aRow['inspection_type_name'];
    $row[] = $aRow['inspection_template_name'];
    $row[] = $aRow['device_name'];
    $row[] = $aRow['client_name'];
    $row[] = $aRow['repair_job_name'];
    $row[] = $aRow['start_date'] != null ? _dt($aRow['start_date']) : '---';
    $row[] = $aRow['end_date'] != null ? _dt($aRow['end_date']) : '---';
    $row[] = $aRow['interval_name'];
    $row[] = $aRow['next_inspection_date'] != null ? _dt($aRow['next_inspection_date']) : '---';
    $row[] = $aRow['next_inspection_alert'];
    $row[] = render_inspection_status_html($aRow['id'], '', $aRow['status']);

    $status = '';
    $checked = '';
    if ($aRow['visible_to_customer'] == 1) {
        $checked = 'checked';
    }

    $status .= '<div class="onoffswitch">
    <input type="checkbox" ' . (((is_admin() || !has_permission('workshop_inspection', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_inspection_visible" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['visible_to_customer'] . '" ' . $checked . '>
    <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
    </div>';

    $row[] = $status;

    $output['aaData'][] = $row;
}


