<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
	'id',
	'manufacture_image',
	'name',
	'url',
	'support_url',
	'phone',
	'email',
	'2',
	'status',
	'1',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'wshop_manufacturers';

$where = [];
$join= [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
	$row = [];
	$row[] = $aRow['id'];

	if ($aRow['manufacture_image'] != '' && file_exists(MANUFACTURER_IMAGES_FOLDER.$aRow['id'].'/'.$aRow['manufacture_image'])) {
		$row[] = '<img class="manufacturer-img" id="wizardPicturePreview" src="' . site_url('modules/workshop/uploads/manufacturers/'.$aRow['id'].'/'.$aRow['manufacture_image']) . '" alt="'.$aRow['manufacture_image'].'" >';
	}else{
		$row[] = '<img class="manufacturer-img" id="wizardPicturePreview" src="' . site_url('modules/workshop/assets/images/upload-image-icon.png') . '" >';
	}

	$row[] = $aRow['name'];
	$row[] = check_for_links($aRow['url']);
	$row[] = check_for_links($aRow['support_url']);
	$row[] = ($aRow['phone'] ? '<a href="tel:' . e($aRow['phone']) . '">' . e($aRow['phone']) . '</a>' : '');
	$row[] = ($aRow['email'] ? '<a href="mailto:' . e($aRow['email']) . '">' . e($aRow['email']) . '</a>' : '');
	$row[] = device_by_manufacturer($aRow['id']);

	$status = '';
	$checked = '';
	if ($aRow['status'] == 1) {
		$checked = 'checked';
	}

	$status .= '<div class="onoffswitch">
	<input type="checkbox" ' . (((is_admin() || !has_permission('workshop_setting', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_manufacturer_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" data-status="' . $aRow['status'] . '" ' . $checked . '>
	<label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
	</div>';

	$row[] = $status;

	$options = '';

	if((has_permission('workshop_setting', '', 'edit') || is_admin())){
		$options .= icon_btn('#', 'fa-regular fa-pen-to-square', 'btn-default', [
			'onclick'    => 'manufacturer_modal(' . $aRow['id'] . '); return false;',
		]);
	}

	if((has_permission('workshop_setting', '', 'delete') || is_admin())){
		$options .= icon_btn('workshop/delete_manufacturer/' . $aRow['id'], 'fa fa-remove', 'btn-danger _delete', ['data-original-title' => _l('delete'), 'data-toggle' => 'tooltip', 'data-placement' => 'top']);
	}

	$row[] = $options;

	$output['aaData'][] = $row;
}

