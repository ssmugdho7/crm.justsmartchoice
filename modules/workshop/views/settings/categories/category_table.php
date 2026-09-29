<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'code',
	'name',
	'use_for',
	'status',
	'description',
	'1',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wshop_categories';

$where = [];
$join= [];

$user_for_filter = $this->ci->input->post('user_for_filter');

if($user_for_filter){
    $where[] = 'AND '.db_prefix().'wshop_categories.use_for = "'.$user_for_filter.'"';
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
	$row = [];
	$row[] = $aRow['id'];
	$row[] = $aRow['code'];
	$row[] = $aRow['name'];
	$row[] = _l('wshop_'.$aRow['use_for']);

	$status = '';
	$checked = '';
	if ($aRow['status'] == 1) {
		$checked = 'checked';
	}

	$status .= '<div class="onoffswitch">
	<input type="checkbox" ' . (((is_admin() || !has_permission('workshop_setting', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_category_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['status'] . '" ' . $checked . '>
	<label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
	</div>';

	$row[] = $status;
	$row[] = nl2br($aRow['description']);

	$options = '';

	if((has_permission('workshop_setting', '', 'edit') || is_admin())){
		$options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
			'onclick'    => 'edit_category(this,' . $aRow['id'] . '); return false;',
			'data-code'  => $aRow['code'],
			'data-name'  => $aRow['name'],
			'data-use_for'  => $aRow['use_for'],
			'data-description'  => $aRow['description'],
		]);
	}

	if(( has_permission('workshop_setting', '', 'delete') || is_admin())){
		$options .= icon_btn('workshop/delete_category/' . $aRow['id'], 'fa fa-remove', 'btn-danger _delete', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top']);
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

