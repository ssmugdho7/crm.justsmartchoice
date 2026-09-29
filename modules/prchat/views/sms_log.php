<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<h4 class="no-margin"><i class="fa fa-commenting"></i> Employee SMS Log</h4><hr class="hr-panel-heading" />
<div class="table-responsive"><table class="table dt-table"><thead><tr><th>Date</th><th>Employee</th><th>Phone</th><th>Message</th><th>Status</th><th>Provider ID</th><th>Error</th></tr></thead><tbody>
<?php foreach ($sms_rows as $row): ?><tr><td><?php echo _dt($row['created_at']); ?></td><td><?php echo html_escape($row['recipient_name']); ?></td><td><?php echo html_escape($row['to_number']); ?></td><td style="max-width:360px;white-space:normal"><?php echo nl2br(html_escape($row['message'])); ?></td><td><span class="label <?php echo $row['status']==='failed'?'label-danger':'label-success'; ?>"><?php echo html_escape($row['status']); ?></span></td><td><?php echo html_escape($row['provider_message_id']); ?></td><td style="max-width:280px;white-space:normal"><?php echo html_escape($row['error_message']); ?></td></tr><?php endforeach; ?>
</tbody></table></div></div></div></div></div></div></div>
<?php init_tail(); ?>
