<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'name',
	'description',
	'value',
	'status',
	'1',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wshop_intervals';

$where = [];
$join= [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['type']);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
	$row = [];
	$row[] = $aRow['id'];
	$row[] = $aRow['name'];
	$row[] = nl2br($aRow['description']);
	$row[] = $aRow['value'].' '.$aRow['type'];

	$status = '';
	$checked = '';
	if ($aRow['status'] == 1) {
		$checked = 'checked';
	}

	$status .= '<div class="onoffswitch">
	<input type="checkbox" ' . (((is_admin() || !has_permission('workshop_setting', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_interval_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['status'] . '" ' . $checked . '>
	<label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
	</div>';

	$row[] = $status;

	$options = '';

	if((has_permission('workshop_setting', '', 'edit') || is_admin())){
		$options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
			'onclick'    => 'edit_interval(this,' . $aRow['id'] . '); return false;',
			'data-name'  => $aRow['name'],
			'data-value'  => $aRow['value'],
			'data-type'  => $aRow['type'],
			'data-description'  => $aRow['description'],
		]);
	}

	if((has_permission('workshop_setting', '', 'delete') || is_admin())){
		$options .= icon_btn('workshop/delete_interval/' . $aRow['id'], 'fa fa-remove', 'btn-danger _delete', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top']);
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

