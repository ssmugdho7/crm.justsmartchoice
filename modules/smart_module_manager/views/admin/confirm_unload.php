<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <?php $this->load->view('admin/_nav'); ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php echo _l('smart_module_manager_complete_unload'); ?>: <?php echo html_escape(smart_module_manager_title_case($module_name)); ?></h4>
                        <div class="alert alert-danger"><?php echo _l('smart_module_manager_confirm_unload'); ?></div>
                        <?php echo form_open(admin_url('smart_module_manager/unload/' . $module_name)); ?>
                            <input type="hidden" name="confirm" value="yes">
                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> <?php echo _l('smart_module_manager_unload'); ?></button>
                            <a href="<?php echo admin_url('smart_module_manager'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
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
