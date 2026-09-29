<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); $command = $command ?? []; ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?><h4><?php echo html_escape($title); ?></h4>
<?php echo form_open(current_full_url()); ?>
<?php echo render_textarea('command_text', 'Command Text', $command['command_text'] ?? '', ['rows'=>5]); ?>
<div class="row"><div class="col-md-4"><?php echo render_select('command_source', [['id'=>'typed','name'=>'Typed'],['id'=>'voice','name'=>'Voice'],['id'=>'camera','name'=>'Camera'],['id'=>'mobile','name'=>'Mobile']], ['id','name'], 'Command Source', $command['command_source'] ?? 'typed'); ?></div><div class="col-md-4"><?php echo render_input('intent', 'Intent', $command['intent'] ?? 'crm_assistant'); ?></div><div class="col-md-4"><?php echo render_input('target_module', 'Target Module', $command['target_module'] ?? ''); ?></div></div>
<div class="row"><div class="col-md-4"><?php echo render_input('target_record_type', 'Target Record Type', $command['target_record_type'] ?? ''); ?></div><div class="col-md-4"><?php echo render_input('target_record_id', 'Target Record ID', $command['target_record_id'] ?? 0, 'number'); ?></div><div class="col-md-4"><?php echo render_select('action_status', [['id'=>'draft','name'=>'Draft'],['id'=>'pending_review','name'=>'Pending Review'],['id'=>'approved','name'=>'Approved'],['id'=>'completed','name'=>'Completed'],['id'=>'rejected','name'=>'Rejected']], ['id','name'], 'Status', $command['action_status'] ?? 'draft'); ?></div></div>
<?php echo render_textarea('ai_response', 'AI Response / Action Preview', $command['ai_response'] ?? '', ['rows'=>4]); ?>
<?php echo render_textarea('review_notes', 'Review Notes', $command['review_notes'] ?? '', ['rows'=>3]); ?>
<button class="btn btn-primary btn-sm" type="submit">Save</button> <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_commands'); ?>">Back</a>
<?php echo form_close(); ?></div></div></div></div><?php init_tail(); ?>
