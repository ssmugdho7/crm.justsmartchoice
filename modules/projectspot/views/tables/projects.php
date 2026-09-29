<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'project_name',
    'category_id',
    '(SELECT category_name FROM ' . db_prefix() . 'projectspot_categories WHERE ' . db_prefix() . 'projectspot_categories.id=' . db_prefix() . 'projectspot_gallery_list.category_id) as categoryName',
    'is_enabled',
    'created_at'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'projectspot_gallery_list';

$join = [];
$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id'
]);

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {

        $row[] = $aRow['project_name'];
        $row[] = $aRow['categoryName'];

        $checked = '';
        if ($aRow['is_enabled'] == 1) {
            $checked = 'checked';
        }
        $row[]= '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . admin_url() . 'projectspot/update_project_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
            </div>';

        $row[] = $aRow['created_at'];


        $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
        $options .= '<a href="' . admin_url('projectspot/create_project/' . $aRow['id']) . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
        <i class="fa-regular fa-pen-to-square fa-lg"></i>
    </a>';

        $options .= '<a href="' . admin_url('projectspot/delete_project/' . $aRow['id']) . '"
    class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
        <i class="fa-regular fa-trash-can fa-lg"></i>
    </a>';

        $options .= '</div>';

        $row[]              = $options;
    }

    $output['aaData'][] = $row;
}
