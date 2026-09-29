<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'name',
	'email',
	'address',
	'phonenumber',
	'active',
	'1',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wshop_branches';

$where = [];
$join= [];

if(!is_admin() && !has_permission('workshop_branch','','view')){
      //View own
    $where[] = 'AND '.db_prefix().'wshop_branches.staffid = '.get_staff_user_id() ;
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
	$row = [];
	$row[] = $aRow['id'];

	$row[] = $aRow['name'];
	$row[] = ($aRow['email'] ? '<a href="mailto:' . e($aRow['email']) . '">' . e($aRow['email']) . '</a>' : '');
	$row[] = $aRow['address'];
	$row[] = ($aRow['phonenumber'] ? '<a href="tel:' . e($aRow['phonenumber']) . '">' . e($aRow['phonenumber']) . '</a>' : '');

	$status = '';
	$checked = '';
	if ($aRow['active'] == 1) {
		$checked = 'checked';
	}

	$status .= '<div class="onoffswitch">
	<input type="checkbox" ' . (((is_admin() || !has_permission('workshop_branch', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_branch_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['active'] . '" ' . $checked . '>
	<label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
	</div>';

	$row[] = $status;

	$options = '';

	if((has_permission('workshop_branch', '', 'edit') || is_admin())){
		$options .= icon_btn('#', 'fa fa-envelope', 'btn-default', [
			'onclick'    => 'send_mail_modal(' . $aRow['id'] . ', this); return false;',
			'data-id' => $aRow['id'],
			'data-name' => $aRow['name'],
			'data-email' => $aRow['email'],
		]);

		$options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
			'onclick'    => 'branch_modal(' . $aRow['id'] . '); return false;',
		]);
	}

	if(( has_permission('workshop_branch', '', 'delete') || is_admin())){
		$options .= icon_btn('workshop/delete_branch/' . $aRow['id'], 'fa fa-remove', 'btn-danger _delete', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top']);
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

