<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'name',
	'2',
	'3',
	'description',
	'status',
	'datecreated',
	'staffid',
	'1',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wshop_fieldsets';

$where = [];
$join= [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
	$qty_fields = $this->ci->workshop_model->count_custom_field_by_field_set($aRow['id']);
	$row = [];
	$row[] = $aRow['id'];
	$row[] = $aRow['name'];
	$row[] = $qty_fields;
	$row[] = cal_model_by_fieldset($aRow['id']);
	$row[] = nl2br($aRow['description']);

	$status = '';
	$checked = '';
	if ($aRow['status'] == 1) {
		$checked = 'checked';
	}

	$status .= '<div class="onoffswitch">
	<input type="checkbox" ' . (((is_admin() || !has_permission('workshop_setting', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_fieldset_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['status'] . '" ' . $checked . '>
	<label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
	</div>';

	$row[] = $status;
	$row[] = _dt($aRow['datecreated']);
	$row[] = get_staff_full_name($aRow['staffid']);

	$options = '';

	if((has_permission('workshop_setting', '', 'edit') || is_admin())){
		$options .= icon_btn(admin_url('workshop/fieldset_detail/'.$aRow['id']), 'fa-regular fa-eye', 'btn-success mbot5', [
			
		]);

		$options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default mbot5', [
			'onclick'    => 'edit_fieldset(this,' . $aRow['id'] . '); return false;',
			'data-name'  => $aRow['name'],
			'data-description'  => $aRow['description'],
		]);
	}

	if(( has_permission('workshop_setting', '', 'delete') || is_admin())){
		$options .= icon_btn('workshop/delete_fieldset/' . $aRow['id'], 'fa fa-remove', 'btn-danger _delete mbot5', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top']);
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

