<?php
$CI          = & get_instance();
$customFieldsColumns = [];
$custom_fields = get_table_custom_fields('dmn');

$aColumns = [db_prefix().'dmn.id as select_id',db_prefix().'dmn.id as record_id',db_prefix().'dmn.title as title',db_prefix().'dmn.description as description',db_prefix().'dmn.staffid as staffid',db_prefix().'projects.name as project_name',db_prefix().'dmn.dateadded as dateadded',];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'dmn';
$join = ['LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'dmn.project_id',];
$where = dmn_own_scope() ? ['AND ('.db_prefix().'dmn.staffid = '.(int)get_staff_user_id().' OR '.db_prefix().'dmn.assigned_staff_id = '.(int)get_staff_user_id().')'] : [];
$filter = [];
foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN '.db_prefix().'customfieldsvalues as ctable_' . $key . ' ON '.db_prefix().'dmn.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}
$join = hooks()->apply_filters('dmn_table_sql_join', $join);
$staffIds = [];
if ($CI->input->post('assigned')) {
    $staffIds = array_filter(array_map('intval',(array)$CI->input->post('assigned')));
}
if (count($staffIds) > 0) {
    array_push($filter, 'AND ('.db_prefix().'dmn.staffid IN (' . implode(', ', $staffIds) . ') OR '.db_prefix().'dmn.assigned_staff_id IN ('.implode(', ', $staffIds).'))');
}
$projectIds = [];
if ($CI->input->post('project')) {
    $projectIds = array_filter(array_map('intval',(array)$CI->input->post('project')));
}
if (count($projectIds) > 0) {
    array_push($filter, 'AND '.db_prefix().'dmn.project_id IN (' . implode(', ', $projectIds) . ')');
}
if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}
if ($CI->input->post('my_dmn')) {
    array_push($where, 'AND '.db_prefix().'dmn.staffid = ' . get_staff_user_id());
}
$aColumns = hooks()->apply_filters('dmn_table_sql_columns', $aColumns);
if (count($custom_fields) > 4) {
    @$CI->db->query('SET SQL_BIG_SELECTS=1');
}
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'dmn.id',db_prefix().'dmn.assigned_staff_id as assigned_employee']);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $key = strpos($aColumns[$i], ' as ') !== false ? substr($aColumns[$i], strpos($aColumns[$i], ' as ')+4) : $aColumns[$i];
        $_data = html_escape($aRow[$key] ?? '');
        if ($i === 0) { $_data = has_permission('dmn','','delete') ? '<input type="checkbox" class="dmn-select" value="'.(int)$aRow['record_id'].'">' : ''; } elseif ($i === 1) { $_data = (int)$aRow['record_id']; } elseif ($key === 'title') {
            $hrefAttr = 'href="' . admin_url('dmn/index/' . $aRow['record_id']) . '" onclick="init_dmn_modal(' . $aRow['record_id'] . ');return false;"';
            $_data = '<a href="' . admin_url('dmn/preview/' . $aRow['record_id']) . '" >' . $_data . '</a>';
            $_data .= '<div class="row-options">';
            $_data .= ' <a href="' . admin_url('dmn/dmn_create/' . $aRow['record_id']) . '">' . _l('dmn_edit') . '</a>';
            if (has_permission('dmn', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('dmn/delete/' . $aRow['record_id']) . '" class="text-danger _delete">' . _l('dmn_delete') . '</a>';
            }
            $_data .= '</div>';
        } 
        elseif ($aColumns[$i] == db_prefix() . 'dmn.description' || $aColumns[$i] == 'description') {
            $_data = $_data;
        }
        elseif ($key === 'staffid') {
            $_data = (int)($aRow['assigned_employee'] ?: $_data);
            if($_data > 0) {
                $CI->load->model('staff_model');
                $oStaff = $CI->staff_model->get($_data);
                if (!$oStaff) { $row[] = ''; continue; }
                $_data = staff_profile_image($oStaff->staffid, array('img', 'img-responsive', 'staff-profile-image-small', 'pull-left')) . '<a href="' . admin_url('profile/' . $oStaff->staffid) . '">' . $oStaff->firstname . ' ' . $oStaff->lastname . '</a><br>';
            }else{
                $_data = '';
            }
        }
        elseif ($aColumns[$i] == db_prefix() . 'dmn.dateadded' || $key === 'dateadded') {
            $_data = _dt($_data);
        }
        else{
            $_data = $_data;
        }
        $row[] = $_data;
    }
    ob_start();
    ?>
    <?php
    $progress = ob_get_contents();
    ob_end_clean();
    
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}