<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="cm-module-nav">
  <a href="<?= admin_url('cabinet_maker/dashboard'); ?>"><i class="fa fa-dashboard"></i> <?= _l('cabinet_maker_dashboard'); ?></a>
  <a href="<?= admin_url('cabinet_maker'); ?>"><i class="fa fa-cubes"></i> <?= _l('cabinet_maker_designs'); ?></a>
  <a href="<?= admin_url('cabinet_maker/materials'); ?>"><i class="fa fa-clone"></i> <?= _l('cabinet_maker_materials'); ?></a>
  <a href="<?= admin_url('cabinet_maker/vendors'); ?>"><i class="fa fa-truck"></i> <?= _l('cabinet_maker_vendors'); ?></a>
  <a href="<?= admin_url('cabinet_maker/offcuts'); ?>"><i class="fa fa-recycle"></i> <?= _l('cabinet_maker_offcuts'); ?></a>
  <a href="<?= admin_url('cabinet_maker/health_check'); ?>"><i class="fa fa-heartbeat"></i> <?= _l('cabinet_maker_health_check'); ?></a>
  <a href="<?= admin_url('cabinet_maker/settings'); ?>"><i class="fa fa-cogs"></i> <?= _l('cabinet_maker_settings'); ?></a>
</div>
