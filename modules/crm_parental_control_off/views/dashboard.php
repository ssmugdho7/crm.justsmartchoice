<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><i class="fa fa-shield-alt"></i> <?php echo html_escape(_l('crm_parental_control') === 'crm_parental_control' ? 'CRM Parental Control' : _l('crm_parental_control')); ?></h4>
            <p class="text-muted mtop10">Smart Choice administration control center for departments, role templates, permission cleanup, backups, health checks, and rollback.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h5>Departments</h5><h2><?php echo count($departments); ?></h2></div></div></div>
      <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h5>Role Templates</h5><h2><?php echo count($roles); ?></h2></div></div></div>
      <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h5>Staff Records</h5><h2><?php echo (int)$preview['staff_count']; ?></h2></div></div></div>
      <div class="col-md-3"><div class="panel_s"><div class="panel-body"><h5>Delete Permission Rows</h5><h2><?php echo (int)$preview['delete_permission_rows_found']; ?></h2></div></div></div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="panel_s"><div class="panel-body">
          <h4>Health Check</h4>
          <table class="table table-striped"><thead><tr><th>Check</th><th>Status</th></tr></thead><tbody>
          <?php foreach ($health as $check) { ?>
            <tr><td><?php echo html_escape($check['name']); ?></td><td><span class="label label-<?php echo $check['status']==='ok'?'success':'warning'; ?>"><?php echo html_escape($check['status']); ?></span></td></tr>
          <?php } ?>
          </tbody></table>
        </div></div>
      </div>
      <div class="col-md-6">
        <div class="panel_s"><div class="panel-body">
          <h4>Safe Actions</h4>
          <p>Run backups before applying role standards. Backups are safe for shared hosting. Apply creates missing standard roles and removes Delete permissions where this Perfex installation stores them in a supported table or role field.</p>
          <a href="<?php echo admin_url('crm_parental_control/backup_database'); ?>" class="btn btn-info mright5"><i class="fa fa-database"></i> Backup Database</a>
          <a href="<?php echo admin_url('crm_parental_control/backup_crm'); ?>" class="btn btn-info mright5"><i class="fa fa-file-archive"></i> Backup CRM Safe Package</a>
          <a href="<?php echo admin_url('crm_parental_control/preview'); ?>" target="_blank" class="btn btn-default mright5"><i class="fa fa-eye"></i> JSON Preview</a>
          <a href="<?php echo admin_url('crm_parental_control/repair_database'); ?>" class="btn btn-warning mright5"><i class="fa fa-wrench"></i> Repair Database</a>
          <a href="<?php echo admin_url('crm_parental_control/download_module'); ?>" class="btn btn-default"><i class="fa fa-download"></i> Download This Module ZIP</a>
          <hr />
          <?php echo form_open(admin_url('crm_parental_control/apply')); ?>
            <input type="hidden" name="confirm_apply" value="1" />
            <button type="submit" class="btn btn-success" onclick="return confirm('Apply Smart Choice company standard now? A rollback snapshot will be created first.');"><i class="fa fa-check"></i> Apply Company Standard</button>
          <?php echo form_close(); ?>
          <br />
          <?php echo form_open(admin_url('crm_parental_control/rollback')); ?>
            <input type="hidden" name="confirm_rollback" value="1" />
            <button type="submit" class="btn btn-danger" onclick="return confirm('Rollback latest company standard permission change?');"><i class="fa fa-undo"></i> Rollback Latest Apply</button>
          <?php echo form_close(); ?>
        </div></div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6"><div class="panel_s"><div class="panel-body">
        <h4>Departments</h4>
        <table class="table table-bordered"><thead><tr><th>Code</th><th>Name</th><th>Description</th></tr></thead><tbody>
        <?php foreach ($departments as $department) { ?>
          <tr><td><?php echo html_escape($department['code']); ?></td><td><?php echo html_escape($department['name']); ?></td><td><?php echo html_escape($department['description']); ?></td></tr>
        <?php } ?>
        </tbody></table>
      </div></div></div>
      <div class="col-md-6"><div class="panel_s"><div class="panel-body">
        <h4>Role Templates</h4>
        <table class="table table-bordered"><thead><tr><th>Role</th><th>Department</th><th>Level</th></tr></thead><tbody>
        <?php foreach ($roles as $role) { ?>
          <tr><td><?php echo html_escape($role['role_name']); ?></td><td><?php echo html_escape($role['department_code']); ?></td><td><?php echo (int)$role['security_level']; ?></td></tr>
        <?php } ?>
        </tbody></table>
      </div></div></div>
    </div>

    <div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
      <h4>Staff Review</h4>
      <table class="table table-striped"><thead><tr><th>Name</th><th>Email</th><th>Role ID</th><th>Admin</th><th>Active</th></tr></thead><tbody>
      <?php foreach ($staff as $s) { ?>
        <tr><td><?php echo html_escape(trim(($s['firstname'] ?? '') . ' ' . ($s['lastname'] ?? ''))); ?></td><td><?php echo html_escape($s['email']); ?></td><td><?php echo html_escape($s['role']); ?></td><td><?php echo !empty($s['admin']) ? 'Yes' : 'No'; ?></td><td><?php echo !empty($s['active']) ? 'Yes' : 'No'; ?></td></tr>
      <?php } ?>
      </tbody></table>
    </div></div></div></div>
  </div>
</div>
<?php init_tail(); ?>
