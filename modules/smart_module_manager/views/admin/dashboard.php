<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php $this->load->view('admin/_nav'); ?>
                <div class="alert alert-info smart-module-alert"><?php echo _l('smart_module_manager_development_notice'); ?></div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="smart-module-header-row">
                            <h4><?php echo _l('smart_module_manager_installed_modules'); ?></h4>
                            <a href="<?php echo admin_url('smart_module_manager'); ?>" class="btn btn-info btn-sm"><i class="fa fa-refresh"></i> <?php echo _l('smart_module_manager_refresh'); ?></a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped dt-table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('smart_module_manager_module'); ?></th>
                                        <th><?php echo _l('smart_module_manager_status'); ?></th>
                                        <th><?php echo _l('smart_module_manager_version'); ?></th>
                                        <th><?php echo _l('smart_module_manager_author'); ?></th>
                                        <th><?php echo _l('smart_module_manager_options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modules as $module) { ?>
                                        <tr>
                                            <td><strong><?php echo html_escape($module['name']); ?></strong><br><small><?php echo html_escape($module['slug']); ?></small></td>
                                            <td>
                                                <?php if ($module['active'] === 'Yes') { ?>
                                                    <span class="label label-success"><?php echo _l('smart_module_manager_enabled'); ?></span>
                                                <?php } else { ?>
                                                    <span class="label label-default"><?php echo _l('smart_module_manager_disabled'); ?></span>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo html_escape($module['version']); ?></td>
                                            <td><?php echo html_escape($module['author']); ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('smart_module_manager/download/' . $module['slug']); ?>" class="btn btn-success btn-sm smart-square-btn"><i class="fa fa-download"></i> <?php echo _l('smart_module_manager_download'); ?></a>
                                                <?php if ($module['slug'] !== SMART_MODULE_MANAGER_MODULE_NAME) { ?>
                                                    <a href="<?php echo admin_url('smart_module_manager/unload/' . $module['slug']); ?>" class="btn btn-danger btn-sm smart-square-btn"><i class="fa fa-trash"></i> <?php echo _l('smart_module_manager_unload'); ?></a>
                                                <?php } ?>
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
<?php init_tail(); ?>
</body>
</html>
