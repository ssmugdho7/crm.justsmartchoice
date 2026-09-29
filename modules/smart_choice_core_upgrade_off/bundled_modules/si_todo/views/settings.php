<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo _l('si_todo_settings'); ?></h4>
                            </div>
                            <div class="col-md-6 text-right si-todo-actions-bar">
                                <a href="<?php echo admin_url('si_todo'); ?>" class="btn btn-default btn-sm"><?php echo _l('si_todo_list_menu'); ?></a>
                                <a href="<?php echo admin_url('si_todo/category_list'); ?>" class="btn btn-default btn-sm"><?php echo _l('si_todo_category_menu'); ?></a>
                                <a href="<?php echo admin_url('si_todo/health'); ?>" class="btn btn-info btn-sm"><?php echo _l('si_todo_health_check'); ?></a>
                                <a href="<?php echo current_full_url(); ?>" class="btn btn-default btn-sm"><?php echo _l('refresh'); ?></a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading">
                        <?php echo form_open('admin/si_todo/settings', ['id' => 'si_todo_settings']); ?>
                        <div class="row">
                            <div class="col-md-4">
                                <?php echo render_input('dashboard_unfinished_limit', 'si_todo_dashboard_unfinished_limit', $settings['dashboard_unfinished_limit'] ?? 5, 'number', ['min' => 0, 'max' => 100]); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input('dashboard_finished_limit', 'si_todo_dashboard_finished_limit', $settings['dashboard_finished_limit'] ?? 5, 'number', ['min' => 0, 'max' => 100]); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input('todos_load_limit', 'si_todo_todos_load_limit', $settings['todos_load_limit'] ?? 20, 'number', ['min' => 1, 'max' => 1000]); ?>
                            </div>
                        </div>
                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-info btn-sm"><?php echo _l('submit'); ?></button>
                        </div>
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
