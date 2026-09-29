<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('crm_file_explorer_manager', 'assets/css/crm_file_explorer_manager.css'); ?>">
<div id="wrapper" class="crm-file-explorer"><div class="content">
<?php $this->load->view('crm_file_explorer_manager/includes/top_nav'); ?>
<div class="row"><div class="col-md-12">
  <div class="sc-help-hero sc-help-hero-premium">
    <div class="sc-help-hero-icon"><i class="fa fa-folder-open"></i></div>
    <div>
      <span class="sc-help-kicker"><?php echo _l('employee_training_manual'); ?></span>
      <h2><?php echo _l('help_guide'); ?></h2>
      <p><?php echo _l('help_intro_text'); ?></p>
      <div class="sc-help-hero-actions">
        <a class="btn sc-btn sc-btn-sm" href="<?php echo admin_url('crm_file_explorer_manager'); ?>"><i class="fa fa-dashboard"></i> <?php echo _l('file_dashboard'); ?></a>
        <a class="btn sc-btn-green sc-btn-sm" href="<?php echo admin_url('crm_file_explorer_manager/media'); ?>"><i class="fa fa-picture-o"></i> <?php echo _l('media_viewer'); ?></a>
        <a class="btn sc-btn-dark sc-btn-sm" href="<?php echo admin_url('crm_file_explorer_manager/health'); ?>"><i class="fa fa-heartbeat"></i> <?php echo _l('health_checker'); ?></a>
      </div>
    </div>
  </div>

  <div class="sc-help-alert"><i class="fa fa-exclamation-triangle"></i><div><strong><?php echo _l('help_safety_title'); ?></strong><p><?php echo _l('help_safety_text'); ?></p></div></div>

  <div class="sc-help-process">
    <div><span>1</span><strong><?php echo _l('help_process_check'); ?></strong><small><?php echo _l('help_process_check_text'); ?></small></div>
    <i class="fa fa-chevron-right"></i>
    <div><span>2</span><strong><?php echo _l('help_process_scan'); ?></strong><small><?php echo _l('help_process_scan_text'); ?></small></div>
    <i class="fa fa-chevron-right"></i>
    <div><span>3</span><strong><?php echo _l('help_process_review'); ?></strong><small><?php echo _l('help_process_review_text'); ?></small></div>
    <i class="fa fa-chevron-right"></i>
    <div><span>4</span><strong><?php echo _l('help_process_backup'); ?></strong><small><?php echo _l('help_process_backup_text'); ?></small></div>
  </div>

  <div class="sc-help-grid sc-help-grid-detailed">
    <section class="sc-help-section sc-help-section-orange"><h4><span>1</span><?php echo _l('help_what_module_does_title'); ?></h4><p><?php echo _l('help_what_module_does_text'); ?></p><ul><li><?php echo _l('help_overview_item_1'); ?></li><li><?php echo _l('help_overview_item_2'); ?></li><li><?php echo _l('help_overview_item_3'); ?></li><li><?php echo _l('help_overview_item_4'); ?></li></ul><div class="sc-tip"><i class="fa fa-lightbulb-o"></i><?php echo _l('help_overview_tip'); ?></div></section>
    <section class="sc-help-section sc-help-section-green"><h4><span>2</span><?php echo _l('help_before_start_title'); ?></h4><p><?php echo _l('help_before_start_text'); ?></p><ol><li><?php echo _l('help_before_start_step_1'); ?></li><li><?php echo _l('help_before_start_step_2'); ?></li><li><?php echo _l('help_before_start_step_3'); ?></li><li><?php echo _l('help_before_start_step_4'); ?></li><li><?php echo _l('help_before_start_step_5'); ?></li></ol></section>
    <section class="sc-help-section sc-help-section-dark"><h4><span>3</span><?php echo _l('help_scan_title'); ?></h4><p><?php echo _l('help_scan_text'); ?></p><ol><li><?php echo _l('help_scan_step_1'); ?></li><li><?php echo _l('help_scan_step_2'); ?></li><li><?php echo _l('help_scan_step_3'); ?></li><li><?php echo _l('help_scan_step_4'); ?></li><li><?php echo _l('help_scan_step_5'); ?></li><li><?php echo _l('help_scan_step_6'); ?></li></ol><div class="sc-tip"><i class="fa fa-clock-o"></i><?php echo _l('help_scan_tip'); ?></div></section>
    <section class="sc-help-section sc-help-section-orange"><h4><span>4</span><?php echo _l('help_dashboard_title'); ?></h4><p><?php echo _l('help_dashboard_text'); ?></p><ul><li><?php echo _l('help_dashboard_item_1'); ?></li><li><?php echo _l('help_dashboard_item_2'); ?></li><li><?php echo _l('help_dashboard_item_3'); ?></li><li><?php echo _l('help_dashboard_item_4'); ?></li></ul><div class="sc-help-definition"><strong><?php echo _l('help_dashboard_definition_title'); ?></strong><?php echo _l('help_dashboard_definition_text'); ?></div></section>
    <section class="sc-help-section sc-help-section-green"><h4><span>5</span><?php echo _l('help_media_title'); ?></h4><p><?php echo _l('help_media_text'); ?></p><ol><li><?php echo _l('help_media_step_1'); ?></li><li><?php echo _l('help_media_step_2'); ?></li><li><?php echo _l('help_media_step_3'); ?></li><li><?php echo _l('help_media_step_4'); ?></li><li><?php echo _l('help_media_step_5'); ?></li></ol><div class="sc-help-definition"><strong><?php echo _l('help_thumbnail_title'); ?></strong><?php echo _l('help_thumbnail_text'); ?></div></section>
    <section class="sc-help-section sc-help-section-dark"><h4><span>6</span><?php echo _l('help_file_types_title'); ?></h4><p><?php echo _l('help_file_types_text'); ?></p><ul><li><?php echo _l('help_file_type_1'); ?></li><li><?php echo _l('help_file_type_2'); ?></li><li><?php echo _l('help_file_type_3'); ?></li><li><?php echo _l('help_file_type_4'); ?></li><li><?php echo _l('help_file_type_5'); ?></li></ul></section>
    <section class="sc-help-section sc-help-section-orange"><h4><span>7</span><?php echo _l('help_backup_title'); ?></h4><p><?php echo _l('help_backup_text'); ?></p><ol><li><?php echo _l('help_backup_step_1'); ?></li><li><?php echo _l('help_backup_step_2'); ?></li><li><?php echo _l('help_backup_step_3'); ?></li><li><?php echo _l('help_backup_step_4'); ?></li><li><?php echo _l('help_backup_step_5'); ?></li></ol><div class="sc-tip"><i class="fa fa-shield"></i><?php echo _l('help_backup_tip'); ?></div></section>
    <section class="sc-help-section sc-help-section-green"><h4><span>8</span><?php echo _l('help_associations_title'); ?></h4><p><?php echo _l('help_associations_text'); ?></p><ul><li><?php echo _l('help_association_item_1'); ?></li><li><?php echo _l('help_association_item_2'); ?></li><li><?php echo _l('help_association_item_3'); ?></li></ul><div class="sc-help-definition"><strong><?php echo _l('help_association_example_title'); ?></strong><?php echo _l('help_association_example_text'); ?></div></section>
    <section class="sc-help-section sc-help-section-dark"><h4><span>9</span><?php echo _l('help_health_title'); ?></h4><p><?php echo _l('help_health_text'); ?></p><ol><li><?php echo _l('help_health_step_1'); ?></li><li><?php echo _l('help_health_step_2'); ?></li><li><?php echo _l('help_health_step_3'); ?></li><li><?php echo _l('help_health_step_4'); ?></li></ol><div class="sc-tip"><i class="fa fa-check-circle"></i><?php echo _l('help_health_tip'); ?></div></section>
    <section class="sc-help-section sc-help-section-orange"><h4><span>10</span><?php echo _l('help_settings_title'); ?></h4><p><?php echo _l('help_settings_text'); ?></p><ol><li><?php echo _l('help_settings_step_1'); ?></li><li><?php echo _l('help_settings_step_2'); ?></li><li><?php echo _l('help_settings_step_3'); ?></li><li><?php echo _l('help_settings_step_4'); ?></li></ol><div class="sc-help-definition"><strong><?php echo _l('help_setting_fields_title'); ?></strong><?php echo _l('help_setting_fields_text'); ?></div></section>
    <section class="sc-help-section sc-help-section-green"><h4><span>11</span><?php echo _l('help_reset_title'); ?></h4><p><?php echo _l('help_reset_text'); ?></p><div class="sc-warning"><strong><?php echo _l('warning'); ?>:</strong> <?php echo _l('help_reset_warning'); ?></div><ol><li><?php echo _l('help_reset_step_1'); ?></li><li><?php echo _l('help_reset_step_2'); ?></li><li><?php echo _l('help_reset_step_3'); ?></li><li><?php echo _l('help_reset_step_4'); ?></li></ol></section>
    <section class="sc-help-section sc-help-section-dark"><h4><span>12</span><?php echo _l('help_troubleshooting_title'); ?></h4><ul><li><?php echo _l('help_troubleshooting_item_1'); ?></li><li><?php echo _l('help_troubleshooting_item_2'); ?></li><li><?php echo _l('help_troubleshooting_item_3'); ?></li><li><?php echo _l('help_troubleshooting_item_4'); ?></li><li><?php echo _l('help_troubleshooting_item_5'); ?></li><li><?php echo _l('help_troubleshooting_item_6'); ?></li><li><?php echo _l('help_troubleshooting_item_7'); ?></li></ul></section>
  </div>

  <div class="sc-help-final-checklist">
    <h3><i class="fa fa-check-square-o"></i> <?php echo _l('help_daily_checklist_title'); ?></h3>
    <div class="sc-checklist-grid"><div><?php echo _l('help_daily_check_1'); ?></div><div><?php echo _l('help_daily_check_2'); ?></div><div><?php echo _l('help_daily_check_3'); ?></div><div><?php echo _l('help_daily_check_4'); ?></div><div><?php echo _l('help_daily_check_5'); ?></div><div><?php echo _l('help_daily_check_6'); ?></div></div>
  </div>
</div></div></div></div>
<?php init_tail(); ?></body></html>
