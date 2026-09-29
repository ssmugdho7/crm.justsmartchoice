<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns=['id','name','form_theme','(SELECT COUNT(id) FROM '.db_prefix().'leads WHERE '.db_prefix().'leads.from_form_id = '.db_prefix().'web_to_lead.id)','dateadded'];
$sIndexColumn='id'; $sTable=db_prefix().'web_to_lead';
$where=[' AND is_mpwtl = 1'];
if (!is_admin() && !has_permission(MPWTL_MODULE_NAME,'','view')) {
    $where[]=' AND created_by = '.(int)get_staff_user_id();
}
$result=data_tables_init($aColumns,$sIndexColumn,$sTable,[],$where,['form_key','id','created_by']);
$output=$result['output']; $rResult=$result['rResult'];
foreach($rResult as $aRow){
    $row=[];
    foreach($aColumns as $col){
        $_data=$aRow[$col];
        if($col==='name'){
            $canEdit=is_admin()||has_permission(MPWTL_MODULE_NAME,'','edit');
            $_data=$canEdit?'<a href="'.admin_url(MPWTL_MODULE_NAME.'/leads/form/'.$aRow['id']).'">'.html_escape($_data).'</a>':html_escape($_data);
            $_data.='<div class="row-options"><a href="'.site_url(MPWTL_MODULE_NAME.'/form/'.$aRow['form_key']).'" target="_blank">'._l('view').'</a>';
            if($canEdit) $_data.=' | <a href="'.admin_url(MPWTL_MODULE_NAME.'/leads/form/'.$aRow['id']).'">'._l('edit').'</a>';
            if(is_admin()||has_permission(MPWTL_MODULE_NAME,'','delete')) $_data.=' | <a href="'.admin_url(MPWTL_MODULE_NAME.'/leads/delete_form/'.$aRow['id']).'" class="text-danger _delete">'._l('delete').'</a>';
            $_data.='</div>';
        } elseif($col==='form_theme') { $_data=_l('mpwtl_theme_'.($_data?:'elegant')); }
        elseif($col==='dateadded') { $_data='<span class="text-has-action is-date" data-toggle="tooltip" data-title="'._dt($_data).'">'.time_ago($_data).'</span>'; }
        $row[]=$_data;
    }
    $row['DT_RowClass']='has-row-options'; $output['aaData'][]=$row;
}
