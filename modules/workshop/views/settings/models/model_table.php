<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'name',
	'model_no',
	'category_id',
	'description',
	'status',
	'1',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wshop_models';

$where = [];
$join= [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['category_id', 'fieldset_id', 'manufacturer_id']);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
	$row = [];
	$row[] = $aRow['id'];
	$row[] = $aRow['name'];
	$row[] = $aRow['model_no'];
	$row[] = wshop_get_category_name($aRow['category_id']);
	$row[] = nl2br($aRow['description']);

	$status = '';
	$checked = '';
	if ($aRow['status'] == 1) {
		$checked = 'checked';
	}

	$status .= '<div class="onoffswitch">
	<input type="checkbox" ' . (((is_admin() || !has_permission('workshop_setting', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_model_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['status'] . '" ' . $checked . '>
	<label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
	</div>';

	$row[] = $status;

	$options = '';

	if((has_permission('workshop_setting', '', 'edit') || is_admin())){
		$options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
			'onclick'    => 'edit_model(this,' . $aRow['id'] . '); return false;',
			'data-name'  => $aRow['name'],
			'data-manufacturer_id'  => $aRow['manufacturer_id'],
			'data-category_id'  => $aRow['category_id'],
			'data-model_no'  => $aRow['model_no'],
			'data-fieldset_id'  => $aRow['fieldset_id'],
			'data-description'  => $aRow['description'],
	
		]);
	}

	if(( has_permission('workshop_setting', '', 'delete') || is_admin())){
		$options .= icon_btn('workshop/delete_model/' . $aRow['id'], 'fa fa-remove', 'btn-danger _delete', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top']);
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

