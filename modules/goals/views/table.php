<?php
defined('BASEPATH') or exit('No direct script access allowed');
$aColumns=['subject','CONCAT(firstname," ",lastname)','priority','goal_status','achievement','start_date','end_date','goal_type'];
$sIndexColumn='id'; $sTable=db_prefix().'goals';
$join=['LEFT JOIN '.db_prefix().'staff ON '.db_prefix().'staff.staffid = '.db_prefix().'goals.staff_id'];
$where=[];
if (staff_cant('view','goals') && staff_can('view_own','goals')) { $where[]='AND '.db_prefix().'goals.staff_id='.get_staff_user_id(); }
$status=$this->ci->input->post('status'); if ($status) { $where[]='AND '.db_prefix().'goals.goal_status='.$this->ci->db->escape($status); }
$priority=$this->ci->input->post('priority'); if ($priority) { $where[]='AND '.db_prefix().'goals.priority='.$this->ci->db->escape($priority); }
$result=data_tables_init($aColumns,$sIndexColumn,$sTable,$join,$where,['id']);
$output=$result['output']; $rResult=$result['rResult'];
foreach($rResult as $aRow){
 $row=[];
 foreach($aColumns as $column){
  $_data=$aRow[$column];
  if($column==='subject'){
   $_data='<a href="'.admin_url('goals/goal/'.$aRow['id']).'" class="tw-font-medium">'.html_escape($_data).'</a><div class="row-options"><a href="'.admin_url('goals/goal/'.$aRow['id']).'">'._l('view').'</a>';
   if(staff_can('delete','goals')){$_data.=' | <a href="'.admin_url('goals/delete/'.$aRow['id']).'" class="text-danger _delete">'._l('delete').'</a>';}
   $_data.='</div>';
  } elseif($column==='start_date'||$column==='end_date'){$_data=html_escape(_d($_data));}
  elseif($column==='goal_type'){$_data=html_escape(format_goal_type($_data));}
  elseif($column==='priority'){$_data='<span class="label sc-goal-priority sc-priority-'.html_escape($_data).'">'._l('goals_priority_'.$_data).'</span>';}
  elseif($column==='goal_status'){$_data='<span class="label sc-goal-status sc-status-'.html_escape($_data).'">'._l('goals_status_'.$_data).'</span>';}
  $row[]=$_data;
 }
 $achievement=$this->ci->goals_model->calculate_goal_achievement($aRow['id']);
 $percent=$achievement ? (float)$achievement['percent'] : 0;
 $row[]='<div class="goal-progress" data-percent="'.($percent/100).'"><strong class="goal-percent">'.html_escape($percent).'%</strong></div>';
 $row['DT_RowClass']='has-row-options'; $output['aaData'][]=$row;
}
