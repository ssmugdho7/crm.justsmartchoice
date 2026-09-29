<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper" class="si-todo-health-page">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo _l('si_todo_health_check'); ?></h4>
                            </div>
                            <div class="col-md-6 text-right si-todo-actions-bar">
                                <a href="<?php echo admin_url('si_todo'); ?>" class="btn btn-default btn-sm"><?php echo _l('si_todo_list_menu'); ?></a>
                                <a href="<?php echo admin_url('si_todo/settings'); ?>" class="btn btn-default btn-sm"><?php echo _l('si_todo_settings_menu'); ?></a>
                                <a href="<?php echo current_full_url(); ?>" class="btn btn-info btn-sm"><?php echo _l('refresh'); ?></a>
                            </div>
                        </div>
                        <hr class="hr-panel-heading">
                        <p><?php echo _l('si_todo_health_intro'); ?></p>
                        <div class="row">
                            <?php foreach ($health as $check) { ?>
                                <div class="col-md-4">
                                    <div class="si-todo-health-card">
                                        <h5><?php echo html_escape($check['name']); ?></h5>
                                        <p class="<?php echo $check['status'] ? 'si-todo-status-ok' : 'si-todo-status-bad'; ?>">
                                            <?php echo $check['status'] ? _l('si_todo_health_ok') : _l('si_todo_health_missing'); ?>
                                        </p>
                                        <small><?php echo html_escape($check['details']); ?></small>
                                    </div>
                                </div>
                            <?php } ?>
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
