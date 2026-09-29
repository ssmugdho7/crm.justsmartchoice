<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="slf-settings-wrap">
  <h4><i class="fa fa-bullhorn"></i> <?php echo _l('social_lead_funnel_settings'); ?></h4>
  <p class="text-muted"><?php echo _l('social_lead_funnel_settings_note'); ?></p>
  <?php echo form_open(admin_url('social_lead_funnel/settings_save')); ?>
  <div class="row">
    <div class="col-md-6">
      <?php render_yes_no_option('social_lead_funnel_enable_facebook', _l('social_lead_funnel_enable_facebook')); ?>
      <?php echo render_input('social_lead_funnel_facebook_page_id', _l('social_lead_funnel_facebook_page_id'), get_option('social_lead_funnel_facebook_page_id')); ?>
      <?php echo render_input('social_lead_funnel_facebook_access_token', _l('social_lead_funnel_facebook_access_token'), get_option('social_lead_funnel_facebook_access_token')); ?>
    </div>
    <div class="col-md-6">
      <?php render_yes_no_option('social_lead_funnel_enable_nextdoor', _l('social_lead_funnel_enable_nextdoor')); ?>
      <?php echo render_input('social_lead_funnel_nextdoor_api_key', _l('social_lead_funnel_nextdoor_api_key'), get_option('social_lead_funnel_nextdoor_api_key')); ?>
      <?php render_yes_no_option('social_lead_funnel_enable_ai_drafts', _l('social_lead_funnel_enable_ai_drafts')); ?>
    </div>
  </div>
  <?php echo render_textarea('social_lead_funnel_keywords', _l('social_lead_funnel_keywords'), get_option('social_lead_funnel_keywords'), ['rows'=>3]); ?>
  <div class="row"><div class="col-md-6"><?php render_yes_no_option('social_lead_funnel_auto_create_task', _l('social_lead_funnel_auto_create_task')); ?></div><div class="col-md-6"><?php render_yes_no_option('social_lead_funnel_auto_notify_staff', _l('social_lead_funnel_auto_notify_staff')); ?></div></div>
  <button class="btn btn-primary btn-sm"><i class="fa fa-save"></i> <?php echo _l('save'); ?></button>
  <a href="<?php echo admin_url('social_lead_funnel/health'); ?>" class="btn btn-default btn-sm"><i class="fa fa-heartbeat"></i> <?php echo _l('health_check'); ?></a>
  <a href="<?php echo admin_url('social_lead_funnel/help'); ?>" class="btn btn-default btn-sm"><i class="fa fa-question-circle"></i> <?php echo _l('help'); ?></a>
  <?php echo form_close(); ?>
</div>
