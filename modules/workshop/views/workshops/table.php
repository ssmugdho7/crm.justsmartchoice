<?php

defined('BASEPATH') or exit('No direct script access allowed');


$aColumns = [
    db_prefix() . 'wshop_workshops.id as id',
    db_prefix() . 'wshop_workshops.name as workshop_name',
    db_prefix() . 'wshop_repair_jobs.name as repair_name',
    db_prefix() . 'wshop_categories.name as report_type_name',
    db_prefix() . 'wshop_workshops.report_status_id as report_status_id',
    db_prefix() . 'wshop_workshops.sale_agent as sale_agent',
    db_prefix() . 'wshop_workshops.from_date as from_date',
    db_prefix() . 'wshop_workshops.to_date as to_date',
    db_prefix() . 'wshop_workshops.parts_information as parts_information',
    db_prefix() . 'wshop_workshops.description as description',
    db_prefix() . 'wshop_workshops.visible_to_customer as visible_to_customer',
    
];
$sIndexColumn = db_prefix() . 'wshop_workshops.id';
$sTable = db_prefix() . 'wshop_workshops';

$where = [];
$join= [];
$join         = [
    'LEFT JOIN '.db_prefix().'wshop_repair_jobs ON '.db_prefix().'wshop_repair_jobs.id = '.db_prefix().'wshop_workshops.repair_job_id',
    'LEFT JOIN '.db_prefix().'wshop_categories ON '.db_prefix().'wshop_categories.id = '.db_prefix().'wshop_workshops.report_type_id',
];

$repair_job_filter = $this->ci->input->post('repair_job_filter');
$device_filter = $this->ci->input->post('device_filter');
if($device_filter){
    $where[] = 'AND '.db_prefix().'wshop_workshops.repair_job_id IN (SELECT id FROM '.db_prefix().'wshop_repair_jobs WHERE '.db_prefix().'wshop_repair_jobs.device_id = '.$device_filter.')';
}
if($repair_job_filter){
    $where[] = 'AND '.db_prefix().'wshop_workshops.repair_job_id ='.$repair_job_filter;
}
$report_type_filter = $this->ci->input->post('report_type_filter');
if($report_type_filter){
    $where[] = 'AND '.db_prefix().'wshop_workshops.report_type_id ='.$report_type_filter;
}
$report_status_filter = $this->ci->input->post('report_status_filter');
if($report_status_filter){
    $where[] = 'AND '.db_prefix().'wshop_workshops.report_status_id ='.$report_status_filter;
}

if(!is_admin() && !has_permission('workshop_workshop','','view')){
      //View own
    $where[] = 'AND '.db_prefix().'wshop_workshops.staffid = '.get_staff_user_id() ;
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['repair_job_id', db_prefix().'wshop_repair_jobs.job_tracking_number as job_tracking_number']);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['id'];

    if((has_permission('workshop_workshop', '', 'view') || has_permission('workshop_workshop', '', 'view_own') || is_admin())){
        $options = $aRow['workshop_name'];
    }else{
        $options = $aRow['workshop_name'];
    }

    $options .= '<div class="row-options">';

    if((has_permission('workshop_workshop', '', 'edit') || has_permission('workshop_workshop', '', 'create') || is_admin())){
        $options .= '<a href="#" onclick="workshop_modal('.$aRow['id'].', '.$aRow['repair_job_id'].'); return false;" >' . _l('edit') . '</a>';
    }
    if (has_permission('workshop_workshop', '', 'delete')) {
        $options .= ' | <a href="#" onclick="delete_workshop('.$aRow['id'].'); return false;" class="text-danger">' . _l('delete') . '</a>';
    }
    $options .= '</div>';
    $row[] = $options;

    $row[] = '<a href="'.admin_url('workshop/repair_job_detail/'.$aRow['repair_job_id'].'?tab=workshop').'" target="_blank">'.$aRow['job_tracking_number'].' '.$aRow['repair_name'].'</a>';
    $row[] = $aRow['report_type_name'];
    $row[] = wshop_get_category_name($aRow['report_status_id']);
    $row[] = get_staff_full_name($aRow['sale_agent']);
    $row[] = $aRow['from_date'] != null ? _dt($aRow['from_date']) : '---';
    $row[] = $aRow['to_date'] != null ? _dt($aRow['to_date']) : '---';
    $row[] = $aRow['parts_information'];
    $row[] = $aRow['description'];

    $status = '';
    $checked = '';
    if ($aRow['visible_to_customer'] == 1) {
        $checked = 'checked';
    }

    $status .= '<div class="onoffswitch">
    <input type="checkbox" ' . (((is_admin() || !has_permission('workshop_setting', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_workshop_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['visible_to_customer'] . '" ' . $checked . '>
    <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
    </div>';

    $row[] = $status;

    $output['aaData'][] = $row;
}


