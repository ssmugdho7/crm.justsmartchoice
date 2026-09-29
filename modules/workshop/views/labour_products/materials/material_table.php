<?php

defined('BASEPATH') or exit('No direct script access allowed');

$baseCurrency = get_base_currency();
$aColumns = [
    db_prefix() . 'wshop_labour_product_materials.id as id',
    db_prefix().'items.description as description',
    'quantity',
    db_prefix().'items.unit as unit',
    '1',
];
$sIndexColumn = db_prefix() . 'wshop_labour_product_materials.id';
$sTable = db_prefix() . 'wshop_labour_product_materials';

$where = [];
$join= [];
$join         = [
    'LEFT JOIN '.db_prefix().'items ON '.db_prefix().'items.id = '.db_prefix().'wshop_labour_product_materials.item_id',
];

$labour_product_id = $this->ci->input->post('labour_product_id');

$where[] = 'AND '.db_prefix().'wshop_labour_product_materials.labour_product_id ='.$labour_product_id;

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['labour_product_id']);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['id'];

    $row[] = $aRow['description'];
    $row[] = $aRow['quantity'];
    $row[] = $aRow['unit'];

    $options = '';

    if((has_permission('workshop_labour_product', '', 'edit') || is_admin())){

        $options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
            'onclick'    => 'material_modal(' . $aRow['id'] . ', '.$aRow['labour_product_id'].'); return false;',
        ]);
    }

    if(has_permission('workshop_labour_product', '', 'delete') ){
        $options .= icon_btn('#', 'fa fa-remove', 'btn-danger', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top',
            'onclick'    => 'delete_material(' . $aRow['id'] . '); return false;',
        ]);
    }

    $row[] = $options;

    $output['aaData'][] = $row;
}

