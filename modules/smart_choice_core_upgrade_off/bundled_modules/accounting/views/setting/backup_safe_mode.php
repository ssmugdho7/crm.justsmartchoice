<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row smart-choice-compact-page accounting-hub-backup-page">
  <div class="col-md-12">
    <div class="alert alert-info">
      <strong><?php echo _l('backup_safe_mode_database_check'); ?></strong><br>
      <?php echo _l('backup_safe_mode_database_check_note'); ?>
    </div>
  </div>
  <div class="col-md-4">
    <div class="panel_s"><div class="panel-body">
      <h5 class="bold"><?php echo _l('safe_mode'); ?></h5>
      <p><?php echo _l('current_status'); ?>: <span class="label label-<?php echo !empty($safe_mode_enabled) ? 'warning' : 'success'; ?>"><?php echo !empty($safe_mode_enabled) ? _l('enabled') : _l('disabled'); ?></span></p>
      <p class="text-muted"><?php echo _l('safe_mode_note'); ?></p>
      <a href="<?php echo admin_url('accounting/backup_safe_mode_action/safe_mode_on'); ?>" class="btn btn-warning btn-xs"><i class="fa fa-shield"></i> <?php echo _l('enable_safe_mode'); ?></a>
      <a href="<?php echo admin_url('accounting/backup_safe_mode_action/safe_mode_off'); ?>" class="btn btn-success btn-xs"><i class="fa fa-check"></i> <?php echo _l('disable_safe_mode'); ?></a>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="panel_s"><div class="panel-body">
      <h5 class="bold"><?php echo _l('data_backup'); ?></h5>
      <p><?php echo _l('backup_folder_path'); ?>:<br><code><?php echo html_escape($backup_status['path'] ?? ''); ?></code></p>
      <p><?php echo _l('backup_exists'); ?>: <span class="label label-<?php echo !empty($backup_status['exists']) ? 'success' : 'default'; ?>"><?php echo !empty($backup_status['exists']) ? _l('yes') : _l('no'); ?></span></p>
      <p class="text-muted"><?php echo !empty($backup_status['last_file']) ? html_escape($backup_status['last_file']) : _l('no_backup_file_found'); ?></p>
      <a href="<?php echo admin_url('accounting/backup_safe_mode_action/create_backup'); ?>" class="btn btn-info btn-xs"><i class="fa fa-download"></i> <?php echo _l('create_backup'); ?></a>
      <a href="<?php echo admin_url('accounting/backup_safe_mode_action/check_backup'); ?>" class="btn btn-default btn-xs"><i class="fa fa-search"></i> <?php echo _l('check_backup'); ?></a>
      <a href="<?php echo admin_url('accounting/backup_safe_mode_action/restore_backup'); ?>" class="btn btn-warning btn-xs _delete"><i class="fa fa-undo"></i> <?php echo _l('restore_backup'); ?></a>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="panel_s"><div class="panel-body">
      <h5 class="bold"><?php echo _l('database_check'); ?></h5>
      <p><?php echo _l('current_status'); ?>: <span class="label label-<?php echo !empty($database_status['ok']) ? 'success' : 'warning'; ?>"><?php echo !empty($database_status['ok']) ? _l('passed') : _l('needs_attention'); ?></span></p>
      <p class="text-muted"><?php echo html_escape($database_status['message'] ?? ''); ?></p>
      <a href="<?php echo admin_url('accounting/backup_safe_mode_action/database_check'); ?>" class="btn btn-info btn-xs"><i class="fa fa-database"></i> <?php echo _l('run_database_check'); ?></a>
      <a href="<?php echo current_full_url(); ?>" class="btn btn-default btn-xs"><i class="fa fa-refresh"></i> <?php echo _l('refresh'); ?></a>
    </div></div>
  </div>
</div>
