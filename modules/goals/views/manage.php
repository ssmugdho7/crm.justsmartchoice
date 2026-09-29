<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-choice-goals">
<?php $this->load->view('goals/_navigation'); ?>
<div class="row sc-goal-summary">
<?php foreach (['total'=>'goals_total','active'=>'goals_active','overdue'=>'goals_overdue','completed'=>'goals_completed'] as $key=>$label) { ?>
<div class="col-md-3 col-sm-6"><div class="sc-goal-card"><span><?php echo _l($label); ?></span><strong><?php echo (int)$summary[$key]; ?></strong></div></div>
<?php } ?>
</div>
<div class="row"><div class="col-md-12">
<div class="sc-goal-toolbar">
<?php if (staff_can('create','goals')) { ?><a href="<?php echo admin_url('goals/goal'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> <?php echo _l('new_goal'); ?></a><?php } ?>
<a href="<?php echo admin_url('goals/health'); ?>" class="btn btn-default btn-sm"><i class="fa fa-heart-pulse"></i> <?php echo _l('goals_health_check'); ?></a>
<a href="<?php echo admin_url('goals/help'); ?>" class="btn btn-default btn-sm"><i class="fa fa-circle-question"></i> <?php echo _l('goals_help_guide'); ?></a>
<select id="goal-status-filter" class="selectpicker" data-width="150px"><option value=""><?php echo _l('goals_all_statuses'); ?></option><option value="active"><?php echo _l('goals_status_active'); ?></option><option value="paused"><?php echo _l('goals_status_paused'); ?></option><option value="completed"><?php echo _l('goals_status_completed'); ?></option><option value="cancelled"><?php echo _l('goals_status_cancelled'); ?></option></select>
<select id="goal-priority-filter" class="selectpicker" data-width="150px"><option value=""><?php echo _l('goals_all_priorities'); ?></option><option value="low"><?php echo _l('goals_priority_low'); ?></option><option value="medium"><?php echo _l('goals_priority_medium'); ?></option><option value="high"><?php echo _l('goals_priority_high'); ?></option><option value="critical"><?php echo _l('goals_priority_critical'); ?></option></select>
</div>
<div class="panel_s"><div class="panel-body panel-table-full sc-goals-table-wrap">
<?php render_datatable([_l('goal_subject'),_l('staff_member'),_l('goals_priority'),_l('goals_status'),_l('goal_achievement'),_l('goal_start_date'),_l('goal_end_date'),_l('goal_type'),_l('goal_progress')], 'goals'); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
<script>
$(function(){
 var table = initDataTable('.table-goals', window.location.href, [8], [8], {status:function(){return $('#goal-status-filter').val();},priority:function(){return $('#goal-priority-filter').val();}});
 $('#goal-status-filter,#goal-priority-filter').on('change',function(){table.ajax.reload();});
 $('.table-goals').on('draw.dt',function(){ $('.goal-progress').each(function(){var p=parseFloat($(this).data('percent')||0);$(this).circleProgress({value:p,size:42,animation:false,fill:{gradient:['#169179','#F97316']}});});});
});
</script></body></html>
