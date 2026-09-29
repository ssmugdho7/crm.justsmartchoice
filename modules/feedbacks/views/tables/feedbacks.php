<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'feedbackid',
    'subject',
    '(SELECT count(questionid) FROM ' . db_prefix() . 'form_questions WHERE ' . db_prefix() . 'form_questions.rel_id = ' . db_prefix() . 'feedbacks.feedbackid AND rel_type="feedback")',
    '(SELECT count(resultsetid) FROM ' . db_prefix() . 'feedbackresultsets WHERE ' . db_prefix() . 'feedbackresultsets.feedbackid = ' . db_prefix() . 'feedbacks.feedbackid)',
    'datecreated',
    'active',
];
$sIndexColumn = 'feedbackid';
$sTable       = db_prefix() . 'feedbacks';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], [
    'hash',
]);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'subject') {
            $_data = '<a href="' . admin_url('feedbacks/feedback/' . $aRow['feedbackid']) . '">' . $_data . '</a>';

            $_data .= '<div class="row-options">';

            $_data .= '<a href="' . site_url('feedbacks/feedback/' . $aRow['feedbackid'] . '/' . $aRow['hash']) . '" >' . _l('feedback_list_view_tooltip') . '</a>';

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
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
