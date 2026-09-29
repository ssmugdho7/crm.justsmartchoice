<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('social_lead_funnel/_nav'); ?>
<div class="row"><div class="col-md-7"><div class="panel_s slf-card"><div class="panel-body">
<h4><?php echo html_escape($opportunity['title']); ?></h4>
<p><span class="slf-pill"><?php echo html_escape(ucfirst($opportunity['source'])); ?></span> <strong class="slf-score"><?php echo (int)$opportunity['ai_score']; ?>%</strong></p>
<p><strong><?php echo _l('name'); ?>:</strong> <?php echo html_escape($opportunity['author_name']); ?></p>
<p><strong><?php echo _l('phone'); ?>:</strong> <?php echo html_escape($opportunity['contact_phone']); ?></p>
<p><strong><?php echo _l('email'); ?>:</strong> <?php echo html_escape($opportunity['contact_email']); ?></p>
<hr><h5><?php echo _l('message'); ?></h5><div class="slf-message"><?php echo nl2br(html_escape($opportunity['message'])); ?></div>
</div></div></div><div class="col-md-5"><div class="panel_s slf-card"><div class="panel-body">
<h4><?php echo _l('social_lead_funnel_ai_reply_draft'); ?></h4>
<textarea class="form-control" rows="8" readonly><?php echo html_escape($opportunity['ai_reply_draft']); ?></textarea>
<div class="tw-mt-3">
<a class="btn btn-default btn-sm" href="<?php echo admin_url('social_lead_funnel/edit/'.$opportunity['id']); ?>"><i class="fa fa-edit"></i> <?php echo _l('edit'); ?></a>
<?php echo form_open(admin_url('social_lead_funnel/convert_to_lead/'.$opportunity['id']), ['style'=>'display:inline']); ?><button class="btn slf-btn-orange btn-sm" type="submit"><i class="fa fa-user-plus"></i> <?php echo _l('social_lead_funnel_convert_to_lead'); ?></button><?php echo form_close(); ?>
</div>
<?php if(!empty($opportunity['lead_id'])){ ?><hr><a href="<?php echo admin_url('leads/index/'.$opportunity['lead_id']); ?>" class="btn slf-btn-green btn-sm"><i class="fa fa-link"></i> <?php echo _l('lead'); ?> #<?php echo (int)$opportunity['lead_id']; ?></a><?php } ?>
</div></div></div></div>
</div></div></div></div>
<?php init_tail(); ?>
