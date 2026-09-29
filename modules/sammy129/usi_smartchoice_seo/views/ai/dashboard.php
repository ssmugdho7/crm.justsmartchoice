<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<h4 class="sc-title"><i class="fa fa-microchip"></i> <?php echo html_escape($title); ?></h4>
<div class="row sc-dashboard-cards">
<?php foreach ($counts as $label => $count) { ?>
  <div class="col-md-3 col-sm-6"><div class="sc-card"><div class="sc-card-count"><?php echo (int)$count; ?></div><div class="sc-card-label"><?php echo ucwords(str_replace('_', ' ', html_escape($label))); ?></div></div></div>
<?php } ?>
</div>
<div class="alert alert-info mtop15">This module controls AI CRM intake for voice commands, camera estimate records, AI training rules, website pages, SEO keywords, and operational reports for justsmartchoice.com. All CRM-changing commands remain in review-first mode unless an administrator changes the setting.</div>
</div></div></div></div><?php init_tail(); ?>
