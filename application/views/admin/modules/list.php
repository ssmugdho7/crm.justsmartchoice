<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.smart-choice-modules-page .module-install-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:14px;box-shadow:0 2px 8px rgba(17,24,39,.04)}
.smart-choice-modules-page .module-install-card h3{margin:0 0 6px;font-weight:700;color:#111827;font-size:18px}
.smart-choice-modules-page .module-install-row{display:flex;gap:8px;align-items:center;max-width:560px}
.smart-choice-modules-page .module-install-row .form-control{height:34px}
.smart-choice-modules-page .module-install-row .btn{height:34px;display:inline-flex;align-items:center;justify-content:center;min-width:90px;padding:5px 12px}
.smart-choice-modules-page .sc-module-filterbar{display:flex;flex-wrap:wrap;gap:6px;margin:10px 0}.smart-choice-modules-page .sc-module-filterbar .btn{height:32px;padding:4px 10px;border-radius:7px}.smart-choice-modules-page .sc-module-tools{display:flex;flex-wrap:wrap;gap:8px;align-items:center;justify-content:space-between;margin-bottom:10px}
.smart-choice-modules-page .sc-module-tools .form-control{max-width:360px;height:34px}
.smart-choice-modules-page .sc-module-tools .btn{height:34px;display:inline-flex;align-items:center;justify-content:center}
.smart-choice-modules-page .table-responsive{overflow-x:hidden!important}
.smart-choice-modules-page table{table-layout:fixed;width:100%!important;margin-bottom:0}
.smart-choice-modules-page table th:first-child,.smart-choice-modules-page table td:first-child{width:34%;max-width:34%;white-space:normal!important;vertical-align:top}
.smart-choice-modules-page table th:nth-child(2),.smart-choice-modules-page table td:nth-child(2){width:66%;max-width:66%;white-space:normal!important;vertical-align:top}
.smart-choice-modules-page .sc-module-name{font-weight:700;color:#111827;margin-bottom:5px;line-height:1.2;font-size:13px}
.smart-choice-modules-page .sc-module-actions{display:flex;flex-wrap:wrap;gap:4px;margin-top:5px}
.smart-choice-modules-page .sc-module-actions a{display:inline-flex;align-items:center;justify-content:center;min-height:24px;min-width:72px;padding:3px 7px;border-radius:6px;background:#f5f5f5;color:#374151;border:1px solid #d1d5db;font-size:11px;line-height:1.2;text-decoration:none;text-align:center}
.smart-choice-modules-page .sc-module-actions a.text-success{background:#e8f7ee;color:#007A3D;border-color:#bfe8cf}
.smart-choice-modules-page .sc-module-actions a.text-danger{background:#fff0ed;color:#b42318;border-color:#ffd0c7}
.smart-choice-modules-page .sc-module-description{font-size:12px;line-height:1.3;color:#374151;margin:0 0 6px;word-break:normal;overflow-wrap:anywhere}
.smart-choice-modules-page .sc-module-meta{font-size:11px;color:#6b7280;line-height:1.25}
.smart-choice-modules-page .label{font-size:10px;padding:3px 6px;border-radius:10px}
.smart-choice-modules-page .panel-body{padding:14px}
.smart-choice-modules-page .alert{margin-bottom:10px}
.smart-choice-modules-page .btn,.smart-choice-modules-page button{display:inline-flex!important;align-items:center!important;justify-content:center!important}
</style>
<div id="wrapper" class="smart-choice-modules-page">
    <div class="content">
<div class="sc-crm-brandbar"><div><strong>Smart Choice CRM</strong> <span class="label label-info">Version 3.5.7</span><div class="text-muted">Created and maintained by Smart Choice Contractors USA</div></div><a href="<?= admin_url('modules/clear_cache'); ?>" class="btn btn-default btn-sm" onclick="return confirm('Clean the CRM cache now?');"><i class="fa fa-broom"></i> Clean Cache</a></div>
        <div class="module-install-card">
            <?= form_open_multipart(admin_url('modules/upload'), ['id' => 'module_install_form']); ?>
                <h3>Upload Module</h3>
                <p class="text-muted no-mbot">Upload a module ZIP file and install it into the CRM.</p>
                <div class="module-install-row mtop10">
                    <input type="file" class="form-control" name="module">
                    <button type="submit" class="btn btn-primary">Install</button>
                </div>
            <?= form_close(); ?>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="sc-module-tools sc-module-tools-single-row">
                            <input type="text" id="sc_module_search" class="form-control" placeholder="Search modules...">
                            <button type="button" class="btn btn-default active" data-sc-filter="all">All <span class="badge" id="sc_count_all">0</span></button>
                            <button type="button" class="btn btn-success" data-sc-filter="active">Activated</button>
                            <button type="button" class="btn btn-default" data-sc-filter="inactive">Deactivated</button>
                            <button type="button" class="btn btn-warning" data-sc-filter="update">Needs Update</button>
                            <button type="button" class="btn btn-danger" data-sc-filter="error">Errors</button>
                            <a href="#" class="btn btn-info" data-sc-filter="update"><i class="fa fa-database"></i> Review Upgrades</a>
                            <a href="<?= admin_url('modules/clear_cache'); ?>" class="btn btn-default"><i class="fa fa-broom"></i> Clean Cache</a>
                            <a href="<?= admin_url('modules/upgrade_all_database'); ?>" class="btn btn-success" onclick="return confirm('Perfex will upgrade only modules with a pending database migration. Modules without pending upgrades will be skipped. Continue?');"><i class="fa fa-arrow-up"></i> Upgrade All Dependencies</a>
                        </div>
                        <div class="alert alert-info"><b>Smart Choice Core Governance:</b> Modules can be downloaded after installation. New modules should include an impact manifest declaring affected core tables, CSS, JavaScript, and files.<br>
                            Fast mode is enabled. External module version checks, module listing hooks, ratings, and help-guide listing links are bypassed so this page does not freeze on shared hosting.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover" id="sc_modules_table">
                                <thead>
                                    <tr>
                                        <th><?= _l('module'); ?></th>
                                        <th><?= _l('module_description'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modules as $module) {
                                        $system_name = $module['system_name'];
                                        $database_upgrade_is_required = false;
                                        $versionRequirementMet = true;

                                        try {
                                            $database_upgrade_is_required = $this->app_modules->is_database_upgrade_required($system_name);
                                        } catch (Throwable $e) {
                                            $database_upgrade_is_required = false;
                                        } catch (Exception $e) {
                                            $database_upgrade_is_required = false;
                                        }

                                        try {
                                            $versionRequirementMet = $this->app_modules->is_minimum_version_requirement_met($system_name);
                                        } catch (Throwable $e) {
                                            $versionRequirementMet = true;
                                        } catch (Exception $e) {
                                            $versionRequirementMet = true;
                                        }

                                        $action_links = [];
                                        $action_links[] = '<a href="' . admin_url('modules/download/' . $system_name) . '"><i class="fa fa-download"></i> Download</a>';

                                        if ($module['activated'] === 0 && $versionRequirementMet) {
                                            $action_links[] = '<a href="' . admin_url('modules/activate/' . $system_name) . '">' . _l('module_activate') . '</a>';
                                        }

                                        if ($module['activated'] === 1) {
                                            $action_links[] = '<a href="' . admin_url('modules/deactivate/' . $system_name) . '">' . _l('module_deactivate') . '</a>';
                                        }

                                        if ($database_upgrade_is_required) {
                                            $action_links[] = '<a href="' . admin_url('modules/upgrade_database/' . $system_name) . '" class="text-success bold">' . _l('module_upgrade_database') . '</a>';
                                        }

                                        if ($module['activated'] === 0 && !in_array($system_name, uninstallable_modules())) {
                                            $action_links[] = '<a href="' . admin_url('modules/uninstall/' . $system_name) . '" class="_delete text-danger">' . _l('module_uninstall') . '</a>';
                                        }

                                        $description = $module['headers']['description'] ?? '';
                                        $author = $module['headers']['author'] ?? '';
                                        $version = $module['headers']['version'] ?? '';
                                        ?>
                                        <tr data-active="<?= $module['activated'] === 1 ? '1' : '0'; ?>" data-update="<?= $database_upgrade_is_required ? '1' : '0'; ?>" data-error="<?= !$versionRequirementMet ? '1' : '0'; ?>" class="<?= $module['activated'] === 1 && !$database_upgrade_is_required ? 'info' : ''; ?><?= $database_upgrade_is_required ? ' warning' : ''; ?>">
                                            <td data-order="<?= e($system_name); ?>">
                                                <div class="sc-module-name"><?= e($module['headers']['module_name']); ?></div>
                                                <div class="sc-module-actions"><?= implode('', $action_links); ?></div>
                                                <?php if (!$versionRequirementMet) { ?>
                                                    <div class="alert alert-warning mtop5">
                                                        This module requires at least v<?= e($module['headers']['requires_at_least']); ?> of the CRM.<?= $module['activated'] === 0 ? ' Hence, cannot be activated' : ''; ?>
                                                    </div>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <p class="sc-module-description"><?= $description; ?></p>
                                                <div class="sc-module-meta">
                                                    Version <?= e($version); ?><?php if ($author !== '') { ?> &nbsp;|&nbsp; By <?= e(strip_tags($author)); ?><?php } ?>
                                                    <?php if ($database_upgrade_is_required) { ?> &nbsp;|&nbsp; <span class="label label-warning">Database upgrade required</span><?php } ?>
                                                </div>
                                            </td>
                                        </tr>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
 var input=document.getElementById('sc_module_search'), table=document.getElementById('sc_modules_table'); if(!table)return;
 var rows=Array.prototype.slice.call(table.querySelectorAll('tbody tr')), current='all';
 function matches(row){var q=input?input.value.toLowerCase():'';if(q&&row.textContent.toLowerCase().indexOf(q)===-1)return false;if(current==='active')return row.dataset.active==='1';if(current==='inactive')return row.dataset.active==='0';if(current==='update')return row.dataset.update==='1';if(current==='error')return row.dataset.error==='1';return true;}
 function apply(){rows.forEach(function(r){r.style.display=matches(r)?'':'none';});}
 function count(name,test){var el=document.getElementById('sc_count_'+name);if(el)el.textContent=rows.filter(test).length;}
 count('all',function(){return true});
 document.querySelectorAll('[data-sc-filter]').forEach(function(b){b.addEventListener('click',function(e){e.preventDefault();current=b.dataset.scFilter;document.querySelectorAll('.sc-module-tools [data-sc-filter]').forEach(function(x){x.classList.remove('active')});var target=document.querySelector('.sc-module-tools [data-sc-filter="'+current+'"]');if(target)target.classList.add('active');apply();});});
 if(input)input.addEventListener('input',apply); apply();
});
</script>
<style>
.sc-crm-brandbar{display:flex;align-items:center;justify-content:space-between;gap:10px;background:#fff;border:1px solid #dce8f1;border-left:4px solid #168dde;border-radius:7px;padding:10px 12px;margin-bottom:12px}.sc-crm-brandbar strong{font-size:18px;color:#1f3c88}.sc-module-tools{display:flex!important;align-items:center!important;gap:4px!important;flex-wrap:nowrap!important;overflow-x:auto!important;padding-bottom:4px!important}.sc-module-tools .btn{padding:3px 7px!important;min-height:27px!important;height:27px!important;font-size:10.5px!important;white-space:nowrap!important;border-radius:5px!important}.sc-module-tools .badge{font-size:9px!important;padding:1px 4px!important}.sc-module-tools #sc_module_search{min-width:220px!important;height:31px!important}.sc-module-actions a{display:inline-block;margin-right:8px;font-size:12px}@media(max-width:900px){.sc-module-tools{overflow-x:auto!important}.sc-module-tools #sc_module_search{min-width:180px!important}.sc-crm-brandbar{align-items:flex-start}}
</style>
<?php init_tail(); ?>
