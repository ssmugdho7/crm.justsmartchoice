<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionCreate   = staff_can('create', 'project_templates');
$hasPermissionEdit   = staff_can('edit', 'project_templates');
$hasPermissionDelete = staff_can('delete', 'project_templates');

$aColumns = [
    '1', // bulk actions
    PROJECT_TEMPLATES_TABLE_NAME . '.id as id',
    PROJECT_TEMPLATES_TABLE_NAME . '.name as project_name',
    PROJECT_TEMPLATES_TABLE_NAME . '.description as project_description',
];

$sIndexColumn = 'id';
$sTable       = PROJECT_TEMPLATES_TABLE_NAME;

$where = [];
$join  = [];

if(!staff_can('view', 'project_templates')){
    $where[] = ' AND added_by='.get_staff_user_id();
}
$aColumns = hooks()->apply_filters('project_templates_table_sql_columns', $aColumns);

$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    $join,
    $where
);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

    $row[] = '<a href="' . admin_url('project_templates/view/' . $aRow['id']) . '">' . $aRow['id'] . '</a>';

    $outputName = '';

    $outputName .= '<a href="' . admin_url('project_templates/view/' . $aRow['id']) . '" class="display-block main-projects-table-href-name' . (!empty($aRow['rel_id']) ? ' mbot5' : '') . '">' . $aRow['project_name'] . '</a>';

    $outputName .= '<div class="row-options">';

    $class = 'text-success bold';
    $style = '';

    $actions = [];
    if ($hasPermissionCreate) {
        $actions[] = '<a href="' . admin_url('project_templates/copy/' . $aRow['id']) . '">' . _l('copy') . '</a>';
    }

    if ($hasPermissionEdit) {
        $actions[] = '<a href="' . admin_url('project_templates/template/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }

    if ($hasPermissionDelete) {
        $actions[] = '<a href="' . admin_url('project_templates/delete_project_template/' . $aRow['id']) . '" class="text-danger _delete project-delete">' . _l('delete') . '</a>';
    }
    $outputName .= implode('<span class="text-dark"> | </span>', $actions);
    $outputName .= '</div>';

    $row[] = $outputName;

    $row[] = $aRow['project_description'];

    $row['DT_RowClass'] = 'has-row-options';

    $row = hooks()->apply_filters('project_templates_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
