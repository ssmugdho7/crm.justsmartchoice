<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="scfc-settings-box">
  <h4><i class="fa fa-random"></i> <?php echo _l('scfc_settings'); ?></h4>
  <p class="text-muted">Control merge-field connector safety options.</p>
  <?php echo render_yes_no_option('scfc_enable_warnings', 'Enable delete/change warning checks'); ?>
  <?php echo render_yes_no_option('scfc_enable_health_button', 'Show Health Checker button'); ?>
  <?php echo render_yes_no_option('scfc_allow_delete_tokens', 'Allow deleting custom tokens'); ?>
  <?php echo render_input('settings[scfc_default_preview_limit]', 'Preview/Search Limit', get_option('scfc_default_preview_limit'), 'number'); ?>
  <hr>
  <a class="btn scfc-btn" href="<?php echo admin_url('smart_choice_field_connector'); ?>">Open Field Connector</a>
  <a class="btn scfc-btn-blue" href="<?php echo admin_url('smart_choice_field_connector/health'); ?>">Health Checker</a>
  <div class="scfc-help-card mtop20">
    <h5>Help Guide</h5>
    <p><strong>Mappings:</strong> show where information comes from and where it should be used.</p>
    <p><strong>Custom Tokens:</strong> create easy merge fields for data you use often.</p>
    <p><strong>Impact:</strong> checks contracts, proposals, emails, estimates, and invoices before deleting a token.</p>
    <p><strong>Health Checker:</strong> confirms required module tables exist.</p>
  </div>
</div>
