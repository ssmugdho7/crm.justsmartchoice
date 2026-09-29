<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'name',
    'start_date',
    'due_date',
    'description',
];

$sIndexColumn = 'id';
$sTable       = PROJECT_TEMPLATES_MILESTONE_TABLE_NAME;

$where = [
    'AND project_template_id=' . $this->ci->db->escape_str($project_template_id),
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, [
    'id',
    'milestone_order',
    'description',
    'description_visible_to_customer',
    'hide_from_customer',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $nameRow = $aRow['name'];

    if (staff_can('edit_milestones', 'project_templates')) {
        $nameRow = '<a href="#" onclick="edit_milestone_template(this,' . $aRow['id'] . '); return false" data-name="' . $aRow['name'] . '" data-start_date="' . _d($aRow['start_date']) . '" data-due_date="' . _d($aRow['due_date']) . '" data-order="' . $aRow['milestone_order'] . '" data-description="' . htmlspecialchars(clear_textarea_breaks($aRow['description'])) . '" data-description-visible-to-customer="' . $aRow['description_visible_to_customer'] . '" data-hide-from-customer="' . $aRow['hide_from_customer'] . '">' . $nameRow . '</a>';
    }

    if (staff_can('delete_milestones', 'project_templates')) {
        $nameRow .= '<div class="row-options">';
        $nameRow .= '<a href="' . admin_url('project_templates/delete_milestone/' . $project_template_id . '/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
        $nameRow .= '</div>';
    }

    $row[] = $nameRow;
    $row[] =  _d($aRow['start_date']);

    $dateRow = _d($aRow['due_date']);

    $row[] = $dateRow;

    $row[] = clear_textarea_breaks($aRow['description']);

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}
