<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?php echo module_dir_url('crm_file_explorer_manager', 'assets/css/crm_file_explorer_manager.css'); ?>">
<div id="wrapper" class="crm-file-explorer">
  <div class="content">
    <?php $this->load->view('crm_file_explorer_manager/includes/top_nav'); ?>
    <div class="row"><div class="col-md-12">
      <div class="sc-card">
        <h3 class="sc-title"><i class="fa fa-archive"></i> <?php echo _l('safe_backups'); ?></h3>
        <div class="sc-warning"><?php echo _l('safe_backup_explanation'); ?></div>
        <?php echo form_open(admin_url('crm_file_explorer_manager/create_backup'), ['class' => 'form-inline']); ?>
          <select name="backup_type" class="form-control">
            <option value="module_files"><?php echo _l('module_files'); ?></option>
            <option value="modules_only"><?php echo _l('modules_only'); ?></option>
            <option value="crm_without_uploads"><?php echo _l('crm_without_uploads'); ?></option>
          </select>
          <button class="btn sc-btn" type="submit"><i class="fa fa-file-archive-o"></i> <?php echo _l('create_backup'); ?></button>
        <?php echo form_close(); ?>
      </div>
      <div class="sc-card">
        <table class="table table-striped sc-table">
          <thead><tr><th><?php echo _l('backup_type'); ?></th><th><?php echo _l('file_name'); ?></th><th><?php echo _l('file_size'); ?></th><th><?php echo _l('created_time'); ?></th><th><?php echo _l('options'); ?></th></tr></thead>
          <tbody>
          <?php foreach ($backups as $b) { ?>
            <tr>
              <td><?php echo html_escape(ucwords(str_replace('_', ' ', $b['backup_type']))); ?></td>
              <td><?php echo html_escape($b['backup_name']); ?></td>
              <td><?php echo number_format($b['file_size'] / 1024 / 1024, 2); ?> MB</td>
              <td><?php echo html_escape($b['created_at']); ?></td>
              <td class="tw-space-x-1">
                <a class="btn btn-info btn-xs" href="<?php echo admin_url('crm_file_explorer_manager/download_backup/' . $b['id']); ?>"><i class="fa fa-download"></i> <?php echo _l('download'); ?></a>
                <?php if (has_permission(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, '', 'delete') || is_admin()) { ?>
                  <?php echo form_open(admin_url('crm_file_explorer_manager/delete_backup/' . $b['id']), ['style' => 'display:inline-block;', 'onsubmit' => "return confirm('" . html_escape(_l('confirm_delete_backup')) . "');"]); ?>
                    <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></button>
                  <?php echo form_close(); ?>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
          <?php if (empty($backups)) { ?><tr><td colspan="5" class="text-center"><?php echo _l('no_backups_found'); ?></td></tr><?php } ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
</div>
<?php init_tail(); ?>
</body></html>
