<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php $this->load->view('admin/_nav'); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php echo _l('smart_module_manager_settings'); ?></h4>
                        <hr>
                        <?php echo form_open(admin_url('smart_module_manager/settings')); ?>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="smart_module_manager_enabled" id="smart_module_manager_enabled" value="1" <?php echo get_option('smart_module_manager_enabled') === '1' ? 'checked' : ''; ?>>
                                <label for="smart_module_manager_enabled"><?php echo _l('smart_module_manager_enabled'); ?></label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="smart_module_manager_allow_unload" id="smart_module_manager_allow_unload" value="1" <?php echo get_option('smart_module_manager_allow_unload') === '1' ? 'checked' : ''; ?>>
                                <label for="smart_module_manager_allow_unload"><?php echo _l('smart_module_manager_allow_unload'); ?></label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="smart_module_manager_backup_before_unload" id="smart_module_manager_backup_before_unload" value="1" <?php echo get_option('smart_module_manager_backup_before_unload') === '1' ? 'checked' : ''; ?>>
                                <label for="smart_module_manager_backup_before_unload"><?php echo _l('smart_module_manager_backup_before_unload'); ?></label>
                            </div>
                            <div class="checkbox checkbox-danger">
                                <input type="checkbox" name="smart_module_manager_allow_database_cleanup" id="smart_module_manager_allow_database_cleanup" value="1" <?php echo get_option('smart_module_manager_allow_database_cleanup') === '1' ? 'checked' : ''; ?>>
                                <label for="smart_module_manager_allow_database_cleanup"><?php echo _l('smart_module_manager_allow_database_cleanup'); ?></label>
                            </div>
                            <?php echo render_input('smart_module_manager_export_path', _l('smart_module_manager_export_path'), get_option('smart_module_manager_export_path')); ?>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo _l('smart_module_manager_save_settings'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
