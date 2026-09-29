<?php

defined('BASEPATH') or exit('No direct script access allowed');
$this->ci->load->model('departments_model');
$has_permission_delete = has_permission('workshop_mechanic', '', 'delete');
$has_permission_edit   = has_permission('workshop_mechanic', '', 'edit');
$has_permission_create = has_permission('workshop_mechanic', '', 'create');

$custom_fields = get_custom_fields('staff', [
	'show_on_table' => 1,
]);
$aColumns = [
	'nation',
	'firstname',
	'email',
	'team_manage',
	db_prefix().'roles.name',
	'active',
];
$sIndexColumn = 'staffid';
$sTable       = db_prefix().'staff';
$join         = [
	'LEFT JOIN '.db_prefix().'roles ON '.db_prefix().'roles.roleid = '.db_prefix().'staff.role',
];

$i            = 0;
foreach ($custom_fields as $field) {
	$select_as = 'cvalue_' . $i;
	if ($field['type'] == 'date_picker' || $field['type'] == 'date_picker_time') {
		$select_as = 'date_picker_cvalue_' . $i;
	}
	array_push($aColumns, 'ctable_' . $i . '.value as ' . $select_as);
	array_push($join, 'LEFT JOIN '.db_prefix().'customfieldsvalues as ctable_' . $i . ' ON '.db_prefix().'staff.staffid = ctable_' . $i . '.relid AND ctable_' . $i . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $i . '.fieldid=' . $field['id']);
	$i++;
}
if (count($custom_fields) > 4) {
	@$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$where = hooks()->apply_filters('staff_table_sql_where', []);
$where = array();

$department_id = $this->ci->input->post('deparment');
if(isset($department_id) && new_strlen($department_id) > 0){

	$departmentgroup = $this->ci->workshop_model->get_staff_in_deparment($department_id);
	if (count($departmentgroup) > 0) {

		$where[] = 'AND '.db_prefix().'staff.staffid IN (SELECT staffid FROM '.db_prefix().'staff_departments WHERE departmentid IN (' . implode(', ', $departmentgroup) . '))';
	}

}

if($this->ci->input->post('role')){
	$where_role = '';
	$roles = $this->ci->input->post('role');
	foreach ($roles as $role) {
		if($role != '')
		{
			if($where_role == ''){
				$where_role .= ' ('.db_prefix().'staff.role in ("'.$role.'")';
			}else{
				$where_role .= ' or '.db_prefix().'staff.role in ("'.$role.'")';
			}
		}
	}
	if($where_role != '')
	{
		$where_role .= ')';
		if($where != ''){
			array_push($where, 'AND'. $where_role);
		}else{
			array_push($where, $where_role);
		}
		
	}
}          


$manages = $this->ci->input->post('staff_teammanage');
if(isset($manages) && new_strlen($manages) > 0){

	$where[] = '  AND staffid IN (select 
	staffid 
	from    (select * from '.db_prefix().'staff as s
	order by s.team_manage, s.staffid) departments_sorted,
	(select @pv := '.$manages.') initialisation
	where   find_in_set(team_manage, @pv)
	and     length(@pv := concat(@pv, ",", staffid)) OR staffid ='.$manages.')';
}

$mechanic_role_id = $this->ci->workshop_model->mechanic_role_exists();
$where[] = 'AND '.db_prefix().'staff.role = '.$mechanic_role_id;

//load deparment by manager
if(!is_admin() && !has_permission('workshop_mechanic','','view')){
	$where[] = 'AND 1=2';

}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
	'firstname',
	'email',
	'profile_image',
	'lastname',
	db_prefix().'staff.staffid',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
	$row = [];
	for ($i = 0; $i < count($aColumns); $i++) {
		if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
			$_data = $aRow[strafter($aColumns[$i], 'as ')];
		} else {
			$_data = $aRow[$aColumns[$i]];
		}
		
		if ($aColumns[$i] == 'active') {
			$checked = '';
			if ($aRow['active'] == 1) {
				$checked = 'checked';
			}
			$_data = '<div class="onoffswitch">
			<input type="checkbox" ' . (($aRow['staffid'] == get_staff_user_id() || (is_admin($aRow['staffid']) || !has_permission('workshop_mechanic', '', 'edit')) && !is_admin()) ? 'disabled' : '') . ' data-switch-url="' . admin_url() . 'workshop/change_staff_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['staffid'] . '" data-id="' . $aRow['staffid'] . '" ' . $checked . '>
			<label class="onoffswitch-label" for="c_' . $aRow['staffid'] . '"></label>
			</div>';

			$_data .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
		} elseif ($aColumns[$i] == 'firstname') {
			$_data = '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . staff_profile_image($aRow['staffid'], [
				'staff-profile-image-small',
			]) . '</a>';
			$_data .= ' <a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . $aRow['firstname'] . ' ' . $aRow['lastname'] . '</a>';
			
			$_data .= '<div class="row-options">';

			if (has_permission('workshop_mechanic', '', 'view') || has_permission('workshop_mechanic', '', 'view_own') || ($aRow['staffid'] == get_staff_user_id()) ) {
				$_data .= '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . _l('view') . '</a>';
			}

			if (has_permission('workshop_mechanic', '', 'edit') || ($aRow['staffid'] == get_staff_user_id() && get_option('hide_general_staff_information') == 0) || is_admin()) {
				$_data .= ' | <a href="#" onclick="update_mechanic(' . $aRow['staffid'] . ');return false;" >' . _l('edit') . '</a>';
			}

			if (has_permission('workshop_mechanic', '', 'delete') || is_admin()) {
				if ($has_permission_delete && $output['iTotalRecords'] > 1 && $aRow['staffid'] != get_staff_user_id()) {
					$_data .= ' | <a href="#" onclick="delete_staff_member(' . $aRow['staffid'] . '); return false;" class="text-danger">' . _l('delete') . '</a>';
				}
			}

			$_data .= '</div>';
		} elseif ($aColumns[$i] == 'email') {
			$_data = '<a href="mailto:' . $_data . '">' . $_data . '</a>';
		} elseif ($aColumns[$i] == 'team_manage') {
			if($aRow['staffid'] != ''){
				$team = $this->ci->departments_model->get_staff_departments($aRow['staffid']);
				$str = '';
				$j = 0;
				foreach ($team as $value) {
					$j++;
					$str .= '<span class="label label-tag tag-id-1"><span class="tag">'.$value['name'].'</span><span class="hide">, </span></span>&nbsp';
					if($j%2 == 0){
						$str .= '<br><br/>';
					}
					
				}
				$_data = $str;
			}
			else{
				$_data = '';
			}
		}elseif($aColumns[$i] == 'nation'){
			$_data = '<div class="checkbox"><input type="checkbox" value="' . $aRow['staffid'] . '"><label></label></div>';
		}
		else {

			if (strpos($aColumns[$i] ?? '', 'date_picker_') !== false) {
				$_data = (strpos($_data ?? '', ' ') !== false ? _dt($_data) : _d($_data));
			}
		}
		$row[] = $_data;
	}

	$row['DT_RowClass'] = 'has-row-options';
	$output['aaData'][] = $row;
}
