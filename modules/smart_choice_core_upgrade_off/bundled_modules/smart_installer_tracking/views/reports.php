<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-installer-tracking"><div class="panel_s"><div class="panel-body">
<h3 class="sit-page-title"><?php echo html_escape($title); ?></h3>
<form method="get" class="row tw-mb-3"><div class="col-md-3"><label>From</label><input type="date" name="from" class="form-control" value="<?php echo html_escape($filters['from']); ?>"></div><div class="col-md-3"><label>To</label><input type="date" name="to" class="form-control" value="<?php echo html_escape($filters['to']); ?>"></div><div class="col-md-3"><label>Installer</label><select name="staff_id" class="form-control selectpicker" data-live-search="true"><option value="">All Installers</option><?php foreach ($staff as $member) { $name=trim(($member['firstname']??'').' '.($member['lastname']??'')); ?><option value="<?php echo (int)$member['staffid']; ?>" <?php echo (string)$filters['staff_id']===(string)$member['staffid']?'selected':''; ?>><?php echo html_escape($name); ?></option><?php } ?></select></div><div class="col-md-3"><label>&nbsp;</label><button class="btn btn-primary btn-block">Filter</button></div></form>
<div class="sit-chart" id="sit-report-chart">Performance comparison chart</div>
<div class="table-responsive"><table class="table table-striped sit-table"><thead><tr><th>Installer</th><th>Total Trips</th><th>Completed</th><th>Arrived</th><th>Cancelled</th><th>Completion Score</th><th>Average ETA</th></tr></thead><tbody>
<?php foreach ($reports as $row) { $total=(int)$row['total_trips']; $score=$total>0?round(((int)$row['completed_trips']/$total)*100):0; ?>
<tr><td><?php echo html_escape($row['staff_name'] ?? ''); ?></td><td><?php echo $total; ?></td><td><?php echo (int)$row['completed_trips']; ?></td><td><?php echo (int)$row['arrived_trips']; ?></td><td><?php echo (int)$row['cancelled_trips']; ?></td><td><div class="progress"><div class="progress-bar" style="width:<?php echo $score; ?>%;"><?php echo $score; ?>%</div></div></td><td><?php echo html_escape(round((float)$row['average_eta'],1)); ?> minutes</td></tr>
<?php } ?>
</tbody></table></div>
</div></div></div></div>
<?php init_tail(); ?>
