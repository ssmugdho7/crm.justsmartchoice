<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<div class="panel_s sc-sammy-panel"><div class="panel-body">
  <div class="sc-sammy-header">
    <div><h4 class="no-margin">Sammy AI Workflow Engine</h4><p class="text-muted mtop5">Create, test, enable, disable, duplicate, and monitor AI workflows across estimates, packages, purchasing, scheduling, communications, and project handoff.</p></div>
    <div class="sc-sammy-toolbar">
      <a href="<?php echo admin_url('usi_smartchoice_seo'); ?>" class="btn btn-default btn-sm">Dashboard</a>
      <a href="<?php echo admin_url('usi_smartchoice_seo/workflow'); ?>" class="btn btn-success btn-sm">New Workflow</a>
      <a href="<?php echo admin_url('usi_smartchoice_seo/workflow_engine'); ?>" class="btn btn-default btn-sm">Reload</a>
    </div>
  </div>
  <hr class="hr-panel-heading" />
  <form method="get" action="<?php echo admin_url('usi_smartchoice_seo/workflow_engine'); ?>" class="sc-filter-row">
    <select name="is_active" class="form-control input-sm"><option value="">All Statuses</option><option value="1" <?php echo isset($filters['is_active']) && $filters['is_active']==='1' ? 'selected' : ''; ?>>Enabled</option><option value="0" <?php echo isset($filters['is_active']) && $filters['is_active']==='0' ? 'selected' : ''; ?>>Disabled</option></select>
    <select name="trigger_area" class="form-control input-sm"><option value="">All Trigger Areas</option><?php foreach (['manual'=>'Manual','ai_estimates'=>'AI Estimates','customer_packages'=>'Customer Packages','material_takeoff'=>'Material Takeoff','ai_purchasing'=>'AI Purchasing','ai_scheduling'=>'AI Scheduling','closeout_warranty'=>'Closeout & Warranty'] as $value=>$label) { ?><option value="<?php echo html_escape($value); ?>" <?php echo isset($filters['trigger_area']) && $filters['trigger_area']===$value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option><?php } ?></select>
    <input type="text" name="workflow_type" value="<?php echo html_escape($filters['workflow_type'] ?? ''); ?>" class="form-control input-sm" placeholder="Workflow type">
    <button class="btn btn-primary btn-sm" type="submit">Filter</button>
  </form>

  <?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_workflows')); ?>
  <div class="table-responsive mtop20"><table class="table table-bordered table-striped sc-compact-table">
    <thead><tr><th width="30"><input type="checkbox" onclick="$('.sc-workflow-check').prop('checked', this.checked);"></th><th>Workflow</th><th>Type</th><th>Trigger</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if (empty($workflows)) { ?><tr><td colspan="7" class="text-center text-muted">No workflows found.</td></tr><?php } ?>
      <?php foreach ($workflows as $workflow) { ?>
      <tr>
        <td><input type="checkbox" class="sc-workflow-check" name="ids[]" value="<?php echo (int)$workflow['id']; ?>"></td>
        <td><strong><?php echo html_escape($workflow['workflow_name']); ?></strong><br><span class="text-muted"><?php echo html_escape($workflow['description']); ?></span></td>
        <td><?php echo html_escape(ucwords(str_replace('_',' ', $workflow['workflow_type']))); ?></td>
        <td><?php echo html_escape(ucwords(str_replace('_',' ', $workflow['trigger_area']))); ?><br><span class="text-muted"><?php echo html_escape($workflow['trigger_event']); ?></span></td>
        <td><?php echo (int)$workflow['is_active'] === 1 ? '<span class="label label-success">Enabled</span>' : '<span class="label label-default">Disabled</span>'; ?></td>
        <td><?php echo html_escape($workflow['updated_at']); ?></td>
        <td>
          <a href="<?php echo admin_url('usi_smartchoice_seo/view_workflow/' . (int)$workflow['id']); ?>" class="btn btn-default btn-xs">View</a>
          <a href="<?php echo admin_url('usi_smartchoice_seo/workflow/' . (int)$workflow['id']); ?>" class="btn btn-info btn-xs">Edit</a>
          <a href="<?php echo admin_url('usi_smartchoice_seo/run_workflow/' . (int)$workflow['id']); ?>" class="btn btn-success btn-xs">Test</a>
          <?php if ((int)$workflow['is_active'] === 1) { ?><a href="<?php echo admin_url('usi_smartchoice_seo/disable_workflow/' . (int)$workflow['id']); ?>" class="btn btn-warning btn-xs">Disable</a><?php } else { ?><a href="<?php echo admin_url('usi_smartchoice_seo/enable_workflow/' . (int)$workflow['id']); ?>" class="btn btn-success btn-xs">Enable</a><?php } ?>
          <a href="<?php echo admin_url('usi_smartchoice_seo/duplicate_workflow/' . (int)$workflow['id']); ?>" class="btn btn-default btn-xs">Duplicate</a>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table></div>
  <button type="submit" class="btn btn-danger btn-sm">Mass Delete</button>
  <?php echo form_close(); ?>

  <h5 class="mtop30">Recent Workflow Runs</h5>
  <div class="table-responsive"><table class="table table-bordered table-striped sc-compact-table"><thead><tr><th>ID</th><th>Workflow</th><th>Source</th><th>Status</th><th>Started</th><th>Result</th></tr></thead><tbody>
  <?php if (empty($runs)) { ?><tr><td colspan="6" class="text-center text-muted">No workflow runs found.</td></tr><?php } ?>
  <?php foreach ($runs as $run) { ?><tr><td><?php echo (int)$run['id']; ?></td><td><?php echo (int)$run['workflow_id']; ?></td><td><?php echo html_escape($run['source_area']); ?> #<?php echo (int)$run['source_id']; ?></td><td><?php echo html_escape($run['run_status']); ?></td><td><?php echo html_escape($run['started_at']); ?></td><td><?php echo html_escape($run['result_message']); ?></td></tr><?php } ?>
  </tbody></table></div>
</div></div>
</div></div></div></div>
<?php init_tail(); ?></body></html>
