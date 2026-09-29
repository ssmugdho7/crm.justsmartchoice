<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-choice-goals">
<?php $this->load->view('goals/_navigation'); ?>
<div class="row sc-goal-summary">
<?php foreach (['total'=>'goals_total','active'=>'goals_active','completed'=>'goals_completed','overdue'=>'goals_overdue','due_week'=>'goals_due_this_week','completion_rate'=>'goals_completion_rate'] as $key=>$label) { ?>
<div class="col-lg-2 col-md-4 col-sm-6"><div class="sc-goal-card"><span><?php echo _l($label); ?></span><strong><?php echo html_escape($summary[$key]); ?><?php echo $key==='completion_rate'?'%':''; ?></strong></div></div>
<?php } ?>
</div>
<div class="row">
<div class="col-md-6"><div class="panel_s"><div class="panel-body"><h4><?php echo _l('goals_by_department'); ?></h4><canvas id="goals-department-chart" height="180"></canvas></div></div></div>
<div class="col-md-6"><div class="panel_s"><div class="panel-body"><h4><?php echo _l('goals_monthly_completion'); ?></h4><canvas id="goals-monthly-chart" height="180"></canvas></div></div></div>
</div>
<div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body"><h4><?php echo _l('goals_staff_performance'); ?></h4><div class="table-responsive"><table class="table table-striped"><thead><tr><th><?php echo _l('staff_member'); ?></th><th><?php echo _l('goals_total'); ?></th><th><?php echo _l('goals_completed'); ?></th><th><?php echo _l('goals_completion_rate'); ?></th></tr></thead><tbody><?php foreach($staff as $row){$rate=$row['total']?round(($row['completed']/$row['total'])*100):0;?><tr><td><?php echo html_escape(trim($row['firstname'].' '.$row['lastname'])); ?></td><td><?php echo (int)$row['total']; ?></td><td><?php echo (int)$row['completed']; ?></td><td><?php echo $rate; ?>%</td></tr><?php } ?></tbody></table></div></div></div></div></div>
</div></div>
<?php init_tail(); ?>
<script>
$(function(){
 new Chart(document.getElementById('goals-department-chart'),{type:'bar',data:{labels:<?php echo json_encode(array_column($by_department,'name')); ?>,datasets:[{label:<?php echo json_encode(_l('goals')); ?>,data:<?php echo json_encode(array_map('intval',array_column($by_department,'total'))); ?>}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}});
 new Chart(document.getElementById('goals-monthly-chart'),{type:'line',data:{labels:<?php echo json_encode(array_column($monthly,'month')); ?>,datasets:[{label:<?php echo json_encode(_l('goals_completed')); ?>,data:<?php echo json_encode(array_map('intval',array_column($monthly,'total'))); ?>,tension:.35,fill:true}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}});
});
</script></body></html>