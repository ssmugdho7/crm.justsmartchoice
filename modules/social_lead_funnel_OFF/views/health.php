<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('social_lead_funnel/_nav'); ?>
<div class="panel_s"><div class="panel-body"><h4><?php echo _l('social_lead_funnel_health'); ?></h4><hr><table class="table table-bordered"><tbody><?php foreach($health as $k=>$v){ ?><tr><td><?php echo html_escape($k); ?></td><td><pre><?php echo html_escape(is_array($v)?json_encode($v, JSON_PRETTY_PRINT):$v); ?></pre></td></tr><?php } ?></tbody></table></div></div>
</div></div></div></div><?php init_tail(); ?>
