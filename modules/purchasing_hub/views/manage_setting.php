<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body">
  <?php echo form_open(admin_url('purchasing_hub/settings')); ?>
    <?php echo render_input('purchasing_hub_default_tax_rate', 'Default Tax Rate', get_option('purchasing_hub_default_tax_rate'), 'number', ['step'=>'0.01']); ?>
    <?php echo render_textarea('purchasing_hub_default_terms', 'Default Terms', get_option('purchasing_hub_default_terms')); ?>
    <div class="checkbox checkbox-primary"><input type="checkbox" id="purchasing_hub_allow_item_images" name="purchasing_hub_allow_item_images" value="1" <?php echo get_option('purchasing_hub_allow_item_images') == '1' ? 'checked' : ''; ?>><label for="purchasing_hub_allow_item_images">Allow Item Images</label></div>
    <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
  <?php echo form_close(); ?>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>
