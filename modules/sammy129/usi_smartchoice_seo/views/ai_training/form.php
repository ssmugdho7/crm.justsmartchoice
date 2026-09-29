<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); $item = $item ?? []; ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?><h4><?php echo html_escape($title); ?></h4>
<?php echo form_open(current_full_url()); ?>
<?php echo render_input('title', 'Training Rule Title', $item['title'] ?? ''); ?>
<div class="row"><div class="col-md-6"><?php echo render_select('category', [['id'=>'crm_workflow','name'=>'CRM Workflow'],['id'=>'voice_command','name'=>'Voice Command'],['id'=>'camera_estimate','name'=>'Camera Estimate'],['id'=>'seo_content','name'=>'SEO Content'],['id'=>'safety_rule','name'=>'Safety Rule']], ['id','name'], 'Category', $item['category'] ?? 'crm_workflow'); ?></div><div class="col-md-6"><?php echo render_select('status', [['id'=>'active','name'=>'Active'],['id'=>'review','name'=>'Review'],['id'=>'paused','name'=>'Paused'],['id'=>'archived','name'=>'Archived'],['id'=>'testing','name'=>'Testing']], ['id','name'], 'Status', $item['status'] ?? 'active'); ?></div></div>
<?php echo render_textarea('prompt_text', 'Prompt Text', $item['prompt_text'] ?? '', ['rows'=>7]); ?>
<?php echo render_textarea('expected_behavior', 'Expected Behavior', $item['expected_behavior'] ?? '', ['rows'=>7]); ?>
<button class="btn btn-primary btn-sm" type="submit">Save</button> <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_training'); ?>">Back</a>
<?php echo form_close(); ?></div></div></div></div><?php init_tail(); ?>
