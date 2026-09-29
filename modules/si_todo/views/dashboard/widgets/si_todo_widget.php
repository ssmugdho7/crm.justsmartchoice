<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$si_todos = isset($si_todos) && is_array($si_todos) ? $si_todos : [];
$si_todos_finished = isset($si_todos_finished) && is_array($si_todos_finished) ? $si_todos_finished : [];
?>
<div class="widget" id="widget-<?php echo basename(__FILE__, '.php'); ?>" data-name="<?php echo _l('si_todo_dashboard_menu'); ?>">
    <div class="panel_s todo-panel">
        <div class="panel-body padding-10">
            <div class="widget-dragger"></div>
            <p class="pull-left padding-5"><?php echo _l('si_todo_dashboard_menu'); ?></p>
            <a href="<?php echo admin_url('si_todo'); ?>" class="pull-right padding-5 si-todo-widget-action">&nbsp;|&nbsp;&nbsp;<?php echo _l('home_widget_view_all'); ?></a>
            <a href="#si_todo_modal" data-toggle="modal" class="pull-right padding-5 si-todo-widget-action"><?php echo _l('new_todo'); ?></a>
            <div class="clearfix"></div>
            <hr class="hr-panel-heading-dashboard">

            <?php $si_total_todos = count($si_todos); ?>
            <h4 class="todo-title text-warning"><i class="fa fa-warning"></i> <?php echo _l('home_latest_todos'); ?></h4>
            <ul class="list-unstyled todo unfinished-todos si-todos-sortable sortable">
                <?php foreach ($si_todos as $todo) { ?>
                    <li>
                        <?php echo form_hidden('todo_order', $todo['item_order'] ?? 0); ?>
                        <?php echo form_hidden('finished', 0); ?>
                        <div class="media">
                            <div class="media-left no-padding-right">
                                <div class="dragger todo-dragger"></div>
                                <div class="checkbox checkbox-default todo-checkbox">
                                    <input type="checkbox" name="todo_id" value="<?php echo (int)($todo['todoid'] ?? 0); ?>">
                                    <label></label>
                                </div>
                            </div>
                            <div class="media-body">
                                <p class="todo-description read-more no-padding-left" data-todo-description="<?php echo (int)($todo['todoid'] ?? 0); ?>">
                                    <i class="fa fa-flag mright5" style="color:<?php echo si_todo_priority_color($todo['priority'] ?? 1); ?>"></i>
                                    <?php echo $todo['description'] ?? ''; ?>
                                    <br>
                                    <small class="text-bold" style="color:<?php echo html_escape($todo['color'] ?? '#333333'); ?>"><?php echo html_escape($todo['category_name'] ?? ''); ?></small>
                                </p>
                                <a href="#" onclick="si_delete_todo_item(this,<?php echo (int)($todo['todoid'] ?? 0); ?>); return false;" class="pull-right text-muted"><i class="fa fa-remove"></i></a>
                                <a href="#" onclick="si_edit_todo_item(<?php echo (int)($todo['todoid'] ?? 0); ?>); return false;" class="pull-right text-muted mright5"><i class="fa fa-pencil"></i></a>
                                <small class="todo-date"><?php echo html_escape($todo['dateadded'] ?? ''); ?></small>
                            </div>
                        </div>
                    </li>
                <?php } ?>
                <li class="padding no-todos ui-state-disabled <?php echo $si_total_todos > 0 ? 'hide' : ''; ?>"><?php echo _l('home_no_latest_todos'); ?></li>
            </ul>

            <?php $si_total_finished_todos = count($si_todos_finished); ?>
            <h4 class="todo-title text-success"><i class="fa fa-check"></i> <?php echo _l('home_latest_finished_todos'); ?></h4>
            <ul class="list-unstyled todo finished-todos si-todos-sortable sortable">
                <?php foreach ($si_todos_finished as $todo_finished) { ?>
                    <li>
                        <?php echo form_hidden('todo_order', $todo_finished['item_order'] ?? 0); ?>
                        <?php echo form_hidden('finished', 1); ?>
                        <div class="media">
                            <div class="media-left no-padding-right">
                                <div class="dragger todo-dragger"></div>
                                <div class="checkbox checkbox-default todo-checkbox">
                                    <input type="checkbox" value="<?php echo (int)($todo_finished['todoid'] ?? 0); ?>" name="todo_id" checked>
                                    <label></label>
                                </div>
                            </div>
                            <div class="media-body">
                                <p class="todo-description read-more line-throught no-padding-left">
                                    <i class="fa fa-flag mright5" style="color:<?php echo si_todo_priority_color($todo_finished['priority'] ?? 1); ?>"></i>
                                    <?php echo $todo_finished['description'] ?? ''; ?>
                                    <br>
                                    <small style="color:<?php echo html_escape($todo_finished['color'] ?? '#333333'); ?>"><?php echo html_escape($todo_finished['category_name'] ?? ''); ?></small>
                                </p>
                                <a href="#" onclick="si_delete_todo_item(this,<?php echo (int)($todo_finished['todoid'] ?? 0); ?>); return false;" class="pull-right text-muted"><i class="fa fa-remove"></i></a>
                                <a href="#" onclick="si_edit_todo_item(<?php echo (int)($todo_finished['todoid'] ?? 0); ?>); return false;" class="pull-right text-muted mright5"><i class="fa fa-pencil"></i></a>
                                <small class="todo-date todo-date-finished"><?php echo html_escape($todo_finished['datefinished'] ?? ''); ?></small>
                            </div>
                        </div>
                    </li>
                <?php } ?>
                <li class="padding no-todos ui-state-disabled <?php echo $si_total_finished_todos > 0 ? 'hide' : ''; ?>"><?php echo _l('home_no_finished_todos_found'); ?></li>
            </ul>
        </div>
    </div>
    <?php $this->load->view('si_todo/add_todo'); ?>
    <?php $this->load->view('si_todo/add_category'); ?>
</div>
<?php hooks()->add_action('app_admin_footer', 'parse_si_todo_js');
function parse_si_todo_js()
{ ?>
    <script>var si_todo_alphanumeric_validation = '<?php echo _l('si_todo_alphanumeric_validation'); ?>';</script>
    <script src="<?php echo module_dir_url('si_todo', 'assets/js/si_todo.js'); ?>"></script>
    <script src="<?php echo module_dir_url('si_todo', 'assets/js/si_todo_category.js'); ?>"></script>
<?php } ?>
