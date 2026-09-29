<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('social_lead_funnel/_nav'); ?>
<div class="panel_s"><div class="panel-body">
<h4><?php echo _l('social_lead_funnel_templates'); ?></h4><hr>
<?php echo form_open(admin_url('social_lead_funnel/template_save')); ?>
<div class="row"><div class="col-md-3"><?php echo render_input('name','name'); ?></div><div class="col-md-2"><?php echo render_input('source','source','all'); ?></div><div class="col-md-2"><?php echo render_input('language','language','english'); ?></div><div class="col-md-2"><div class="checkbox checkbox-primary"><input type="checkbox" name="active" checked id="active"><label for="active"><?php echo _l('active'); ?></label></div></div></div>
<?php echo render_textarea('body','message','',['rows'=>4]); ?><button class="btn slf-btn-green btn-sm"><?php echo _l('save'); ?></button><?php echo form_close(); ?>
<hr><table class="table dt-table"><thead><tr><th><?php echo _l('name'); ?></th><th><?php echo _l('source'); ?></th><th><?php echo _l('language'); ?></th><th><?php echo _l('active'); ?></th></tr></thead><tbody><?php foreach($templates as $t){ ?><tr><td><?php echo html_escape($t['name']); ?></td><td><?php echo html_escape($t['source']); ?></td><td><?php echo html_escape($t['language']); ?></td><td><?php echo $t['active']?'Yes':'No'; ?></td></tr><?php } ?></tbody></table>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>
