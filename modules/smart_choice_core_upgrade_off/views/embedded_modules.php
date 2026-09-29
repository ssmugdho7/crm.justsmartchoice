<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-choice-core-page"><div class="row"><div class="col-md-12">

<div class="panel_s"><div class="panel-body"><h3>Embedded Modules</h3><p>These module folders are bundled with this Smart Choice Core upgrade package. Activate each module from Setup > Modules after this package copies the folders.</p><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Module</th><th>Folder</th><th>Installed</th><th>Main File</th><th>Settings</th></tr></thead><tbody><?php foreach ($modules as $module) { ?><tr><td><?php echo html_escape($module['name']); ?></td><td><?php echo html_escape($module['folder']); ?></td><td><?php echo $module['installed'] ? 'Yes' : 'No'; ?></td><td><?php echo $module['main_file'] ? 'Yes' : 'Check'; ?></td><td><?php echo $module['settings'] ? 'Detected' : 'Managed by Smart Choice Core'; ?></td></tr><?php } ?></tbody></table></div></div></div>
</div></div></div></div><?php init_tail(); ?>
