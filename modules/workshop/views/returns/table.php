<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'wshop_return_deliveries.id as id',
    db_prefix() . 'wshop_return_deliveries.name as _name',
    db_prefix().'clients.company as company',
    db_prefix().'wshop_repair_jobs.job_tracking_number as job_tracking_number',
    db_prefix().'wshop_categories.name as delivery_method',
    db_prefix().'wshop_return_deliveries.delivery_method_id as delivery_method_id',
    db_prefix().'wshop_return_deliveries.expected_delivery_date as expected_delivery_date',
    db_prefix() . 'wshop_return_deliveries.status as transaction_status',
    '1',
];
$sIndexColumn = db_prefix() . 'wshop_return_deliveries.id';
$sTable = db_prefix() . 'wshop_return_deliveries';

$where = [];
$join= [];
$join         = [
    'LEFT JOIN '.db_prefix().'wshop_repair_jobs ON '.db_prefix().'wshop_repair_jobs.id = '.db_prefix().'wshop_return_deliveries.repair_job_id',
    'LEFT JOIN '.db_prefix().'clients ON '.db_prefix().'clients.userid = '.db_prefix().'wshop_repair_jobs.client_id',
    'LEFT JOIN '.db_prefix().'wshop_categories ON '.db_prefix().'wshop_categories.id = '.db_prefix().'wshop_return_deliveries.delivery_method_id',
];

if($transaction_type){
    $where[] = 'AND '.db_prefix().'wshop_return_deliveries.transaction_type ="'.$transaction_type.'"';
}


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['client_id', 'transaction_type', 'repair_job_id']);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['id'];
    $row[] = $aRow['_name'];
    $row[] = '<a href="'.admin_url('clients/client/'.$aRow['client_id']).'" target="_blank">'.$aRow['company'].'</a>';
    $row[] = $aRow['job_tracking_number'];
    $row[] = $aRow['delivery_method'];
    $row[] = $aRow['expected_delivery_date'] != null ? _d($aRow['expected_delivery_date']) : '---';
    $row[] = render_transaction_status_html($aRow['id'], '', $aRow['transaction_status']);

    $options = '';

    if((has_permission('workshop_repair_job', '', 'view') || has_permission('workshop_repair_job', '', 'view_own') || is_admin())){

        $options .= icon_btn(admin_url('workshop/device_detail/'.$aRow['id']), 'fa-solid fa-eye', 'btn-default', [
            
        ]);
    }

    if((has_permission('workshop_repair_job', '', 'edit') || has_permission('workshop_repair_job', '', 'create') || is_admin())){

        $options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
            'onclick'    => 'transaction_modal(' . $aRow['id'] . ',' . $aRow['repair_job_id'] . ',\''.$aRow['transaction_type'].'\'); return false;',
        ]);
    }

    if((has_permission('workshop_repair_job', '', 'edit') || has_permission('workshop_repair_job', '', 'create') || is_admin())){
        $options .= icon_btn('#', 'fa fa-remove', 'btn-danger', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top',
            'onclick'    => 'delete_transaction(' . $aRow['id'] . '); return false;',
        ]);
    }

    $row[] = $options;

    $output['aaData'][] = $row;
}


