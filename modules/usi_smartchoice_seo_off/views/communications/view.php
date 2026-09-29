<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body">
<?php if(!$communication){ ?><div class="alert alert-warning">Communication not found.</div><?php } else { ?>
<div class="usi-toolbar"><h4><?php echo html_escape($communication['subject']); ?></h4><div class="usi-actions">
<a class="btn btn-success btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/mark_communication_ready/'.(int)$communication['id']); ?>"><i class="fa fa-check"></i> Ready To Send</a>
<a class="btn btn-primary btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/mark_communication_sent/'.(int)$communication['id']); ?>"><i class="fa fa-send"></i> Mark Sent</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/communication/'.(int)$communication['id']); ?>"><i class="fa fa-pencil"></i> Edit</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/communications'); ?>"><i class="fa fa-arrow-left"></i> Back</a>
</div></div>
<table class="table table-condensed usi-compact-table"><tbody>
<tr><th>Status</th><td><?php echo html_escape(ucwords(str_replace('_',' ',$communication['send_status']))); ?></td></tr>
<tr><th>Type</th><td><?php echo html_escape(ucwords(str_replace('_',' ',$communication['communication_type']))); ?></td></tr>
<tr><th>Language</th><td><?php echo html_escape($communication['language']); ?></td></tr>
<tr><th>AI Estimate ID</th><td><?php echo (int)$communication['ai_estimate_id']; ?></td></tr>
<tr><th>Customer Package ID</th><td><?php echo (int)$communication['customer_package_id']; ?></td></tr>
</tbody></table>
<div class="well"><pre style="white-space:pre-wrap;background:transparent;border:0;margin:0;font-family:inherit;"><?php echo html_escape($communication['message_body']); ?></pre></div>
<?php } ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
</body></html>
