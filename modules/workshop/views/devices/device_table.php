<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'wshop_devices.id as id',
    'primary_profile_image',
    db_prefix() . 'wshop_devices.code as device_code',
    db_prefix() . 'wshop_devices.name as device_name',
    'serial_no',
    db_prefix().'clients.company',
    db_prefix().'wshop_models.name as model_name',
    db_prefix().'wshop_categories.name as category_name',
    db_prefix().'wshop_manufacturers.name as manufacturer_name',
    'purchase_date',
    'last_maintenance', //last_maintenance_date
    'next_maintenance', //next_maintenance_date
    'warranty_period_months',
    'warranty_expiry_date',
    '5', //warranty_status
    db_prefix() . 'wshop_devices.status as device_status',
    '1',
];
$sIndexColumn = db_prefix() . 'wshop_devices.id';
$sTable = db_prefix() . 'wshop_devices';

$where = [];
$join= [];
$join         = [
    'LEFT JOIN '.db_prefix().'clients ON '.db_prefix().'clients.userid = '.db_prefix().'wshop_devices.client_id',
    'LEFT JOIN '.db_prefix().'wshop_models ON '.db_prefix().'wshop_models.id = '.db_prefix().'wshop_devices.model_id',
    'LEFT JOIN '.db_prefix().'wshop_manufacturers ON '.db_prefix().'wshop_manufacturers.id = '.db_prefix().'wshop_models.manufacturer_id',
    'LEFT JOIN '.db_prefix().'wshop_categories ON '.db_prefix().'wshop_categories.id = '.db_prefix().'wshop_models.category_id',
];


$client_filter = $this->ci->input->post('client_filter');
$devices_filter = $this->ci->input->post('devices_filter');
$model_filter = $this->ci->input->post('model_filter');
$warranty_status_filter = $this->ci->input->post('warranty_status_filter');

if($client_filter){
    $where[] = 'AND '.db_prefix().'wshop_devices.client_id ='.$client_filter;
}

if($devices_filter){
    $where[] = 'AND '.db_prefix().'wshop_devices.id ='.$devices_filter;
}

if($model_filter){
    $where[] = 'AND '.db_prefix().'wshop_devices.model_id ='.$model_filter;
}

if($warranty_status_filter){
    if($warranty_status_filter == 'being_under_warranty'){
        $where[] = 'AND DATE_FORMAT('.db_prefix().'wshop_devices.warranty_expiry_date, "%Y-%m-%d") > "'.date('Y-m-d').'"';
    }elseif($warranty_status_filter == 'out_of_warranty'){
        $where[] = 'AND DATE_FORMAT('.db_prefix().'wshop_devices.warranty_expiry_date, "%Y-%m-%d") <= "'.date('Y-m-d').'"';
    }
}

if(!is_admin() && !has_permission('workshop_device','','view')){
      //View own
    $where[] = 'AND '.db_prefix().'wshop_devices.staffid = '.get_staff_user_id() ;
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['client_id', 'model_id']);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['id'];

    if ($aRow['primary_profile_image'] != '' && file_exists(MAIN_IMAGE_DEVICES_IMAGES_FOLDER.$aRow['id'].'/'.$aRow['primary_profile_image'])) {
        $row[] = '<img class="manufacturer-img" id="wizardPicturePreview" src="' . site_url('modules/workshop/uploads/main_image_devices/'.$aRow['id'].'/'.$aRow['primary_profile_image']) . '" alt="'.$aRow['primary_profile_image'].'" >';
    }else{
        $row[] = '<img class="manufacturer-img" id="wizardPicturePreview" src="' . site_url('modules/workshop/assets/images/upload-image-icon.png') . '" >';
    }

    $row[] = $aRow['device_code'];
    $row[] = $aRow['device_name'];
    $row[] = $aRow['serial_no'];
    $row[] = $aRow[db_prefix().'clients.company'];
    $row[] = $aRow['model_name'];
    $row[] = $aRow['category_name'];
    $row[] = $aRow['manufacturer_name'];
    $row[] = $aRow['purchase_date'] != null ? _d($aRow['purchase_date']) : '---';
    $row[] = _d($aRow['last_maintenance']);
    $row[] = _d($aRow['next_maintenance']);
    $row[] = $aRow['warranty_period_months'] > 0 ? $aRow['warranty_period_months'].' '._l('wshop_months') : '---';
    $row[] = $aRow['warranty_expiry_date'] != null ? _d($aRow['warranty_expiry_date']) : '---';
    
    $warranty_status = '---';
    if($aRow['warranty_expiry_date'] != null){
        if(strtotime($aRow['warranty_expiry_date']) > strtotime(date('Y-m-d'))){
            $warranty_status = '<span class="label label-success">'._l('wshop_being_under_warranty').'</span>';
        }else{
            $warranty_status = '<span class="label label-warning">'._l('wshop_out_of_warranty').'</span>';
        }
    }

    $row[] = $warranty_status;

    $status = '';
    $checked = '';
    if ($aRow['device_status'] == 1) {
        $checked = 'checked';
    }

    $status .= '<div class="onoffswitch">
    <input type="checkbox" ' . (((is_admin() || !has_permission('workshop_device', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_device_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['device_status'] . '" ' . $checked . '>
    <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
    </div>';

    $row[] = $status;

    $options = '';

    if((has_permission('workshop_device', '', 'view') || has_permission('workshop_device', '', 'view_own') || is_admin())){

        $options .= icon_btn(admin_url('workshop/device_detail/'.$aRow['id']), 'fa-solid fa-eye', 'btn-default', [
            
        ]);
    }

    if((has_permission('workshop_device', '', 'edit') || is_admin())){

        $options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
            'onclick'    => 'device_modal(' . $aRow['id'] . '); return false;',
        ]);
    }

    if(( has_permission('workshop_device', '', 'delete') || is_admin())){
        $options .= icon_btn('#', 'fa fa-remove', 'btn-danger', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top',
            'onclick'    => 'delete_device(' . $aRow['id'] . '); return false;',
        ]);
    }

    $row[] = $options;

    $output['aaData'][] = $row;
}

