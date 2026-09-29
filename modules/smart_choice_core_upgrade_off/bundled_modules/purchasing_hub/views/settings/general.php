<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s purchasing-hub-page"><div class="panel-body">
  <h4>Purchasing Hub Settings</h4>
  <p class="text-muted">Configure defaults for Purchasing Hub. Operational screens are available from the Purchasing Hub menu.</p>
  <?php echo render_input('settings[purchasing_hub_default_tax_rate]', 'Default Tax Rate', get_option('purchasing_hub_default_tax_rate'), 'number', ['step'=>'0.01']); ?>
  <?php echo render_textarea('settings[purchasing_hub_default_terms]', 'Default Purchasing Terms', get_option('purchasing_hub_default_terms')); ?>
  <?php echo render_yes_no_option('purchasing_hub_allow_item_images', 'Allow Item Images'); ?>
  <hr>
  <a href="<?php echo admin_url('purchasing_hub'); ?>" class="btn btn-primary">Open Purchasing Hub</a>
  <a href="<?php echo admin_url('purchasing_hub/reports'); ?>" class="btn btn-info">Open Purchasing Reports</a>
</div></div>
