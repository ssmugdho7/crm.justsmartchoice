<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?><div id="wrapper"><div class="content"><?php $this->load->view("_navigation"); ?>
<div class="panel_s"><div class="panel-body">
<a href="<?php echo admin_url('smart_daily_productivity/reports'); ?>" class="btn btn-default btn-sm">&larr; Reports</a>
<h3><?php echo html_escape(trim($member->firstname.' '.$member->lastname)); ?> — Daily Work</h3>
<p class="text-muted"><?php echo _d($date); ?> | <?php echo count($entries); ?> recorded activities</p>
<?php $minutes=0; foreach($entries as $item){$minutes+=(int)$item['minutes_spent'];} ?>
<div class="alert alert-info">Total reported time: <strong><?php echo round($minutes/60,2); ?> hours</strong> (<?php echo $minutes; ?> minutes)</div>
<?php if (!$entries){ ?><p class="alert alert-warning">No recorded activities for this employee on this date. This does not establish that no work was performed.</p><?php } ?>
<?php foreach($entries as $item){ ?><div class="panel panel-default"><div class="panel-heading"><strong><?php echo html_escape($item['title']); ?></strong> <span class="pull-right"><?php echo (int)$item['minutes_spent']; ?> minutes</span></div><div class="panel-body"><p><strong>Category:</strong> <?php echo html_escape($item['category']); ?> &nbsp; <strong>Source:</strong> <?php echo html_escape($item['source_type']); ?> &nbsp; <strong>Status:</strong> <?php echo html_escape($item['status']); ?></p><div style="white-space:pre-wrap;overflow-wrap:anywhere"><?php echo html_escape((string)$item['description']); ?></div></div></div><?php } ?>
</div></div></div></div><?php init_tail(); ?>
