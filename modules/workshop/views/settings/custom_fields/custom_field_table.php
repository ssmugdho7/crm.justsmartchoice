<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
    'type',
    'required',
    'options',
    'field_order',
    'active',
    '1',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wshop_customfields';

$where = [];
$join= [];
if($this->ci->input->post('fieldset_id')){
    $where[] = 'AND '.db_prefix().'wshop_customfields.fieldset_id = '.$this->ci->input->post('fieldset_id');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['fieldset_id']);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['id'];
    $row[] = $aRow['name'];
    $row[] = str_replace('_', ' ', $aRow['type']);
    $required = '';
    if ($aRow['required'] == 1) {
        $required = '<i class="fa fa-check text-success"></i>';
    } else {
        $required = '<i class="fa fa-times text-danger"></i>';
    }
    $row[] = $required;

    $option_list = '';
    if ($aRow['options'] != '' && $aRow['options'] != null) {
        $decode_option = json_decode($aRow['options']);
        if (is_array($decode_option)) {
            foreach ($decode_option as $option) {
                $option_list .= '<span class="label label-success mright5 mbot5">' . $option . '</span>';
            }
        }
    }
    $row[] = $option_list;

    $row[] = $aRow['field_order'];

    $status = '';
    $checked = '';
    if ($aRow['active'] == 1) {
        $checked = 'checked';
    }

    $status .= '<div class="onoffswitch">
    <input type="checkbox" ' . (((is_admin() || !has_permission('workshop_setting', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_custom_field_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['active'] . '" ' . $checked . '>
    <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
    </div>';

    $row[] = $status;

    $options = '';

    if((has_permission('workshop_setting', '', 'edit') || is_admin())){
        $options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
            'onclick'    => 'custom_field_modal(' . $aRow['id'] . '); return false;',            
        ]);
    }

    if(( has_permission('workshop_setting', '', 'delete') || is_admin())){
        $options .= icon_btn('workshop/delete_custom_field/' . $aRow['id']. '/' .$aRow['fieldset_id'], 'fa fa-remove', 'btn-danger _delete', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top']);
    }

    $row[] = $options;

    $output['aaData'][] = $row;
}

