<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php $this->load->view('admin/_nav'); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="smart-module-header-row">
                            <h4><?php echo _l('smart_module_manager_health_check'); ?></h4>
                            <a href="<?php echo admin_url('smart_module_manager/repair_database'); ?>" class="btn btn-warning btn-sm"><i class="fa fa-wrench"></i> <?php echo _l('smart_module_manager_repair_database'); ?></a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('smart_module_manager_module'); ?></th>
                                        <th><?php echo _l('smart_module_manager_structure_check'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modules as $module) { ?>
                                        <tr>
                                            <td><strong><?php echo html_escape($module['name']); ?></strong><br><small><?php echo html_escape($module['slug']); ?></small></td>
                                            <td>
                                                <?php foreach ($module['health'] as $label => $ok) { ?>
                                                    <span class="label <?php echo $ok ? 'label-success' : 'label-danger'; ?> smart-health-label">
                                                        <?php echo html_escape($label); ?>: <?php echo $ok ? 'OK' : 'Missing'; ?>
                                                    </span>
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
