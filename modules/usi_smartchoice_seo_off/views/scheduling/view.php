<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body">
<?php if(empty($schedule)){ ?><div class="alert alert-warning">Schedule not found.</div><?php } else { ?>
<div class="_buttons mbottom15">
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/scheduling'); ?>">Back</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/schedule_plan/'.(int)$schedule['id']); ?>">Edit</a>
<a class="btn btn-info btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/generate_schedule_items/'.(int)$schedule['id']); ?>">Generate Schedule Items</a>
<a class="btn btn-success btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/mark_schedule_ready/'.(int)$schedule['id']); ?>">Ready For Calendar</a>
</div>
<h4><?php echo html_escape($schedule['title']); ?></h4>
<p><strong>Status:</strong> <?php echo html_escape(ucwords(str_replace('_',' ',(string)$schedule['status']))); ?> | <strong>Start:</strong> <?php echo html_escape($schedule['start_date']); ?> | <strong>End:</strong> <?php echo html_escape($schedule['end_date']); ?></p>
<div class="well"><strong>Summary:</strong><br><?php echo nl2br(html_escape($schedule['schedule_summary'])); ?><br><br><strong>Conflict Notes:</strong><br><?php echo nl2br(html_escape($schedule['conflict_notes'])); ?></div>
<div class="table-responsive"><table class="table table-bordered table-striped sc-table-compact">
<thead><tr><th>#</th><th>Item</th><th>Type</th><th>Start</th><th>End</th><th>Duration</th><th>Status</th><th>Notes</th></tr></thead><tbody>
<?php foreach($items as $item){ ?><tr>
<td><?php echo (int)$item['item_order']; ?></td><td><?php echo html_escape($item['item_title']); ?></td><td><?php echo html_escape($item['item_type']); ?></td><td><?php echo html_escape($item['planned_start']); ?></td><td><?php echo html_escape($item['planned_end']); ?></td><td><?php echo (int)$item['duration_days']; ?></td><td><?php echo html_escape($item['status']); ?></td><td><?php echo html_escape($item['notes']); ?></td>
</tr><?php } if(empty($items)){ ?><tr><td colspan="8" class="text-center">No schedule items found.</td></tr><?php } ?>
</tbody></table></div>
<?php } ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>
