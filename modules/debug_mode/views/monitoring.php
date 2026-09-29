<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="debug-mode-page">
  <div class="debug-mode-hero debug-mode-hero-compact"><div><h1><i class="fa fa-line-chart"></i> Site Monitoring And CRM Access</h1><p>Monitor websites and configure time-based CRM IP access rules. Router-level controls must still be configured in the WOW router.</p></div><a class="btn btn-default btn-sm" href="<?php echo admin_url('debug_mode'); ?>">Back</a></div>
  <div class="debug-mode-bottom-nav debug-mode-top-nav">
    <a href="<?php echo admin_url('debug_mode'); ?>">Utilities</a><a href="<?php echo admin_url('debug_mode/health'); ?>">Health</a><a href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Errors</a><a href="<?php echo admin_url('debug_mode/file_structure'); ?>">Structure</a><a href="<?php echo admin_url('debug_mode/network_tools'); ?>">Network</a><a class="active" href="<?php echo admin_url('debug_mode/monitoring'); ?>">Monitoring</a>
  </div>

  <div class="row">
    <div class="col-md-5"><div class="panel_s debug-mode-card"><div class="panel-body">
      <h3>Add Site Monitor</h3>
      <?php echo form_open(admin_url('debug_mode/save_monitor')); ?>
      <?php echo render_input('name','Monitor Name'); ?>
      <?php echo render_input('url','Website Or Endpoint URL','','url'); ?>
      <?php echo render_input('expected_code','Expected HTTP Code','200','number'); ?>
      <button class="btn btn-primary btn-sm" type="submit">Add Monitor</button>
      <?php echo form_close(); ?>
    </div></div></div>
    <div class="col-md-7"><div class="panel_s debug-mode-card"><div class="panel-body">
      <div class="debug-mode-inline-actions"><h3 class="tw-flex-1">Monitored Sites</h3><a class="btn btn-success btn-sm" href="<?php echo admin_url('debug_mode/run_monitor_checks'); ?>"><i class="fa fa-refresh"></i> Run Checks</a></div>
      <div class="debug-mode-table-wrap"><table class="table table-striped table-condensed"><thead><tr><th>Name</th><th>URL</th><th>Status</th><th>Code</th><th>Latency</th><th>Last Check</th><th>Action</th></tr></thead><tbody>
      <?php if (empty($monitors)) { ?><tr><td colspan="7">No monitors configured.</td></tr><?php } ?>
      <?php foreach ($monitors as $row) { ?><tr><td><?php echo html_escape($row['name']); ?></td><td><a target="_blank" rel="noopener" href="<?php echo html_escape($row['url']); ?>"><?php echo html_escape($row['url']); ?></a></td><td><span class="debug-mode-monitor-status <?php echo html_escape($row['last_status']); ?>"><?php echo html_escape(ucfirst((string)$row['last_status'])); ?></span></td><td><?php echo (int)$row['last_code']; ?></td><td><?php echo html_escape($row['last_latency_ms']); ?> ms</td><td><?php echo html_escape($row['last_checked_at']); ?></td><td><a class="btn btn-danger btn-xs" onclick="return confirm('Delete this monitor?');" href="<?php echo admin_url('debug_mode/delete_monitor/'.$row['id']); ?>">Delete</a></td></tr><?php } ?>
      </tbody></table></div>
    </div></div></div>
  </div>

  <div class="panel_s debug-mode-card"><div class="panel-body">
    <h3>CRM IP Access Schedule</h3><p class="text-muted">These rules document and prepare access control for the CRM application only. They do not control every device connected to your WOW router.</p>
    <div class="row"><div class="col-md-5">
      <?php echo form_open(admin_url('debug_mode/save_ip_rule')); ?>
      <?php echo render_input('label','Device Or Person Name'); ?>
      <?php echo render_input('ip_address','IP Address','','text',['placeholder'=>'192.168.1.25']); ?>
      <div class="row"><div class="col-md-6"><?php echo render_input('access_from','Allowed From','','time'); ?></div><div class="col-md-6"><?php echo render_input('access_until','Allowed Until','','time'); ?></div></div>
      <div class="form-group"><label>Allowed Days</label><div class="debug-mode-inline-actions"><?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i=>$day) { ?><label class="checkbox-inline"><input type="checkbox" name="days[]" value="<?php echo $i; ?>"> <?php echo $day; ?></label><?php } ?></div></div>
      <button class="btn btn-primary btn-sm" type="submit">Save Rule</button><?php echo form_close(); ?>
    </div><div class="col-md-7"><div class="debug-mode-table-wrap"><table class="table table-striped table-condensed"><thead><tr><th>Name</th><th>IP</th><th>From</th><th>Until</th><th>Days</th><th>Action</th></tr></thead><tbody>
      <?php if (empty($ip_rules)) { ?><tr><td colspan="6">No CRM IP rules configured.</td></tr><?php } ?>
      <?php foreach ($ip_rules as $row) { ?><tr><td><?php echo html_escape($row['label']); ?></td><td><code><?php echo html_escape($row['ip_address']); ?></code></td><td><?php echo html_escape($row['access_from']); ?></td><td><?php echo html_escape($row['access_until']); ?></td><td><?php echo html_escape($row['days']); ?></td><td><a class="btn btn-danger btn-xs" onclick="return confirm('Delete this rule?');" href="<?php echo admin_url('debug_mode/delete_ip_rule/'.$row['id']); ?>">Delete</a></td></tr><?php } ?>
    </tbody></table></div></div></div>
  </div></div>
</div></div></div>
<?php init_tail(); ?>
