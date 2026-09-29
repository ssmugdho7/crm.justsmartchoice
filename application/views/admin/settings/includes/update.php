<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$installedVersion = get_option('smart_choice_crm_build') ?: (defined('APP_VERSION') ? APP_VERSION : '3.6.3 SC');
$databaseVersion  = isset($current_version) ? (string) $current_version : '363';
?>
<div class="panel_s sc-update-panel">
  <div class="panel-body">
    <h4 class="no-margin"><i class="fa fa-cloud-arrow-up"></i> Smart Choice CRM System Update</h4>
    <hr />
    <div class="alert alert-info">Core updates are governed by Smart Choice Contractors USA. Back up the database and CRM files before installing any core replacement package.</div>
    <div class="table-responsive">
      <table class="table table-bordered sc-update-table">
        <tbody>
          <tr><td class="bold" style="width:220px">CRM Product</td><td>Smart Choice CRM</td></tr>
          <tr><td class="bold">Creator / Maintainer</td><td>Smart Choice Contractors USA</td></tr>
          <tr><td class="bold">Installed Version</td><td><span class="label label-success"><?= e($installedVersion); ?></span></td></tr>
          <tr><td class="bold">Database Version</td><td><span class="label label-info"><?= e($databaseVersion); ?></span></td></tr>
          <tr><td class="bold">Update Method</td><td class="tw-whitespace-normal tw-break-words">Use the Modules portal for governed module uploads and database migrations. Core replacement packages must be backed up before installation.</td></tr>
        </tbody>
      </table>
    </div>
    <a href="<?= admin_url('modules'); ?>" class="btn btn-primary"><i class="fa fa-cubes"></i> Module Update</a>
    <a href="<?= admin_url('settings/clear_cache'); ?>" class="btn btn-default"><i class="fa fa-broom"></i> Clean CRM Cache</a>
  </div>
</div>
<style>.sc-update-table{table-layout:fixed;width:100%}.sc-update-table td{white-space:normal!important;overflow-wrap:anywhere;vertical-align:top}@media(max-width:767px){.sc-update-table td:first-child{width:38%!important}.sc-update-panel .btn{display:block;width:100%;margin:8px 0 0}}</style>
