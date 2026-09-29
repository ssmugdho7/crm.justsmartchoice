<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.smart-choice-modules-page .module-install-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:14px;box-shadow:0 2px 8px rgba(17,24,39,.04)}
.smart-choice-modules-page .module-install-card h3{margin:0 0 6px;font-weight:700;color:#111827;font-size:18px}
.smart-choice-modules-page .module-install-row{display:flex;gap:8px;align-items:center;max-width:560px}
.smart-choice-modules-page .module-install-row .form-control{height:34px}
.smart-choice-modules-page .module-install-row .btn{height:34px;display:inline-flex;align-items:center;justify-content:center;min-width:90px;padding:5px 12px}
.smart-choice-modules-page .sc-module-tools{display:flex;flex-wrap:wrap;gap:8px;align-items:center;justify-content:space-between;margin-bottom:10px}
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
                        <div class="sc-module-tools">
                            <input type="text" id="sc_module_search" class="form-control" placeholder="Search modules...">
                            <div>
                                <a href="<?= admin_url('modules/upgrade_all_database'); ?>" class="btn btn-success" onclick="return confirm('Upgrade all pending module databases now?');">Upgrade All Pending</a>
                            </div>
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
                                        if ($system_name !== 'smart_choice_core_manager' && module_active('smart_choice_core_manager')) {
                                            $action_links[] = '<a href="' . admin_url('smart_choice_core_manager/download_module/' . $system_name) . '"><i class="fa fa-download"></i> Download</a>';
                                        }

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
                                        <tr class="<?= $module['activated'] === 1 && !$database_upgrade_is_required ? 'info' : ''; ?><?= $database_upgrade_is_required ? ' warning' : ''; ?>">
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
    var input = document.getElementById('sc_module_search');
    var table = document.getElementById('sc_modules_table');
    if (!input || !table) { return; }
    input.addEventListener('input', function () {
        var value = input.value.toLowerCase();
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().indexOf(value) !== -1 ? '' : 'none';
        });
    });
});
</script>
<?php init_tail(); ?>
