<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-choice-core-page"><div class="row"><div class="col-md-12">

<div class="panel_s smart-choice-panel">
  <div class="panel-body">
    <div class="smart-choice-titlebar">
      <div>
        <h3>Smart Choice CRM Enterprise Core</h3>
        <p>Version 3.1.2 · Creator Harold Cabrera · Quality ★★★★★ · Time Zone Eastern Time (US & Canada)</p>
      </div>
      <?php echo form_open(admin_url('smart_choice_core_upgrade/apply_defaults')); ?>
      <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Apply Defaults</button>
      <?php echo form_close(); ?>
    </div>
    <div class="row smart-choice-kpis">
      <div class="col-md-3"><div class="sc-kpi"><span>Smart Choice Version</span><strong>3.1.2</strong></div></div>
      <div class="col-md-3"><div class="sc-kpi"><span>Theme</span><strong>Smart Choice</strong></div></div>
      <div class="col-md-3"><div class="sc-kpi"><span>PHP Time Zone</span><strong><?php echo html_escape($version['php_timezone']); ?></strong></div></div>
      <div class="col-md-3"><div class="sc-kpi"><span>Windows Time Zone</span><strong><?php echo html_escape($version['windows_timezone']); ?></strong></div></div>
    </div>
  </div>
</div>
<div class="panel_s"><div class="panel-body"><h4>Embedded Modules</h4><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Module</th><th>Folder</th><th>Status</th><th>Settings</th></tr></thead><tbody><?php foreach ($modules as $module) { ?><tr><td><?php echo html_escape($module['name']); ?></td><td><?php echo html_escape($module['folder']); ?></td><td><?php echo $module['installed'] ? '<span class="label label-success">Installed</span>' : '<span class="label label-danger">Missing</span>'; ?></td><td><?php echo $module['settings'] ? '<span class="label label-info">Detected</span>' : '<span class="label label-default">Centralized</span>'; ?></td></tr><?php } ?></tbody></table></div></div></div>
</div></div></div></div><?php init_tail(); ?>
