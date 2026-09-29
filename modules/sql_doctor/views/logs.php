<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h3><?php echo _l('sql_doctor_logs'); ?></h3>
<?php foreach($logs as $log){ ?>
  <h4><?php echo html_escape($log['file']); ?></h4>
  <pre style="max-height:420px;overflow:auto;background:#111;color:#e6e6e6;padding:15px;border-radius:8px;"><?php echo html_escape($log['content']); ?></pre>
<?php } ?>
<a href="<?php echo admin_url('sql_doctor'); ?>" class="btn btn-default">Back</a>
</div></div></div></div>
<?php init_tail(); ?>
