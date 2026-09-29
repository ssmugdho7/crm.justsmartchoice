<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = ['engg_proj_id','name','project_id','customer_id','site_survey_schedule','drawings_ids','document_ids','shared_file_ids','tpo_status'];
$sIndexColumn = 'engg_proj_id';
$sTable = db_prefix() . 'eng_engineering_projects';
$where = [];
if (!has_permission('engineering_projects', '', 'view') && has_permission('engineering_projects', '', 'view_own')) { $where[] = 'AND created_by = ' . get_staff_user_id(); }
foreach (['site_survey_schedule','contractor_id','install_schedule','final_inspection','tpo_status'] as $filter) { if ($this->ci->input->post($filter)) { $where[] = 'AND '.$filter.' = '.(int)$this->ci->input->post($filter); } }
if($this->ci->input->post('drawings_id')){ $where[] = 'AND FIND_IN_SET('.(int)$this->ci->input->post('drawings_id').',drawings_ids) <> 0'; }
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['engg_proj_id','folder_path','datecreated']);
$output = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    $id = (int)$aRow['engg_proj_id'];
    $row[] = '<a href="'.admin_url('engineering_projects/engineering_project/'.$id).'">#'.$id.'</a><div class="row-options"><a href="'.admin_url('engineering_projects/engineering_project/'.$id).'">'._l('view').'</a>'.(has_permission('engineering_projects','','delete') ? ' | <a href="'.admin_url('engineering_projects/delete/'.$id).'" class="text-danger _delete">'._l('delete').'</a>' : '').'</div>';
    $row[] = html_escape($aRow['name'] ?: 'Engineering Project #'.$id);
    $row[] = $aRow['project_id'] ? '<a href="'.admin_url('projects/view/'.$aRow['project_id']).'">CRM Project #'.(int)$aRow['project_id'].'</a>' : '<span class="text-muted">—</span>';
    $row[] = $aRow['customer_id'] ? '<a href="'.admin_url('clients/client/'.$aRow['customer_id']).'">Customer #'.(int)$aRow['customer_id'].'</a>' : '<span class="text-muted">—</span>';
    $row[] = show_eng_status($aRow['site_survey_schedule']);
    $icons=''; foreach(array_filter(explode(',', (string)$aRow['drawings_ids'])) as $did){ $d=get_drawing($did); if($d){ $icons .= html_escape($d->name).'<br>'.drawing_file($d->draf, $d->folder_path ?? '').' '.drawing_file($d->final_doc, $d->folder_path ?? '').'<br>'; } } $row[] = $icons ?: '<span class="text-muted">No drawings</span>';
    $docs=''; foreach(array_filter(explode(',', (string)$aRow['document_ids'])) as $docid){ $doc=get_document($docid); if($doc){ $docs .= html_escape($doc->name).'<br>'.document_file($doc->noc, $doc->folder_path ?? '').' '.document_file($doc->eng_letter, $doc->folder_path ?? '').' '.document_file($doc->site_insp, $doc->folder_path ?? '').' '.document_file($doc->permit, $doc->folder_path ?? '').'<br>'; } } $row[] = $docs ?: '<span class="text-muted">No documents</span>';
    $row[] = $aRow['shared_file_ids'] ? count(array_filter(explode(',', $aRow['shared_file_ids']))).' shared files' : '<span class="text-muted">—</span>';
    $row[] = show_eng_tpo_status($aRow['tpo_status']);
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
