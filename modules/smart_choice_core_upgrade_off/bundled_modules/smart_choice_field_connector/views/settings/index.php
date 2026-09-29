<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="scfc-settings-box">
  <h4><i class="fa fa-random"></i> <?php echo _l('scfc_settings'); ?></h4>
  <p class="text-muted"><?php echo _l('scfc_settings_help'); ?></p>
  <?php echo render_yes_no_option('scfc_enable_warnings', 'scfc_enable_warning_checks'); ?>
  <?php echo render_yes_no_option('scfc_enable_health_button', 'scfc_show_health_button'); ?>
  <?php echo render_yes_no_option('scfc_allow_delete_tokens', 'scfc_allow_delete_tokens'); ?>
  <?php echo render_yes_no_option('scfc_enable_visual_builder', 'scfc_enable_visual_builder'); ?>
  <?php echo render_input('settings[scfc_default_preview_limit]', 'scfc_preview_limit', get_option('scfc_default_preview_limit'), 'number'); ?>
  <hr>
  <a class="btn scfc-btn" href="<?php echo admin_url('smart_choice_field_connector'); ?>"><?php echo _l('scfc_open_connector'); ?></a>
  <a class="btn scfc-btn-blue" href="<?php echo admin_url('smart_choice_field_connector/health'); ?>"><?php echo _l('scfc_health_check'); ?></a>
  <a class="btn btn-default" href="<?php echo admin_url('smart_choice_field_connector/repair_database'); ?>"><?php echo _l('scfc_repair_database'); ?></a>
  <div class="scfc-help-card mtop20">
    <h5><?php echo _l('scfc_help_guide'); ?></h5>
    <p><strong><?php echo _l('scfc_groups'); ?>:</strong> <?php echo _l('scfc_settings_help_groups'); ?></p>
    <p><strong><?php echo _l('scfc_mappings'); ?>:</strong> <?php echo _l('scfc_settings_help_mappings'); ?></p>
    <p><strong><?php echo _l('scfc_combinations'); ?>:</strong> <?php echo _l('scfc_settings_help_combinations'); ?></p>
    <p><strong><?php echo _l('scfc_impact'); ?>:</strong> <?php echo _l('scfc_settings_help_impact'); ?></p>
  </div>
</div>
