<?php

defined('BASEPATH') or exit('No direct script access allowed');


$table_ldpfdata = db_prefix() . 'popups_subscribers';
$table_ldp = db_prefix() . 'popups_popups';



$aColumnsQuery = [
    $table_ldp.'.name as popup_name',
    $table_ldpfdata.'.data as data',
    $table_ldpfdata.'.url as url',
    $table_ldpfdata.'.created_at as created_at',
];

$aColumnsEach = [
    'popup_name',
    'data',
    'url',
    'created_at',
];
$where        = [];

// Add blank where all filter can be stored
$filter = [];

$popups         = $this->ci->popup_model->get_all_popups();
$popupsIds = [];
foreach ($popups as $item) {
    if ($this->ci->input->post('popup_id_' . $item['id'])) {
        array_push($popupsIds, $item['id']);
    }
}

if (count($popupsIds) > 0) {
    array_push($filter, 'AND popup_id IN (' . implode(',', $popupsIds) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

$sIndexColumn = 'id';

$sTable       = $table_ldpfdata;

$join = ['LEFT JOIN ' . $table_ldp . ' ON ' . $table_ldp . '.id = ' . $table_ldpfdata . '.popup_id'];

$result = data_tables_init($aColumnsQuery, $sIndexColumn, $sTable, $join, $where, [$table_ldpfdata.'.id as id', $table_ldp.'.code as code']);

$output  = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumnsEach); $i++) {
        $_data = $aRow[$aColumnsEach[$i]];
        if ($aColumnsEach[$i] == 'popup_name') {
            $_data = '<a href="' . admin_url('perfex_popup/popups/setting/'. $aRow['code']) . '">' . $aRow['popup_name'] . '</a>';
            
            $_data .= '<div class="row-options">';
            if (has_permission('popups-subscribers', '', 'delete')) {
                $_data .= '<a href="' . admin_url('perfex_popup/popups/delete_subscriber/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        }
        else if ($aColumnsEach[$i] == 'data') {
            $data = json_decode($aRow['data']);
            $_data = '';
            foreach ($data as $key => $value) {
                $_data .= '<span class="text-small">'.$key.': '.$value.'</span></br>';
            }
        }

        $row[] = $_data;
    }

    $_data = '<a href="#" class="btn btn-default btn-convert-data-to-lead mright5" data-id='.$aRow['id'].'>
            '._l('convert_to_lead').'</a>';
    $_data .= '<a href="#" class="btn btn-success btn-convert-data-to-customer" data-id='.$aRow['id'].'>
            '._l('convert_to_customer').'</a>';

    $row[] = $_data;
    $output['aaData'][] = $row;
}