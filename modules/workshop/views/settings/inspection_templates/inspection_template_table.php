<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'code',
	'name',
	'description',
	'status',
	'datecreated',
	'staffid',
	'1',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wshop_inspection_templates';

$where = [];
$join= [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
	$row = [];
	$row[] = $aRow['id'];
	$row[] = $aRow['code'];
	$row[] = $aRow['name'];
	$row[] = nl2br($aRow['description']);

	$status = '';
	$checked = '';
	if ($aRow['status'] == 1) {
		$checked = 'checked';
	}

	$status .= '<div class="onoffswitch">
	<input type="checkbox" ' . (((is_admin() || !has_permission('workshop_setting', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_inspection_template_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['status'] . '" ' . $checked . '>
	<label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
	</div>';

	$row[] = $status;
	$row[] = _dt($aRow['datecreated']);
	$row[] = get_staff_full_name($aRow['staffid']);

	$options = '';

	if((has_permission('workshop_setting', '', 'edit') || is_admin())){
		$options .= icon_btn(admin_url('workshop/inspection_template_detail/'.$aRow['id']), 'fa-regular fa-eye', 'btn-success mbot5', [
			
		]);

		$options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default mbot5', [
			'onclick'    => 'edit_inspection_template(this,' . $aRow['id'] . '); return false;',
			'data-code'  => $aRow['code'],
			'data-name'  => $aRow['name'],
			'data-description'  => $aRow['description'],
		]);
	}

	if((has_permission('workshop_setting', '', 'delete') || is_admin())){
		$options .= icon_btn('workshop/delete_inspection_template/' . $aRow['id'], 'fa fa-remove', 'btn-danger _delete mbot5', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top']);
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

