<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'code',
	'name',
	'1',
	'status',
	'estimated_hours',
	'description',
	'1',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wshop_appointment_types';

$where = [];
$join= [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['plate_renewal', 'warrant_of_fitness', 'next_service']);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
	$row = [];
	$row[] = $aRow['id'];
	$row[] = $aRow['code'];
	$row[] = $aRow['name'];

	$appointment_type_products = $this->ci->workshop_model->get_appointment_type_products($aRow['id']);
	$str = '';
	$j = 0;
	foreach ($appointment_type_products as $value) {
		$j++;
		$str .= '<span class="label label-tag tag-id-1"><span class="tag">'.get_labour_product_name($value['item_id']).'</span><span class="hide">, </span></span>&nbsp';
		if($j%2 == 0){
			$str .= '<br><br/>';
		}

	}

	$row[] = $str;

	$status = '';
	$checked = '';
	if ($aRow['status'] == 1) {
		$checked = 'checked';
	}

	$status .= '<div class="onoffswitch">
	<input type="checkbox" ' . (((is_admin() || !has_permission('workshop_setting', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_appointment_type_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['status'] . '" ' . $checked . '>
	<label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
	</div>';

	$row[] = $status;

	$row[] = $aRow['estimated_hours'];
	$row[] = nl2br($aRow['description']);

	$options = '';

	if((has_permission('workshop_setting', '', 'edit') || is_admin())){
		$options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
			'onclick'    => 'appointment_type_modal(' . $aRow['id'] . '); return false;',
		]);
	}

	if(( has_permission('workshop_setting', '', 'delete') || is_admin())){
		$options .= icon_btn('workshop/delete_appointment_type/' . $aRow['id'], 'fa fa-remove', 'btn-danger _delete', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top']);
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

