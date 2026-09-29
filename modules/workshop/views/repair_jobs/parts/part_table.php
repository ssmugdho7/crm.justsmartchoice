<?php

defined('BASEPATH') or exit('No direct script access allowed');

$baseCurrency = get_base_currency();
$aColumns = [
    db_prefix() . 'items.id as id',
    db_prefix() . 'items.description as description',
    db_prefix() . 'items.long_description as long_description',
    db_prefix().'items_groups.name as category_name',
    db_prefix() . 'items.rate as rate',
    't1.taxrate as taxrate_1',
    't2.taxrate as taxrate_2',
    '2',
    '3',
    '4',
];
$sIndexColumn = db_prefix() . 'items.id';
$sTable = db_prefix() . 'items';

$where = [];
$join= [];
$join         = [
    'LEFT JOIN ' . db_prefix() . 'taxes t1 ON t1.id = ' . db_prefix() . 'items.tax',
    'LEFT JOIN ' . db_prefix() . 'taxes t2 ON t2.id = ' . db_prefix() . 'items.tax2',
    'LEFT JOIN '.db_prefix().'items_groups ON '.db_prefix().'items_groups.id = '.db_prefix().'items.group_id',
];

$category_filter = $this->ci->input->post('category_filter');
$status_filter = $this->ci->input->post('status_filter');
$appointment_type_id = $this->ci->input->post('appointment_type_id');
$inspection_form_detail_id = $this->ci->input->post('inspection_form_detail_id');

if($category_filter){
    $where[] = 'AND '.db_prefix().'items.group_id ='.$category_filter;
}

if($status_filter){
    if($status_filter == -1){
        $status_filter = 0;
    }
    $where[] = 'AND '.db_prefix().'items.status ='.$status_filter;
}

if($appointment_type_id){
    $where[] = 'AND '.db_prefix().'items.id IN (SELECT '.db_prefix().'wshop_labour_product_materials.item_id FROM '.db_prefix().'wshop_labour_product_materials WHERE '.db_prefix().'wshop_labour_product_materials.labour_product_id IN (  (SELECT '.db_prefix().'wshop_appointment_products.item_id FROM '.db_prefix().'wshop_appointment_products WHERE '.db_prefix().'wshop_appointment_products.appointment_type_id = '.
    $appointment_type_id.' ) ) )';
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['t1.name as taxname_1',
    't2.name as taxname_2',
    't1.id as tax_id_1',
    't2.id as tax_id_2',]);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['id'];

    $row[] = $aRow['description'];
    $row[] = $aRow['long_description'];
    $row[] = $aRow['category_name'];
    $row[] = app_format_money($aRow['rate'], $baseCurrency);

    $aRow['taxrate_1'] = $aRow['taxrate_1'] ?? 0;
    $row[]             = '<span data-toggle="tooltip" title="' . e($aRow['taxname_1']) . '" data-taxid="' . $aRow['tax_id_1'] . '">' . e(app_format_number($aRow['taxrate_1'])) . '%' . '</span>';

    $aRow['taxrate_2'] = $aRow['taxrate_2'] ?? 0;
    $row[]             = '<span data-toggle="tooltip" title="' . e($aRow['taxname_2']) . '" data-taxid="' . $aRow['tax_id_2'] . '">' . e(app_format_number($aRow['taxrate_2'])) . '%' . '</span>';

    $row[] = '<input name="quantity" tabindex="-1" type="number" value="1"
    id="task-single-quantity" data-id="'.$aRow['id'].'"
    class="task-info-inline-input-edit pointer purchase_order-table-single-inline-field tw-text-neutral-800">';

    $options = '';
    $options .= icon_btn('#', 'fa fa-check', 'btn-primary', ['data-original-title' => _l('add'), 'data-toggle' => 'tooltip', 'data-placement' => 'top',
            'onclick'    => 'add_part_to_table(this, ' . $aRow['id'] . '); return false;',
        ]);
    $row[] = $options;
    
    $options = '';
    $options .= icon_btn('#', 'fa fa-check', 'btn-primary', ['data-original-title' => _l('add'), 'data-toggle' => 'tooltip', 'data-placement' => 'top',
        'onclick'    => 'add_part_to_table(this, ' . $aRow['id'] . ', ' . $inspection_form_detail_id . '); return false;',
    ]);
    $row[] = $options;


    $output['aaData'][] = $row;
}

