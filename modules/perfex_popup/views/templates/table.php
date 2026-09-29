<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'thumbnail',
    'name',
    'active',
    'created_at',
    'updated_at',
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'popups_templates';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id','code']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {

    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'thumbnail') {
            $_data = '<a href="' . admin_url('perfex_popup/templates/template/'. $aRow['id']) . '"><img src="'.base_url(PERFEX_POPUP_UPLOAD_PATH.'/popup_thumb_templates')."/".$_data.'" class="fluid" width="120"></a>';
            $_data .= '<div class="row-options">';
            if (has_permission('popups-templates', '', 'delete')) {
                $_data .= '<a href="' . admin_url('perfex_popup/templates/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        }
        else if ($aColumns[$i] == 'active') {
            if ($aRow['active']) {
                $_data = '<span class="badge bg-success">'._l('active').'</span>';
            }
            else{
                $_data = '<span class="badge bg-warning">'._l('not_active').'</span>';
            }
            $_data .= '</div>';
        }
        elseif($aColumns[$i] == 'created_at' || $aColumns[$i] == 'updated_at'){
            $_data = _dt($_data);
        }

        $row[] = $_data;
    }
    if (has_permission('popups-templates', '', 'edit')) {
        $_data = '<a href="'.admin_url('perfex_popup/popups/builder/'. $aRow['code'])."/main-content/template".'" class="btn btn-success mright5"><i class="fa fa-magic"></i> '._l('builder').'</a>';
    }
    $_data .= '<a href="'.admin_url('perfex_popup/templates/template/'. $aRow['id']).'" class="btn btn-default"><i class="fa fa-cog"></i> '._l('settings').'</a>';

    $row[] = $_data;
    $output['aaData'][] = $row;
}

