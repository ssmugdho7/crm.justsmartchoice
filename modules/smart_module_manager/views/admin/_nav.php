<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="smart-module-nav panel_s">
    <div class="panel-body">
        <div class="smart-module-nav-title">
            <i class="fa fa-archive"></i> <?php echo _l('smart_module_manager'); ?>
        </div>
        <div class="smart-module-nav-links">
            <a href="<?php echo admin_url('smart_module_manager'); ?>" class="btn btn-default btn-sm"><i class="fa fa-dashboard"></i> <?php echo _l('smart_module_manager_dashboard'); ?></a>
            <a href="<?php echo admin_url('smart_module_manager/health'); ?>" class="btn btn-default btn-sm"><i class="fa fa-heartbeat"></i> <?php echo _l('smart_module_manager_health_check'); ?></a>
            <a href="<?php echo admin_url('smart_module_manager/settings'); ?>" class="btn btn-default btn-sm"><i class="fa fa-cogs"></i> <?php echo _l('smart_module_manager_settings'); ?></a>
        </div>
    </div>
</div>
