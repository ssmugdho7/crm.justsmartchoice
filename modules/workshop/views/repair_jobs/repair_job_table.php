<?php

defined('BASEPATH') or exit('No direct script access allowed');
$baseCurrency = get_base_currency();

$aColumns = [
    db_prefix() . 'wshop_repair_jobs.id as id',
    'job_tracking_number',
    '1',
    'appointment_date',
    'estimated_completion_date',
    db_prefix().'wshop_appointment_types.name as appointment_name',
    db_prefix().'clients.company',
    db_prefix() . 'wshop_repair_jobs.phonenumber as phonenumber',
    db_prefix() . 'wshop_repair_jobs.name as device_name',
    db_prefix() . 'wshop_models.name as model_name',
    'sale_agent',
    'total',
    db_prefix() . 'wshop_repair_jobs.estimated_hours as estimated_hours',
    db_prefix() . 'wshop_repair_jobs.status as status',
    'invoice_id',
];
$sIndexColumn = db_prefix() . 'wshop_repair_jobs.id';
$sTable = db_prefix() . 'wshop_repair_jobs';

$where = [];
$join= [];
$join         = [
    'LEFT JOIN '.db_prefix().'clients ON '.db_prefix().'clients.userid = '.db_prefix().'wshop_repair_jobs.client_id',
    'LEFT JOIN '.db_prefix().'wshop_appointment_types ON '.db_prefix().'wshop_appointment_types.id = '.db_prefix().'wshop_repair_jobs.appointment_type_id',
    'LEFT JOIN '.db_prefix().'wshop_devices ON '.db_prefix().'wshop_devices.id = '.db_prefix().'wshop_repair_jobs.device_id',
    'LEFT JOIN '.db_prefix().'wshop_models ON '.db_prefix().'wshop_models.id = '.db_prefix().'wshop_devices.model_id',
];


$client_filter = $this->ci->input->post('client_filter');
$appointment_type_filter = $this->ci->input->post('appointment_type_filter');
$from_date_filter = $this->ci->input->post('from_date_filter');
$to_date_filter = $this->ci->input->post('to_date_filter');
$device_filter = $this->ci->input->post('device_filter');

if($client_filter){
    $where[] = 'AND '.db_prefix().'wshop_repair_jobs.client_id ='.$client_filter;
}

if($appointment_type_filter){
    $where[] = 'AND '.db_prefix().'wshop_repair_jobs.appointment_type_id ='.$appointment_type_filter;
}
if($device_filter){
    $where[] = 'AND '.db_prefix().'wshop_repair_jobs.device_id ='.$device_filter;
}

if($from_date_filter != '' &&  $to_date_filter != ''){
    $from_date_filter = to_sql_date($from_date_filter);
    $to_date_filter = to_sql_date($to_date_filter);

    array_push($where, ' AND ( (date_format('.db_prefix().'wshop_repair_jobs.appointment_date,"%Y-%m-%d") BETWEEN "'.$from_date_filter.'" AND "'.$to_date_filter.'") )');

}elseif( $from_date_filter != '' && $to_date_filter == ''){
    $from_date_filter = to_sql_date($from_date_filter);

    array_push($where, ' AND ( (date_format('.db_prefix().'wshop_repair_jobs.appointment_date,"%Y-%m-%d") >= "'.$from_date_filter.'" ))');

}elseif($from_date_filter == '' && $to_date_filter != ''){
    $to_date_filter = to_sql_date($to_date_filter);

    array_push($where, ' AND ( (date_format('.db_prefix().'wshop_repair_jobs.appointment_date,"%Y-%m-%d") <= "'.$to_date_filter.'" ))');
}

if(!is_admin() && !has_permission('workshop_repair_job','','view')){
      //View own
    $where[] = 'AND ('.db_prefix().'wshop_repair_jobs.staffid = '.get_staff_user_id() .' OR '.db_prefix().'wshop_repair_jobs.sale_agent = '.get_staff_user_id() .')' ;
}


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix().'wshop_repair_jobs.client_id as client_id', 'appointment_type_id']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['id'];

    if((has_permission('workshop_repair_job', '', 'view') || has_permission('workshop_repair_job', '', 'view_own') || is_admin())){
        $options = '<a href="' .admin_url('workshop/repair_job_detail/' . $aRow['id']) . '?tab=detail" >' . $aRow['job_tracking_number'] . '</a>';
    }else{
        $options = $aRow['job_tracking_number'];
    }

    $options .= '<div class="row-options">';
    if((has_permission('workshop_repair_job', '', 'view') || has_permission('workshop_repair_job', '', 'view_own') || is_admin())){
        $options .= '<a href="' .admin_url('workshop/repair_job_detail/' . $aRow['id']) . '?tab=detail" >' . _l('view') . '</a>';
    }

    if((has_permission('workshop_repair_job', '', 'edit')|| is_admin()) && ($aRow['status'] == 'Booked_In' || $aRow['status'] == 'In_Progress') ){
        $options .= ' | <a href="' .admin_url('workshop/add_edit_repair_job/' . $aRow['id'] ) . '" >' . _l('edit') . '</a>';
    }
    if (has_permission('workshop_repair_job', '', 'delete')) {
        $options .= ' | <a href="#" onclick="delete_repair_job('.$aRow['id'].'); return false;" class="text-danger">' . _l('delete') . '</a>';
    }
    $options .= '</div>';
    $row[] = $options;

    $row[] = format_repair_job_number($aRow['id']);
    $row[] = _dt($aRow['appointment_date']);
    $row[] = _dt($aRow['estimated_completion_date']);
    $row[] = $aRow['appointment_name'];
    $row[] = $aRow[db_prefix().'clients.company'];
    $row[] = $aRow['phonenumber'];
    $row[] = $aRow['device_name'];
    $row[] = $aRow['model_name'];
    $row[] = get_staff_full_name($aRow['sale_agent']);
    $row[] = app_format_money($aRow['total'], $baseCurrency);
    $row[] = $aRow['estimated_hours'];
    $row[] = render_repair_job_status_html($aRow['id'], '', $aRow['status']);
    $row[] = $aRow['invoice_id'] != null ? '<a href='.admin_url('invoices/list_invoices/'.$aRow['invoice_id']).' target="_blank">'.format_invoice_number($aRow['invoice_id']).'</a>' : '---';

    $output['aaData'][] = $row;
}

