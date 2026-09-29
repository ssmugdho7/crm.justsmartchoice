<?php

defined('BASEPATH') or exit('No direct script access allowed');

$baseCurrency = get_base_currency();
$aColumns = [
    db_prefix() . 'wshop_labour_products.id as id',
    db_prefix() . 'wshop_labour_products.code as labour_product_code',
    db_prefix() . 'wshop_labour_products.name as labour_product_name',
    db_prefix().'wshop_categories.name as category_name',
    db_prefix().'wshop_labour_products.standard_time as standard_time',
    db_prefix().'wshop_labour_products.labour_cost as labour_cost',
    't1.taxrate as taxrate_1',
    't2.taxrate as taxrate_2',
    'assign_staff', //warranty_status
    db_prefix() . 'wshop_labour_products.status as labour_product_status',
    '1',
    '2',
    '3',
    '4',
];
$sIndexColumn = db_prefix() . 'wshop_labour_products.id';
$sTable = db_prefix() . 'wshop_labour_products';

$where = [];
$join= [];
$join         = [
    'LEFT JOIN ' . db_prefix() . 'taxes t1 ON t1.id = ' . db_prefix() . 'wshop_labour_products.tax',
    'LEFT JOIN ' . db_prefix() . 'taxes t2 ON t2.id = ' . db_prefix() . 'wshop_labour_products.tax2',
    'LEFT JOIN '.db_prefix().'wshop_categories ON '.db_prefix().'wshop_categories.id = '.db_prefix().'wshop_labour_products.category_id',
];

$category_filter = $this->ci->input->post('category_filter');
$status_filter = $this->ci->input->post('status_filter');
$assign_staff_filter = $this->ci->input->post('assign_staff_filter');
$appointment_type_id = $this->ci->input->post('appointment_type_id');
$inspection_form_detail_id = $this->ci->input->post('inspection_form_detail_id');

if($category_filter){
    $where[] = 'AND '.db_prefix().'wshop_labour_products.category_id ='.$category_filter;
}

if($status_filter){
    if($status_filter == -1){
        $status_filter = 0;
    }
    $where[] = 'AND '.db_prefix().'wshop_labour_products.status ='.$status_filter;
}
if($assign_staff_filter){
    $where[] = 'AND '.db_prefix().'wshop_labour_products.assign_staff ='.$assign_staff_filter;
}

if($appointment_type_id){
    $where[] = 'AND '.db_prefix().'wshop_labour_products.id IN (SELECT '.db_prefix().'wshop_appointment_products.item_id FROM '.db_prefix().'wshop_appointment_products WHERE '.db_prefix().'wshop_appointment_products.appointment_type_id = '.
    $appointment_type_id.' )';
}else{
    if(!is_admin() && !has_permission('workshop_labour_product','','view')){
      //View own
        $where[] = 'AND '.db_prefix().'wshop_labour_products.staffid = '.get_staff_user_id() ;
    }
}


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['labour_type','t1.name as taxname_1',
    't2.name as taxname_2',
    't1.id as tax_id_1',
    't2.id as tax_id_2',]);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['id'];

    $row[] = $aRow['labour_product_code'];
    $row[] = $aRow['labour_product_name'];
    $row[] = $aRow['category_name'];
    $row[] = $aRow['standard_time'];

    if($aRow['labour_type'] == 'fixed'){
        $row[] = app_format_money($aRow['labour_cost'], $baseCurrency).' ('. _l('wshop_fixed_price').')';
    }else{
        $row[] = app_format_money($aRow['labour_cost'], $baseCurrency). ' ('. _l('wshop_hours').')';
    }

    $aRow['taxrate_1'] = $aRow['taxrate_1'] ?? 0;
    $row[]             = '<span data-toggle="tooltip" title="' . e($aRow['taxname_1']) . '" data-taxid="' . $aRow['tax_id_1'] . '">' . e(app_format_number($aRow['taxrate_1'])) . '%' . '</span>';

    $aRow['taxrate_2'] = $aRow['taxrate_2'] ?? 0;
    $row[]             = '<span data-toggle="tooltip" title="' . e($aRow['taxname_2']) . '" data-taxid="' . $aRow['tax_id_2'] . '">' . e(app_format_number($aRow['taxrate_2'])) . '%' . '</span>';

    if($aRow['assign_staff']){
        $row[] = get_staff_full_name($aRow['assign_staff']);
    }else{
        $row[] = '';
    }


    $status = '';
    $checked = '';
    if ($aRow['labour_product_status'] == 1) {
        $checked = 'checked';
    }

    $status .= '<div class="onoffswitch">
    <input type="checkbox" ' . (((is_admin() || !has_permission('workshop_labour_product', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_labour_product_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['labour_product_status'] . '" ' . $checked . '>
    <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
    </div>';

    $row[] = $status;

    $options = '';

    if((has_permission('workshop_labour_product', '', 'view') || has_permission('workshop_labour_product', '', 'view_own') || is_admin())){

        $options .= icon_btn(admin_url('workshop/labour_product_detail/'.$aRow['id']), 'fa-solid fa-eye', 'btn-default', [
            
        ]);
    }

    if((has_permission('workshop_labour_product', '', 'edit') || is_admin())){

        $options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
            'onclick'    => 'labour_product_modal(' . $aRow['id'] . '); return false;',
        ]);
    }

    if((has_permission('workshop_labour_product', '', 'delete') || is_admin())){
        $options .= icon_btn('#', 'fa fa-remove', 'btn-danger', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top',
            'onclick'    => 'delete_labour_product(' . $aRow['id'] . '); return false;',
        ]);
    }

    $row[] = $options;

    $row[] = '<input name="standard_time" tabindex="-1" type="number" value="'.$aRow['standard_time'].'"
    id="task-single-standard_time" data-id="'.$aRow['id'].'"
    class="task-info-inline-input-edit pointer purchase_order-table-single-inline-field tw-text-neutral-800">';

    $options = '';
    $options .= icon_btn('#', 'fa fa-check', 'btn-primary', ['data-original-title' => _l('add'), 'data-toggle' => 'tooltip', 'data-placement' => 'top',
            'onclick'    => 'add_labour_product_to_table(this, ' . $aRow['id'] . '); return false;',
        ]);
    $row[] = $options;

    $options = '';
    $options .= icon_btn('#', 'fa fa-check', 'btn-primary', ['data-original-title' => _l('add'), 'data-toggle' => 'tooltip', 'data-placement' => 'top',
            'onclick'    => 'add_labour_product_to_table(this, ' . $aRow['id'] . ', ' . $inspection_form_detail_id . '); return false;',
        ]);
    $row[] = $options;


    $output['aaData'][] = $row;
}

