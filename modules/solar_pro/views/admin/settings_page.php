<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content solar-pro-shell">
  <div class="solar-hero compact"><div><span class="solar-kicker"><?php echo _l('solar_pro_settings'); ?></span><h1><?php echo _l('solar_pro_settings'); ?></h1><p><?php echo _l('solar_pro_settings_subtitle'); ?></p></div><div class="solar-actions"><a class="btn btn-default" target="_blank" href="<?php echo site_url('solar_pro/estimate'); ?>"><i class="fa-solid fa-arrow-up-right-from-square"></i> <?php echo _l('solar_pro_open_public_portal'); ?></a><a class="btn btn-default" href="<?php echo admin_url('settings?group=solar_pro'); ?>"><i class="fa-solid fa-sliders"></i> <?php echo _l('solar_pro_open_native_settings'); ?></a></div></div>
  <?php echo form_open(admin_url('solar_pro/settings_save')); $this->load->view('settings/settings'); ?>
  <div class="panel_s"><div class="panel-body text-right"><button class="btn solar-btn-primary"><i class="fa fa-save"></i> <?php echo _l('solar_pro_save'); ?></button></div></div>
  <?php echo form_close(); ?>
</div></div>
<?php init_tail(); ?>
