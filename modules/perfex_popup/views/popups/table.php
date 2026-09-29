<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'name',
    'is_enabled',
    'created_at',
    'updated_at',
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'popups_popups';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id','code']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {

    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'name') {
            $_data = '<a href="' . admin_url('perfex_popup/popups/builder/'. $aRow['code']) . '">' . $_data . '</a>';
            $_data .= '<div class="row-options">';
            if (has_permission('popups', '', 'delete')) {
                $_data .= '<a href="' . admin_url('perfex_popup/popups/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        }
        else if ($aColumns[$i] == 'is_enabled') {
        	if ($aRow['is_enabled']) {
        		$_data = '<span class="badge bg-success">'._l('enabled').'</span>';
        	}
        	else{
        		$_data = '<span class="badge bg-warning">'._l('disable').'</span>';
        	}
            $_data .= '</div>';
        }
        elseif($aColumns[$i] == 'created_at' || $aColumns[$i] == 'updated_at'){
            $_data = _dt($_data);
        }

        $row[] = $_data;
    }
    
    $_data = '<a href="'.admin_url('perfex_popup/popups/builder/'. $aRow['code']).'" class="btn btn-success mright5"><i class="fa fa-magic"></i> '._l('builder').'</a>';
    $_data .= '<a href="'.admin_url('perfex_popup/popups/setting/'. $aRow['code']).'" class="btn btn-default mright5"><i class="fa fa-cog"></i> '._l('settings').'</a>';
    $_data .= '<a href="javascript:void(0)" data-code="'.$aRow['code'].'" class="btn_install_popup btn btn-default mright5"><i class="fa fa-anchor"></i> '._l('install').'</a>';

    $row[] = $_data;
    $output['aaData'][] = $row;
}

