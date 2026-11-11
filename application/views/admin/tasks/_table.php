<?php

defined('BASEPATH') or exit('No direct script access allowed');

$table_data = [
    ['name' => _l('the_number_sign'), 'th_attrs' => ['class' => 'sc-task-col-number']],
    ['name' => _l('task'), 'th_attrs' => ['class' => 'sc-task-col-name']],
    ['name' => _l('task_status'), 'th_attrs' => ['class' => 'sc-task-col-status']],
    ['name' => _l('tasks_dt_datestart'), 'th_attrs' => ['class' => 'sc-task-col-date']],
    [
        'name'     => _l('task_duedate'),
        'th_attrs' => ['class' => 'duedate sc-task-col-date'],
    ],
    ['name' => _l('task_assigned'), 'th_attrs' => ['class' => 'sc-task-col-assignees']],
    ['name' => _l('tags'), 'th_attrs' => ['class' => 'sc-task-col-tags']],
    ['name' => _l('tasks_list_priority'), 'th_attrs' => ['class' => 'sc-task-col-priority']],
];

array_unshift($table_data, [
    'name'     => '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="tasks"><label></label></div>',
    'th_attrs' => ['class' => (isset($bulk_actions) ? 'sc-task-col-select' : 'not_visible sc-task-col-select')],
]);

$custom_fields = get_custom_fields('tasks', [
    'show_on_table' => 1,
]);

foreach ($custom_fields as $field) {
    array_push($table_data, [
        'name'     => $field['name'],
        'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
    ]);
}

$table_data = hooks()->apply_filters('tasks_table_columns', $table_data);

render_datatable($table_data, 'tasks', ['number-index-' . isset($bulk_actions) ? 2 : 1], [
    'data-last-order-identifier' => 'tasks',
    'data-default-order'         => get_table_last_order('tasks'),
    'id'                         => $table_id ?? 'tasks',
]);
