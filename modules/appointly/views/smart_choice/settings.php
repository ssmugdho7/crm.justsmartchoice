<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="tw-p-4">
  <div class="panel_s">
    <div class="panel-body">
      <h4 class="tw-mt-0"><i class="fa-solid fa-bell text-success"></i> <?php echo _l('appointly_smartchoice_notifications'); ?></h4>
      <p class="text-muted"><?php echo _l('appointly_smartchoice_settings_help'); ?></p>
      <hr />
      <?php echo form_open(admin_url('settings?group=appointly-smart-choice-settings')); ?>
      <div class="row">
        <div class="col-md-6">
          <?php echo render_yes_no_option('appointly_sc_daily_popup_enabled', _l('appointly_daily_popup_enabled')); ?>
          <?php echo render_yes_no_option('appointly_sc_sound_enabled', _l('appointly_sound_enabled')); ?>
          <?php echo render_yes_no_option('appointly_sc_daily_staff_notification_enabled', _l('appointly_daily_staff_notification_enabled')); ?>
          <?php echo render_yes_no_option('appointly_sc_one_hour_reminder_enabled', _l('appointly_one_hour_reminder_enabled')); ?>
        </div>
        <div class="col-md-6">
          <?php echo render_yes_no_option('appointly_sc_email_enabled', _l('appointly_email_enabled')); ?>
          <?php echo render_yes_no_option('appointly_sc_telegram_enabled', _l('appointly_telegram_enabled')); ?>
          <?php echo render_input('settings[appointly_sc_telegram_bot_token]', _l('appointly_telegram_bot_token'), get_option('appointly_sc_telegram_bot_token'), 'password'); ?>
          <?php echo render_input('settings[appointly_sc_telegram_default_chat_id]', _l('appointly_telegram_default_chat_id'), get_option('appointly_sc_telegram_default_chat_id')); ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <?php echo render_input('settings[appointly_sc_popup_auto_close_seconds]', _l('appointly_popup_auto_close_seconds'), get_option('appointly_sc_popup_auto_close_seconds'), 'number', ['min' => '3', 'max' => '60']); ?>
        </div>
        <div class="col-md-4">
          <?php echo render_input('settings[appointly_sc_reminder_minutes_before]', _l('appointly_reminder_minutes_before'), get_option('appointly_sc_reminder_minutes_before'), 'number', ['min' => '15', 'max' => '240']); ?>
        </div>
        <div class="col-md-4">
          <?php echo render_input('settings[appointly_sc_dispatch_department]', _l('appointly_dispatch_department'), get_option('appointly_sc_dispatch_department')); ?>
        </div>
      </div>
      <div class="row"><div class="col-md-6"><?php echo render_yes_no_option('appointly_sc_facebook_enabled','Enable Facebook / Meta Notifications'); ?><?php echo render_input('settings[appointly_sc_facebook_page_token]','Facebook Page Access Token',get_option('appointly_sc_facebook_page_token'),'password'); ?></div><div class="col-md-6"><?php echo render_textarea('settings[appointly_sc_on_the_way_message]','Installer On The Way customer message',get_option('appointly_sc_on_the_way_message')); ?><?php echo render_textarea('settings[appointly_sc_ten_minutes_message]','10 Minutes Away customer message',get_option('appointly_sc_ten_minutes_message')); ?></div></div><div class="alert alert-warning">Telegram requires a bot token and chat ID. Facebook requires a valid Meta Page token and approved messaging setup. GPS-based ten-minute alerts require live tracker ETA data.</div>
      <hr />
      <h4><i class="fa fa-magic text-primary"></i> Booking Automation</h4>
      <p class="text-muted">Choose which CRM records Appointly may create after a successful booking. Existing records are never overwritten.</p>
      <div class="row"><div class="col-md-3"><?php echo render_yes_no_option('appointly_sc_create_customer_on_booking','Create Customer'); ?></div><div class="col-md-3"><?php echo render_yes_no_option('appointly_sc_create_estimate_on_booking','Create Estimate'); ?></div><div class="col-md-3"><?php echo render_yes_no_option('appointly_sc_create_proposal_on_booking','Create Proposal'); ?></div><div class="col-md-3"><?php echo render_yes_no_option('appointly_show_invoice_option','Create Invoice'); ?></div></div>
      <div class="alert alert-info">Estimate, proposal, and invoice creation requires a matched customer contact and a paid service. Customer creation is limited to external bookings with a valid email address.</div>

      <hr />
      <h4><i class="fa-solid fa-route text-info"></i> <?php echo _l('appointly_installer_tracking_settings'); ?></h4>
      <div class="row">
        <div class="col-md-6">
          <?php echo render_yes_no_option('appointly_sc_installer_tracking_enabled', _l('appointly_installer_tracking_enabled')); ?>
          <?php echo render_yes_no_option('appointly_sc_customer_tracking_enabled', _l('appointly_customer_tracking_enabled')); ?>
        </div>
        <div class="col-md-6">
          <?php echo render_input('settings[appointly_sc_customer_tracking_portal_slug]', _l('appointly_customer_tracking_portal_slug'), get_option('appointly_sc_customer_tracking_portal_slug')); ?>
          <?php echo render_input('settings[appointly_sc_tracking_eta_label]', _l('appointly_tracking_eta_label'), get_option('appointly_sc_tracking_eta_label')); ?>
        </div>
      </div>

      <hr />
      <h4><i class="fa-solid fa-palette text-warning"></i> <?php echo _l('appointly_form_display_settings'); ?></h4>
      <p class="text-muted"><?php echo _l('appointly_form_display_settings_help'); ?></p>
      <div class="row">
        <div class="col-md-4">
          <?php echo render_yes_no_option('appointly_sc_show_public_logo', _l('appointly_show_public_logo')); ?>
        </div>
        <div class="col-md-4">
          <?php echo render_input('settings[appointly_sc_public_logo_width]', _l('appointly_public_logo_width'), get_option('appointly_sc_public_logo_width'), 'number', ['min' => '20', 'max' => '240']); ?>
        </div>
        <div class="col-md-4">
          <?php echo render_input('settings[appointly_sc_public_logo_height]', _l('appointly_public_logo_height'), get_option('appointly_sc_public_logo_height'), 'number', ['min' => '10', 'max' => '120']); ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <?php echo render_yes_no_option('appointly_sc_compact_ui_enabled', _l('appointly_compact_ui_enabled')); ?>
        </div>
        <div class="col-md-6">
          <?php echo render_yes_no_option('appointly_sc_single_scroll_enabled', _l('appointly_single_scroll_enabled')); ?>
        </div>
      </div>

      <div class="alert alert-info">
        <strong>Clean Booking Link:</strong><br>
        <code><?php echo site_url('appointly/appointments'); ?></code><br>
        <span class="text-muted">Use this clean link in the AI chat, website buttons, QR codes, SMS, and customer emails. It opens the same public booking form without the column code.</span>
      </div>

      <div class="btn-bottom-toolbar text-right">
        <button type="submit" class="btn btn-primary"><?php echo _l('settings_save'); ?></button>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>
<style>.onoffswitch-inner:before{content:"Yes"!important}.onoffswitch-inner:after{content:"No"!important}.btn{white-space:normal}</style>