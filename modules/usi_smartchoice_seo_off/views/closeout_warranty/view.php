<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body">
<?php if (empty($closeout)) { ?><div class="alert alert-warning">Closeout package not found.</div><?php } else { ?>
<div class="tw-flex tw-justify-between tw-items-center tw-mb-3">
<h4 class="no-margin"><i class="fa fa-shield"></i> <?php echo html_escape($closeout['title']); ?></h4>
<div>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/closeout_record/' . (int)$closeout['id']); ?>"><i class="fa fa-pencil"></i> Edit</a>
<a class="btn btn-info btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/generate_warranty_records/' . (int)$closeout['id']); ?>"><i class="fa fa-shield"></i> Generate Warranties</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/generate_closeout_followups/' . (int)$closeout['id']); ?>"><i class="fa fa-calendar-check-o"></i> Generate Follow Ups</a>
<a class="btn btn-success btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/mark_closeout_ready/' . (int)$closeout['id']); ?>"><i class="fa fa-check"></i> Ready For Customer</a>
<a class="btn btn-primary btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/mark_closeout_completed/' . (int)$closeout['id']); ?>"><i class="fa fa-flag-checkered"></i> Completed</a>
</div></div>
<div class="row"><div class="col-md-6">
<table class="table table-condensed sc-table"><tbody>
<tr><th>Status</th><td><?php echo ucwords(str_replace('_',' ', html_escape($closeout['status']))); ?></td></tr>
<tr><th>Closeout Date</th><td><?php echo html_escape($closeout['closeout_date']); ?></td></tr>
<tr><th>Jobsite Log</th><td><?php echo (int)$closeout['jobsite_log_id']; ?></td></tr>
<tr><th>Project</th><td><?php echo (int)$closeout['project_id']; ?></td></tr>
<tr><th>Ready For Customer</th><td><?php echo !empty($closeout['ready_for_customer']) ? 'Yes' : 'No'; ?></td></tr>
</tbody></table>
</div><div class="col-md-6"><div class="sc-card sc-notes-card"><strong>Closeout Package Notes</strong><br><?php echo nl2br(html_escape($closeout['closeout_package_notes'])); ?></div></div></div>
<hr>
<div class="row"><div class="col-md-6"><h5>Final Photo Checklist</h5><pre class="sc-pre-wrap"><?php echo html_escape($closeout['final_photo_checklist']); ?></pre></div><div class="col-md-6"><h5>Punch Summary</h5><pre class="sc-pre-wrap"><?php echo html_escape($closeout['punch_summary']); ?></pre></div></div>
<div class="row"><div class="col-md-6"><h5>Customer Walkthrough Notes</h5><pre class="sc-pre-wrap"><?php echo html_escape($closeout['customer_walkthrough_notes']); ?></pre></div><div class="col-md-6"><h5>Warranty Summary</h5><pre class="sc-pre-wrap"><?php echo html_escape($closeout['warranty_summary']); ?></pre></div></div>
<hr><h5>Warranty Records</h5><div class="table-responsive"><table class="table table-striped table-condensed sc-table sc-warranty-table"><thead><tr><th class="sc-left sc-col-title">Title</th><th class="sc-left">Trade</th><th class="sc-left">Type</th><th class="sc-left sc-col-date">Start</th><th class="sc-left sc-col-date">End</th><th class="sc-left">Status</th><th class="sc-actions-col">Actions</th></tr></thead><tbody>
<?php foreach (($warranties ?? []) as $w) { $wid = (int)($w['id'] ?? 0); ?><tr><td class="sc-left"><strong><?php echo html_escape($w['warranty_title']); ?></strong><br><small class="text-muted"><?php echo html_escape(substr((string)($w['coverage_notes'] ?? ''), 0, 90)); ?></small></td><td class="sc-left"><?php echo html_escape($w['trade']); ?></td><td class="sc-left"><?php echo html_escape($w['warranty_type']); ?></td><td class="sc-left"><?php echo html_escape($w['start_date']); ?></td><td class="sc-left"><?php echo html_escape($w['end_date']); ?></td><td class="sc-left"><span class="label label-success"><?php echo html_escape(ucwords(str_replace('_',' ', $w['status']))); ?></span></td><td class="sc-actions-col"><a class="btn btn-default btn-xs" href="#warranty-<?php echo $wid; ?>"><i class="fa fa-eye"></i> View</a></td></tr><?php } if (empty($warranties)) { ?><tr><td colspan="7" class="text-center text-muted">No warranty records.</td></tr><?php } ?>
</tbody></table></div>
<?php if (!empty($warranties)) { ?><div class="row sc-warranty-detail-grid"><?php foreach ($warranties as $w) { $wid = (int)($w['id'] ?? 0); ?><div class="col-md-4"><div class="sc-card sc-warranty-detail" id="warranty-<?php echo $wid; ?>"><h5><?php echo html_escape($w['warranty_title']); ?></h5><p><strong>Trade:</strong> <?php echo html_escape($w['trade']); ?><br><strong>Type:</strong> <?php echo html_escape($w['warranty_type']); ?><br><strong>Coverage:</strong> <?php echo nl2br(html_escape($w['coverage_notes'] ?? '')); ?><br><strong>Exclusions:</strong> <?php echo nl2br(html_escape($w['exclusions'] ?? '')); ?></p></div></div><?php } ?></div><?php } ?>
<h5>Follow Ups</h5><div class="table-responsive"><table class="table table-striped table-condensed sc-table"><thead><tr><th>Type</th><th>Due Date</th><th>Subject</th><th>Status</th></tr></thead><tbody>
<?php foreach (($followups ?? []) as $f) { ?><tr><td><?php echo html_escape($f['followup_type']); ?></td><td><?php echo html_escape($f['due_date']); ?></td><td><?php echo html_escape($f['message_subject']); ?></td><td><?php echo html_escape($f['status']); ?></td></tr><?php } if (empty($followups)) { ?><tr><td colspan="4" class="text-center text-muted">No follow ups.</td></tr><?php } ?>
</tbody></table></div>
<?php } ?>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>
