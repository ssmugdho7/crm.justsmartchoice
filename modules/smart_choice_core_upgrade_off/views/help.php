<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-choice-core-page"><div class="row"><div class="col-md-12">

<div class="panel_s"><div class="panel-body"><h3>Smart Choice Core Help Guide</h3><p>This upgrade package works as a safe core overlay. It does not edit protected licensing files. It writes CRM options, registers menus, adds reports, copies bundled module folders, adds permissions, and applies Smart Choice branding.</p><ol><li>Upload this folder to <strong>modules/smart_choice_core_upgrade</strong>.</li><li>Activate it in Setup > Modules.</li><li>Open Smart Choice Core > Health Check.</li><li>Click Apply Defaults.</li><li>Activate any copied embedded modules that are not yet active.</li></ol><div class="alert alert-info">Perfex displays its native database migration version from the migrations table and migration config. Smart Choice version 3.1.2 is displayed by this enterprise core layer and does not remove the original Perfex license system.</div></div></div>
</div></div></div></div><?php init_tail(); ?>
