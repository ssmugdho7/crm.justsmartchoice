<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="crm-file-explorer crm-file-explorer-settings-panel">
  <div class="sc-settings-header">
    <div>
      <h4 class="sc-title"><i class="fa fa-folder-open"></i> <?php echo _l('crm_file_explorer_manager_settings'); ?></h4>
      <p class="text-muted"><?php echo _l('settings_intro'); ?></p>
    </div>
  </div>
  <?php echo form_open(admin_url('crm_file_explorer_manager/settings_save')); ?>
    <input type="hidden" name="redirect_back" value="<?php echo admin_url('settings?group=crm_file_explorer_manager_settings'); ?>">
    <div class="sc-settings-section">
      <h5><i class="fa fa-folder"></i> <?php echo _l('settings_scan_section'); ?></h5>
      <p><?php echo _l('settings_scan_section_help'); ?></p>
      <?php echo render_input('root_path', 'root_folder', get_option('crm_file_explorer_manager_root_path')); ?>
      <?php echo render_input('max_scan_files', 'max_scan_files', get_option('crm_file_explorer_manager_max_scan_files'), 'number'); ?>
    </div>
    <div class="sc-settings-section">
      <h5><i class="fa fa-shield"></i> <?php echo _l('settings_safety_section'); ?></h5>
      <p><?php echo _l('settings_safety_section_help'); ?></p>
      <div class="checkbox checkbox-primary"><input type="checkbox" name="allow_backup_zip" id="allow_backup_zip" value="1" <?php echo get_option('crm_file_explorer_manager_allow_backup_zip')=='1'?'checked':''; ?>><label for="allow_backup_zip"><?php echo _l('allow_backup_zip'); ?></label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" name="public_preview" id="public_preview" value="1" <?php echo get_option('crm_file_explorer_manager_public_preview')=='1'?'checked':''; ?>><label for="public_preview"><?php echo _l('public_preview'); ?></label></div>
    </div>
    <div class="sc-settings-actions">
      <button type="submit" class="btn sc-btn sc-btn-sm"><i class="fa fa-save"></i> <?php echo _l('save'); ?></button>
      <a class="btn sc-btn-dark sc-btn-sm" href="<?php echo admin_url('crm_file_explorer_manager/health'); ?>"><i class="fa fa-heartbeat"></i> <?php echo _l('open_health_checker'); ?></a>
      <a class="btn sc-btn-green sc-btn-sm" href="<?php echo admin_url('crm_file_explorer_manager/help'); ?>"><i class="fa fa-question-circle"></i> <?php echo _l('open_help_guide'); ?></a>
    </div>
  <?php echo form_close(); ?>
  <hr>
  <p><strong><?php echo _l('author'); ?>:</strong> <?php echo html_escape(get_option('crm_file_explorer_manager_author')); ?> | <strong><?php echo _l('version'); ?>:</strong> <?php echo html_escape(CRM_FILE_EXPLORER_MANAGER_VERSION); ?></p>
</div>
<link rel="stylesheet" href="<?php echo module_dir_url('crm_file_explorer_manager', 'assets/css/crm_file_explorer_manager.css'); ?>">
