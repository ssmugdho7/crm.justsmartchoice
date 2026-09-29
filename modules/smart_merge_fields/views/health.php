<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s smf-panel"><div class="panel-body">
<h4 class="smf-title">Merge Fields Health Check</h4>
<?php $this->load->view('smart_merge_fields/_nav'); ?>
<div class="table-responsive"><table class="table table-condensed smf-table"><thead><tr><th>Check</th><th>Status</th><th>Message</th></tr></thead><tbody><?php foreach($checks as $check){ ?><tr><td><?php echo html_escape($check['name']); ?></td><td><span class="label label-<?php echo $check['status']==='Passed'?'success':($check['status']==='Warning'?'warning':'danger'); ?>"><?php echo html_escape($check['status']); ?></span></td><td><?php echo html_escape($check['message']); ?></td></tr><?php } ?></tbody></table></div>
<h4 class="smf-section-title">Audit Log</h4>
<div class="table-responsive"><table class="table table-condensed smf-table"><thead><tr><th>Action</th><th>Message</th><th>Records</th><th>Date</th></tr></thead><tbody><?php foreach($logs as $log){ ?><tr><td><?php echo html_escape($log['action']); ?></td><td><?php echo html_escape($log['message']); ?></td><td><?php echo (int)$log['records_affected']; ?></td><td><?php echo html_escape($log['created_at']); ?></td></tr><?php } ?></tbody></table></div>
</div></div></div></div>
<?php init_tail(); ?>
