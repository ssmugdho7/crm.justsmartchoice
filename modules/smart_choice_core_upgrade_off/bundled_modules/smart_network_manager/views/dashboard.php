<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content snm-wrap">
    <div class="snm-hero">
      <div>
        <h1><i class="fa fa-network-wired"></i> Smart Network Manager</h1>
        <p>Monitor your local network, review devices, manage schedules, and connect Perfex CRM to a secure Windows Server agent.</p>
      </div>
      <div class="snm-actions">
        <?php echo form_open(admin_url('smart_network_manager/scan'), ['class'=>'snm-inline-form']); ?><button type="submit" class="btn snm-btn"><i class="fa fa-sync"></i> Scan / Refresh</button><?php echo form_close(); ?>
        <a href="<?php echo admin_url('smart_network_manager/health'); ?>" class="btn snm-btn-blue"><i class="fa fa-heartbeat"></i> Health Checker</a>
        <a href="<?php echo admin_url('smart_network_manager/help'); ?>" class="btn snm-btn-light"><i class="fa fa-question-circle"></i> Help Guide</a>
        <a href="<?php echo html_escape(get_option('smart_network_manager_phpmyadmin_url')); ?>" target="_blank" rel="noopener" class="btn snm-btn-light"><i class="fa fa-database"></i> phpMyAdmin</a>
      </div>
    </div>

    <div class="row snm-cards">
      <div class="col-md-3"><div class="snm-card"><span>Total Devices</span><strong><?php echo count($devices); ?></strong></div></div>
      <div class="col-md-3"><div class="snm-card"><span>Blocked Devices</span><strong><?php echo count(array_filter($devices, function($d){return !empty($d['is_blocked']);})); ?></strong></div></div>
      <div class="col-md-3"><div class="snm-card"><span>Schedules</span><strong><?php echo count($schedules); ?></strong></div></div>
      <div class="col-md-3"><div class="snm-card"><span>Mode</span><strong><?php echo get_option('smart_network_manager_enable_controls') === '1' ? 'Control' : 'Read Only'; ?></strong></div></div>
    </div>

    <div class="panel_s snm-panel">
      <div class="panel-heading snm-panel-heading">Health Snapshot</div>
      <div class="panel-body"><div class="row">
        <?php foreach($health as $item){ ?><div class="col-md-4"><div class="snm-health"><strong><?php echo html_escape($item['name']); ?></strong><span><?php echo html_escape($item['status']); ?></span></div></div><?php } ?>
      </div></div>
    </div>

    <div class="panel_s snm-panel">
      <div class="panel-heading snm-panel-heading">Devices</div>
      <div class="panel-body table-responsive">
        <table class="table table-striped snm-table">
          <thead><tr><th>Owner</th><th>Device</th><th>Type</th><th>IP</th><th>MAC</th><th>Group</th><th>Status</th><th>Last Seen</th><th>Actions</th></tr></thead>
          <tbody>
          <?php if(empty($devices)){ ?><tr><td colspan="9" class="text-center">No devices found yet. Configure the Windows agent, then click Scan / Refresh.</td></tr><?php } ?>
          <?php foreach($devices as $d){ ?>
            <tr>
              <td><?php echo html_escape($d['owner_name']); ?></td>
              <td><strong><?php echo html_escape($d['device_name']); ?></strong><br><small><?php echo html_escape($d['manufacturer']); ?></small></td>
              <td><?php echo html_escape($d['device_type']); ?></td>
              <td><?php echo html_escape($d['ip_address']); ?></td>
              <td><?php echo html_escape($d['mac_address']); ?></td>
              <td><?php echo html_escape($d['group_name']); ?></td>
              <td><span class="label label-<?php echo $d['status']==='online'?'success':'default'; ?>"><?php echo html_escape($d['status']); ?></span><?php if(!empty($d['is_blocked'])){ ?> <span class="label label-danger">Blocked</span><?php } ?></td>
              <td><?php echo html_escape($d['last_seen']); ?></td>
              <td class="snm-action-buttons">
                <?php echo form_open(admin_url('smart_network_manager/device_action/'.$d['id'].'/allow'), ['class'=>'snm-inline-form']); ?><button class="btn btn-xs btn-default" type="submit">Allow</button><?php echo form_close(); ?>
                <?php echo form_open(admin_url('smart_network_manager/device_action/'.$d['id'].'/block'), ['class'=>'snm-inline-form']); ?><button class="btn btn-xs btn-warning _delete" type="submit">Block</button><?php echo form_close(); ?>
                <?php echo form_open(admin_url('smart_network_manager/device_action/'.$d['id'].'/wake'), ['class'=>'snm-inline-form']); ?><button class="btn btn-xs btn-info" type="submit">Wake</button><?php echo form_close(); ?>
              </td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6"><div class="panel_s snm-panel"><div class="panel-heading snm-panel-heading">Safe Tools</div><div class="panel-body">
        <p>These actions avoid deleting business data. They use POST requests and log activity.</p>
        <?php echo form_open(admin_url('smart_network_manager/clear_cache'), ['class'=>'snm-inline-form']); ?><button type="submit" class="btn snm-btn _delete">Clear Cache Safely</button><?php echo form_close(); ?>
        <?php echo form_open(admin_url('smart_network_manager/toggle_client_portal/enable'), ['class'=>'snm-inline-form']); ?><button type="submit" class="btn snm-btn-blue">Enable Client Portal Registration</button><?php echo form_close(); ?>
        <?php echo form_open(admin_url('smart_network_manager/toggle_client_portal/disable'), ['class'=>'snm-inline-form']); ?><button type="submit" class="btn btn-default">Disable Client Portal Registration</button><?php echo form_close(); ?>
      </div></div></div>
      <div class="col-md-6"><div class="panel_s snm-panel"><div class="panel-heading snm-panel-heading">Help Guide</div><div class="panel-body">
        <ul class="snm-help-list"><li><strong>Scan / Refresh:</strong> asks the Windows Server agent to scan local devices.</li><li><strong>Read Only Mode:</strong> shows devices but does not block anyone.</li><li><strong>Control Mode:</strong> allows block/allow when router support exists.</li><li><strong>Cache Cleanup:</strong> removes cache files but preserves index.html.</li></ul>
      </div></div></div>
    </div>

    <div class="panel_s snm-panel"><div class="panel-heading snm-panel-heading">Recent Logs</div><div class="panel-body table-responsive"><table class="table table-condensed"><thead><tr><th>Date</th><th>Level</th><th>Type</th><th>Message</th></tr></thead><tbody><?php foreach($logs as $log){ ?><tr><td><?php echo html_escape($log['datecreated']); ?></td><td><?php echo html_escape($log['level']); ?></td><td><?php echo html_escape($log['event_type']); ?></td><td><?php echo html_escape($log['message']); ?></td></tr><?php } ?></tbody></table></div></div>
  </div>
</div>
<?php init_tail(); ?>
