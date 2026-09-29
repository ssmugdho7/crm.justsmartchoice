<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">
        <h4 class="no-margin"><i class="fa fa-heartbeat"></i> <?php echo html_escape($title); ?></h4>
        <hr class="hr-panel-heading" />
        <div class="table-responsive">
          <table class="table table-bordered">
            <tbody>
              <tr><th>Company Upload Folder</th><td><code><?php echo html_escape($company_path); ?></code></td></tr>
              <tr><th>Folder Exists</th><td><?php echo $company_exists ? 'Yes' : 'No'; ?></td></tr>
              <tr><th>Folder Writable</th><td><?php echo $company_writable ? 'Yes' : 'No'; ?></td></tr>
              <tr><th>Permissions</th><td><?php echo html_escape($company_permissions); ?></td></tr>
              <tr><th>Notes Table</th><td><?php echo $notes_table ? 'Available' : 'Missing'; ?></td></tr>
              <tr><th>Existing Notes</th><td><?php echo (int) $notes_count; ?></td></tr>
              <tr><th>Sales Payment Metadata</th><td><?php echo $sales_meta_table ? 'Available' : 'Missing'; ?></td></tr>
            </tbody>
          </table>
        </div>
        <h4>Staff Profile Image Diagnostics</h4>
        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead><tr><th>Staff</th><th>Database Value</th><th>Image Files Found</th><th>Folder</th></tr></thead>
            <tbody>
              <?php foreach ($staff as $row) { ?>
              <tr>
                <td><?php echo html_escape(trim($row['firstname'] . ' ' . $row['lastname'])); ?></td>
                <td><?php echo html_escape($row['profile_image'] ?: 'Empty'); ?></td>
                <td><?php echo $row['found'] ? html_escape(implode(', ', $row['found'])) : 'None'; ?></td>
                <td><code><?php echo html_escape($row['folder']); ?></code></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
