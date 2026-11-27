<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'feedbackid as selection_id',
    'feedbackid',
    'subject',
    '(SELECT count(questionid) FROM ' . db_prefix() . 'form_questions WHERE ' . db_prefix() . 'form_questions.rel_id = ' . db_prefix() . 'feedbacks.feedbackid AND rel_type="feedback")',
    '(SELECT count(resultsetid) FROM ' . db_prefix() . 'feedbackresultsets WHERE ' . db_prefix() . 'feedbackresultsets.feedbackid = ' . db_prefix() . 'feedbacks.feedbackid)',
    'datecreated',
    'created_by',
    'active',
    'feedbackid as action_id',
];
$sIndexColumn = 'feedbackid';
$sTable       = db_prefix() . 'feedbacks';
$where = [];
if (!has_permission('feedbacks', '', 'view') && has_permission('feedbacks', '', 'view_own')) { $where[] = 'AND ' . db_prefix() . 'feedbacks.created_by = ' . (int)get_staff_user_id(); }
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, [
    'hash',
]);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    $row[] = has_permission('feedbacks', '', 'delete') ? '<input type="checkbox" class="feedback-row-select" data-feedback-id="' . (int)$aRow['feedbackid'] . '">' : ''; 
    for ($i = 1; $i < count($aColumns)-1; $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'subject') {
            $_data = '<a href="' . admin_url('feedbacks/feedback/' . $aRow['feedbackid']) . '">' . $_data . '</a>';

            $_data .= '<div class="row-options">';

            $_data .= '<a href="' . site_url('feedbacks/participate/index/' . $aRow['feedbackid'] . '/' . $aRow['hash']) . '" >' . _l('feedback_list_view_tooltip') . '</a>';

            if (total_rows(db_prefix() . 'feedbackresultsets', 'feedbackid=' . $aRow['feedbackid']) > 0) {
                $_data .= ' | <a href="' . admin_url('feedbacks/results/' . $aRow['feedbackid']) . '">' . _l('feedback_list_view_results_tooltip') . '</a>';
            }

            $_data .= ' | <a href="' . admin_url('feedbacks/feedback/' . $aRow['feedbackid']) . '">' . _l('edit') . '</a>';

            if (has_permission('feedbacks', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('feedbacks/delete/' . $aRow['feedbackid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }

            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'datecreated') {
            $_data = _dt($_data);
        } elseif ($aColumns[$i] == 'created_by') {
            $_data = !empty($_data) ? staff_profile_image((int)$_data, ['staff-profile-image-small']) . ' ' . html_escape(get_staff_full_name((int)$_data)) : _l('fb_unknown_creator');
        } elseif ($aColumns[$i] == 'active') {
            $checked = '';
            if ($aRow['active'] == 1) {
                $checked = 'checked';
            }

            $_data = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . admin_url() . 'feedbacks/change_feedback_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['feedbackid'] . '" data-id="' . $aRow['feedbackid'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow['feedbackid'] . '"></label>
            </div>';

            // For exporting
            $_data .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
        }
        $row[] = $_data;
    }
    $actions = '<a class="btn btn-default btn-xs" href="' . admin_url('feedbacks/feedback/' . $aRow['feedbackid']) . '"><i class="fa fa-eye"></i> ' . _l('view') . '</a>';
    if (has_permission('feedbacks', '', 'delete')) { $actions .= ' <a class="btn btn-danger btn-xs _delete" href="' . admin_url('feedbacks/delete/' . $aRow['feedbackid']) . '"><i class="fa fa-trash"></i> ' . _l('delete') . '</a>'; }
    $row[] = $actions;
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
