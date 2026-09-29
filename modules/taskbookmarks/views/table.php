<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'tbltaskbookmarks.id',
    'tbltaskbookmarks.name',
    'creator',
    ];
$sIndexColumn = 'id';
$sTable       = 'tbltaskbookmarks';
$join = ['Left join tbllist_widget on tbllist_widget.rel_id = tbltaskbookmarks.id and tbllist_widget.rel_type = "taskbookmarks"'];
$where = ['where tbltaskbookmarks.creator = '.get_staff_user_id()];   
$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['tbltaskbookmarks.id','tbllist_widget.id as id','tbllist_widget.add_from', 'color','icon']);

$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'tbltaskbookmarks.name') {
            $_data = '<a href="#" onclick="edit_taskbookmarks(this,' . htmlspecialchars($aRow['tbltaskbookmarks.id']) . '); return false" data-name="' . htmlspecialchars($aRow['tbltaskbookmarks.name']) . '">' . htmlspecialchars($_data) . '</a>';
        }
        elseif($aColumns[$i] == 'creator'){
            $_data = '<a href="' . admin_url('staff/profile/' . htmlspecialchars($aRow['creator'])) . '">' . staff_profile_image(htmlspecialchars($aRow['creator']), [
                'staff-profile-image-small',
                ]) . '</a>';
            $_data .= ' <a href="' . admin_url('staff/member/' . htmlspecialchars($aRow['creator'])) . '">' . get_staff_full_name(htmlspecialchars($aRow['creator'])) . '</a>';
        }
        $row[] = $_data;
    }


    $icon = '<i class="fa ' . htmlspecialchars($aRow['icon']) . '" style="font-size: 30px; color: '.htmlspecialchars($aRow['color']).';"></i>';
    $row[] = $icon; 

    $options = icon_btn('taskbookmarks/view_taskbookmarks/' . htmlspecialchars($aRow['tbltaskbookmarks.id']), 'eye', 'btn-default', ["data-toggle"=>"tooltip", "title"=> _l('view_taskbookmarks')]);
    $options .= icon_btn('#', 'pencil-square-o', 'btn-default', [
        'onclick' => 'edit_taskbookmarks(this,' . htmlspecialchars($aRow['tbltaskbookmarks.id']) . '); return false', 'data-name' => $aRow['tbltaskbookmarks.name'], 'data-icon' => $aRow['icon'], 'data-color' => $aRow['color'], "data-toggle"=>"tooltip", "title"=>_l('edit_taskbookmarks')
        ]);
    $options .= icon_btn('taskbookmarks/delete_taskbookmarks/' . htmlspecialchars($aRow['tbltaskbookmarks.id']), 'remove', 'btn-danger _delete', ["data-toggle"=>"tooltip", "title"=>_l('delete_taskbookmarks')]);

    if(is_numeric($aRow['id']) && $aRow['add_from'] == get_staff_user_id()){
        $row[] = $options .= icon_btn('#', 'compress', 'btn-danger', ["data-toggle"=>"tooltip", "title"=>_l('added_to_dashboard'), "onclick" => "remove_dashboard(".htmlspecialchars($aRow['tbltaskbookmarks.id']).")"]);
    }else{
        $row[] = $options .= icon_btn('#', 'external-link', 'btn-success', ["data-toggle"=>"tooltip", "title"=>_l('add_dashboard'), "onclick" => "add_dashboard(".htmlspecialchars($aRow['tbltaskbookmarks.id']).")"]);
    }
    $output['aaData'][] = $row;
}
