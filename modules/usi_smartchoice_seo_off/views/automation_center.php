<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s sc-sammy-panel">
          <div class="panel-body">
            <div class="sc-sammy-header">
              <div>
                <h4 class="no-margin">Sammy AI Alerts & Automation Center</h4>
                <p class="text-muted mtop5">Monitor follow-ups, project risk, schedule readiness, purchasing actions, and AI automation work queue.</p>
              </div>
              <div class="sc-sammy-toolbar">
                <a href="<?php echo admin_url('usi_smartchoice_seo'); ?>" class="btn btn-default btn-sm">Dashboard</a>
                <a href="<?php echo admin_url('usi_smartchoice_seo/build_default_alerts'); ?>" class="btn btn-info btn-sm">Build Default Alerts</a>
                <a href="<?php echo admin_url('usi_smartchoice_seo/automation_center'); ?>" class="btn btn-default btn-sm">Reload</a>
              </div>
            </div>

            <hr class="hr-panel-heading" />

            <form method="get" action="<?php echo admin_url('usi_smartchoice_seo/automation_center'); ?>" class="sc-filter-row">
              <select name="status" class="form-control input-sm">
                <option value="">All Statuses</option>
                <?php foreach (['open' => 'Open', 'completed' => 'Completed'] as $value => $label) { ?>
                  <option value="<?php echo html_escape($value); ?>" <?php echo isset($filters['status']) && $filters['status'] === $value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                <?php } ?>
              </select>
              <select name="priority" class="form-control input-sm">
                <option value="">All Priorities</option>
                <?php foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $value => $label) { ?>
                  <option value="<?php echo html_escape($value); ?>" <?php echo isset($filters['priority']) && $filters['priority'] === $value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                <?php } ?>
              </select>
              <select name="source_area" class="form-control input-sm">
                <option value="">All Areas</option>
                <?php foreach (['ai_estimates' => 'AI Estimates', 'ai_purchasing' => 'AI Purchasing', 'ai_scheduling' => 'AI Scheduling', 'ai_closeout' => 'Closeout & Warranty', 'business_intelligence' => 'Business Intelligence'] as $value => $label) { ?>
                  <option value="<?php echo html_escape($value); ?>" <?php echo isset($filters['source_area']) && $filters['source_area'] === $value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                <?php } ?>
              </select>
              <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </form>

            <div class="row mtop20">
              <div class="col-md-4">
                <div class="panel_s">
                  <div class="panel-body">
                    <h5>Create Manual Alert</h5>
                    <?php echo form_open(admin_url('usi_smartchoice_seo/create_automation_alert')); ?>
                    <div class="form-group"><label>Alert Title</label><input type="text" name="alert_title" class="form-control" required></div>
                    <div class="form-group"><label>Alert Type</label><input type="text" name="alert_type" class="form-control" value="manual_follow_up"></div>
                    <div class="form-group"><label>Source Area</label><input type="text" name="source_area" class="form-control" value="sammy_ai"></div>
                    <div class="form-group"><label>Priority</label><select name="priority" class="form-control"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="critical">Critical</option></select></div>
                    <div class="form-group"><label>Due Date</label><input type="date" name="due_date" class="form-control"></div>
                    <div class="form-group"><label>Message</label><textarea name="message" class="form-control" rows="3"></textarea></div>
                    <div class="form-group"><label>Recommended Action</label><textarea name="recommended_action" class="form-control" rows="3"></textarea></div>
                    <button type="submit" class="btn btn-success btn-sm">Create Alert</button>
                    <?php echo form_close(); ?>
                  </div>
                </div>
              </div>
              <div class="col-md-8">
                <?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_automation_alerts')); ?>
                <div class="table-responsive">
                  <table class="table table-bordered table-striped sc-compact-table">
                    <thead>
                      <tr>
                        <th width="30"><input type="checkbox" onclick="$('.sc-alert-check').prop('checked', this.checked);"></th>
                        <th>Title</th>
                        <th>Area</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($alerts)) { ?>
                        <tr><td colspan="7" class="text-center text-muted">No alerts found.</td></tr>
                      <?php } ?>
                      <?php foreach ($alerts as $alert) { ?>
                        <tr>
                          <td><input type="checkbox" class="sc-alert-check" name="ids[]" value="<?php echo (int) $alert['id']; ?>"></td>
                          <td><strong><?php echo html_escape($alert['alert_title']); ?></strong><br><span class="text-muted"><?php echo html_escape($alert['message']); ?></span></td>
                          <td><?php echo html_escape(ucwords(str_replace('_', ' ', $alert['source_area']))); ?></td>
                          <td><?php echo html_escape(ucfirst($alert['priority'])); ?></td>
                          <td><?php echo html_escape(ucfirst($alert['status'])); ?></td>
                          <td><?php echo html_escape($alert['due_date']); ?></td>
                          <td>
                            <?php if ($alert['status'] !== 'completed') { ?><a href="<?php echo admin_url('usi_smartchoice_seo/complete_automation_alert/' . (int) $alert['id']); ?>" class="btn btn-success btn-xs">Complete</a><?php } ?>
                            <a href="<?php echo admin_url('usi_smartchoice_seo/delete_automation_alert/' . (int) $alert['id']); ?>" class="btn btn-danger btn-xs _delete">Delete</a>
                          </td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
                <button type="submit" class="btn btn-danger btn-sm">Mass Delete</button>
                <?php echo form_close(); ?>
              </div>
            </div>

            <h5 class="mtop30">Automation Queue</h5>
            <div class="table-responsive">
              <table class="table table-bordered table-striped sc-compact-table">
                <thead><tr><th>Name</th><th>Type</th><th>Area</th><th>Status</th><th>Run After</th><th>Created</th></tr></thead>
                <tbody>
                  <?php if (empty($queue)) { ?><tr><td colspan="6" class="text-center text-muted">No queued automations found.</td></tr><?php } ?>
                  <?php foreach ($queue as $item) { ?>
                    <tr><td><?php echo html_escape($item['automation_name']); ?></td><td><?php echo html_escape($item['automation_type']); ?></td><td><?php echo html_escape($item['source_area']); ?></td><td><?php echo html_escape($item['status']); ?></td><td><?php echo html_escape($item['run_after']); ?></td><td><?php echo html_escape($item['created_at']); ?></td></tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
</body>
</html>
