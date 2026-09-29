<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper" class="debug-mode-page"><div class="content"><div class="panel_s debug-mode-card"><div class="panel-body">
    <div class="debug-mode-hero debug-mode-hero-compact"><div><h1><i class="fa fa-users"></i> Staff Import / Export</h1><p>Safe employee import/export guide. Export is active; import should be tested first on staging.</p></div><a href="<?php echo admin_url('debug_mode'); ?>" class="btn btn-default btn-xs">Back</a></div>
    <a class="btn btn-success btn-sm mtop15" href="<?php echo admin_url('debug_mode/export_staff'); ?>"><i class="fa fa-download"></i> Export Staff CSV</a>
    <div class="alert alert-warning mtop20"><strong>Import warning:</strong> Staff records include passwords, roles, departments, permissions, and login status. Do not mass import directly into production until a database backup is made and the CSV columns are validated.</div>
    <h4>Recommended Safe Import Process</h4><ol><li>Export current staff first.</li><li>Backup the database.</li><li>Use the exported CSV headers as the sample format.</li><li>Import only into a staging copy first.</li><li>Verify staff roles, permissions, email uniqueness, and active status.</li></ol>
</div></div></div></div><?php init_tail(); ?>
