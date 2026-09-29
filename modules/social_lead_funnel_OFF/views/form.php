<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-10 col-md-offset-1">
<?php $this->load->view('social_lead_funnel/_nav'); ?>
<div class="panel_s slf-card"><div class="panel-body">
<h4><?php echo isset($opportunity) ? _l('edit') : _l('social_lead_funnel_new_opportunity'); ?></h4><hr>
<?php echo form_open(current_url()); ?>
<div class="row">
 <div class="col-md-4"><?php echo render_input('author_name','social_lead_funnel_author', $opportunity['author_name'] ?? ''); ?></div>
 <div class="col-md-4"><?php echo render_input('contact_phone','phone', $opportunity['contact_phone'] ?? ''); ?></div>
 <div class="col-md-4"><?php echo render_input('contact_email','email', $opportunity['contact_email'] ?? ''); ?></div>
</div>
<div class="row">
 <div class="col-md-4"><?php echo render_select('source', [['id'=>'manual','name'=>'Manual'],['id'=>'facebook','name'=>'Facebook'],['id'=>'nextdoor','name'=>'Nextdoor'],['id'=>'messenger','name'=>'Messenger'],['id'=>'lead_ad','name'=>'Lead Ad'],['id'=>'comment','name'=>'Comment']], ['id','name'], 'source', $opportunity['source'] ?? 'manual'); ?></div>
 <div class="col-md-4"><?php echo render_input('service_type','social_lead_funnel_service', $opportunity['service_type'] ?? ''); ?></div>
 <div class="col-md-4"><?php echo render_input('post_url','social_lead_funnel_post_url', $opportunity['post_url'] ?? ''); ?></div>
</div>
<?php echo render_input('title','title', $opportunity['title'] ?? ''); ?>
<?php echo render_textarea('message','message', $opportunity['message'] ?? '', ['rows'=>6]); ?>
<?php echo render_textarea('ai_reply_draft','social_lead_funnel_ai_reply_draft', $opportunity['ai_reply_draft'] ?? '', ['rows'=>5]); ?>
<button class="btn slf-btn-green" type="submit"><i class="fa fa-save"></i> <?php echo _l('submit'); ?></button>
<?php echo form_close(); ?>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>
