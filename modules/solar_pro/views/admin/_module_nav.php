<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<nav class="solar-module-nav" aria-label="<?php echo html_escape(_l('solar_pro_module_menu')); ?>">
  <a href="<?php echo admin_url('solar_pro'); ?>"><i class="fa-solid fa-gauge-high"></i> <?php echo _l('solar_pro_dashboard_short'); ?></a>
  <a href="<?php echo admin_url('solar_pro/analyses'); ?>"><i class="fa-solid fa-chart-line"></i> <?php echo _l('solar_pro_analyses'); ?></a>
  <?php if(is_admin()||staff_can('create','solar_pro')): ?><a href="<?php echo admin_url('solar_pro/analysis'); ?>"><i class="fa-solid fa-circle-plus"></i> <?php echo _l('solar_pro_new_analysis'); ?></a><?php endif; ?>
  <a href="<?php echo admin_url('solar_pro/utilities'); ?>"><i class="fa-solid fa-bolt"></i> <?php echo _l('solar_pro_utilities'); ?></a>
  <a href="<?php echo admin_url('solar_pro/equipment'); ?>"><i class="fa-solid fa-solar-panel"></i> <?php echo _l('solar_pro_equipment'); ?></a>
  <a href="<?php echo admin_url('solar_pro/proposals'); ?>"><i class="fa-solid fa-file-invoice-dollar"></i> <?php echo _l('solar_pro_proposals'); ?></a>
  <a href="<?php echo admin_url('solar_pro/proposal_templates'); ?>"><i class="fa-solid fa-palette"></i> <?php echo _l('solar_pro_proposal_templates_short'); ?></a>
  <a href="<?php echo admin_url('solar_pro/contracts'); ?>"><i class="fa-solid fa-file-contract"></i> <?php echo _l('solar_pro_contracts'); ?></a>
  <a href="<?php echo admin_url('solar_pro/contract_templates'); ?>"><i class="fa-solid fa-file-lines"></i> <?php echo _l('solar_pro_templates'); ?></a>
  <a href="<?php echo admin_url('settings?group=solar_pro'); ?>"><i class="fa-solid fa-sliders"></i> <?php echo _l('solar_pro_settings'); ?></a>
</nav>
