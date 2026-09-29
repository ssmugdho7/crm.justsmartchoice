<?php $rel_id = ''; ?>
<?php $rel_type = ''; ?>
<div class='flexiblewa-task-form'>
    <h4 class="tw-mb-4"><?php echo _l('flexiblewa_task'); ?></h4>
    <input type='hidden' name='rule_action' value='<?php echo FLEXIBLEWA_ADD_NEW_TASK_ACTION ?>' />
    <div>
        <div class="checkbox checkbox-primary checkbox-inline task-add-edit-public tw-pt-2">
            <input type="checkbox" id="task_is_public" name="is_public">
            <label for="task_is_public" data-toggle="tooltip" data-placement="bottom"
                title="<?php echo _l('task_public_help'); ?>"><?php echo _l('task_public'); ?></label>
        </div>
        <div class="checkbox checkbox-primary checkbox-inline task-add-edit-billable tw-pt-2">
            <input type="checkbox" id="task_is_billable" name="billable" checked>
            <label for="task_is_billable"><?php echo _l('task_billable'); ?></label>
        </div>
    </div>
    <div class="tw-mt-3">
        <?php echo render_input('name', 'task_add_edit_subject', ''); ?>
        <?php echo render_input('hourly_rate', 'task_hourly_rate', 0); ?>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?php if (isset($task)) {
                $value = _d($task->startdate);
            } elseif (isset($start_date)) {
                $value = $start_date;
            } else {
                $value = _d(date('Y-m-d'));
            }
            $date_attrs = [];
            if (isset($task) && $task->recurring > 0 && $task->last_recurring_date != null) {
                $date_attrs['disabled'] = true;
            }
            ?>
            <div class="row">
                <div class="col-md-12">
                    <label for="start_date"><?php echo _l('task_add_edit_start_date'); ?></label>
                </div>
                <div class="col-md-6">
                    <div class='form-group'>
                        <div class="input-group">
                            <span class="input-group-addon" id="time_count_addon">
                                <i class='fa fa-plus'></i>
                            </span>
                            <input id='start_time_count' name="start_time_count" type="number" class="form-control" min="0" step='1' value='1'
                                aria-describedby="start_time_count_addon" required>
                        </div>
                    </div>
                </div>
                <div class='col-md-6'>
                    <?php echo render_select('start_period', flexiblewa_get_periods(false), ['id', 'name'], '', 'days', [
                        'required' => 'required'
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <label for="due_date"><?php echo _l('task_add_edit_due_date'); ?></label>
                </div>
                <div class="col-md-6">
                    <div class='form-group'>
                        <div class="input-group">
                        <span class="input-group-addon" id="time_count_addon">
                            <i class='fa fa-plus'></i>
                        </span>
                        <input id='due_time_count' name="due_time_count" type="number" class="form-control" min="0" step='1' value='2'
                            aria-describedby="due_time_count_addon" required>
                        </div>
                    </div>
                </div>
                <div class='col-md-6'>
                    <?php echo render_select('due_period', flexiblewa_get_periods(false), ['id', 'name'], '', 'days', [
                        'required' => 'required'
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="alert alert-info">
                <?php echo _l('flexiblewa_task_add_edit_start_date_help'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="priority"
                    class="control-label"><?php echo _l('task_add_edit_priority'); ?></label>
                <select name="priority" class="selectpicker" id="priority" data-width="100%"
                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                    <?php foreach (get_tasks_priorities() as $priority) { ?>
                        <option value="<?php echo $priority['id']; ?>" <?php if (isset($task) && $task->priority == $priority['id'] || !isset($task) && get_option('default_task_priority') == $priority['id']) {
                                                                                echo ' selected';
                                                                            } ?>><?php echo $priority['name']; ?></option>
                    <?php } ?>
                    <?php hooks()->do_action('task_priorities_select', (isset($task) ? $task : 0)); ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="repeat_every"
                    class="control-label"><?php echo _l('task_repeat_every'); ?></label>
                <select name="repeat_every" id="repeat_every" class="selectpicker" data-width="100%"
                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                    <option value=""></option>
                    <option value="1-week" <?php if (isset($task) && $task->repeat_every == 1 && $task->recurring_type == 'week') {
                                                echo 'selected';
                                            } ?>><?php echo _l('week'); ?></option>
                    <option value="2-week" <?php if (isset($task) && $task->repeat_every == 2 && $task->recurring_type == 'week') {
                                                echo 'selected';
                                            } ?>>2 <?php echo _l('weeks'); ?></option>
                    <option value="1-month" <?php if (isset($task) && $task->repeat_every == 1 && $task->recurring_type == 'month') {
                                                echo 'selected';
                                            } ?>>1 <?php echo _l('month'); ?></option>
                    <option value="2-month" <?php if (isset($task) && $task->repeat_every == 2 && $task->recurring_type == 'month') {
                                                echo 'selected';
                                            } ?>>2 <?php echo _l('months'); ?></option>
                    <option value="3-month" <?php if (isset($task) && $task->repeat_every == 3 && $task->recurring_type == 'month') {
                                                echo 'selected';
                                            } ?>>3 <?php echo _l('months'); ?></option>
                    <option value="6-month" <?php if (isset($task) && $task->repeat_every == 6 && $task->recurring_type == 'month') {
                                                echo 'selected';
                                            } ?>>6 <?php echo _l('months'); ?></option>
                    <option value="1-year" <?php if (isset($task) && $task->repeat_every == 1 && $task->recurring_type == 'year') {
                                                echo 'selected';
                                            } ?>>1 <?php echo _l('year'); ?></option>
                    <option value="custom" <?php if (isset($task) && $task->custom_recurring == 1) {
                                                echo 'selected';
                                            } ?>><?php echo _l('recurring_custom'); ?></option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Rel To will contain the rel_id and rel_type for the Lead we are running Automation on -->
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group select-placeholder>">
                <label for="assignees"><?php echo _l('task_single_assignees'); ?></label>
                <select name="assignees[]" id="assignees" class="selectpicker" data-width="100%"
                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                    multiple data-live-search="true">
                    <?php foreach ($members as $member) { ?>
                        <option value="<?php echo $member['staffid']; ?>" <?php if ((get_option('new_task_auto_assign_current_member') == '1') && get_staff_user_id() == $member['staffid']) {
                                                                                    echo 'selected';
                                                                                } ?>>
                            <?php echo $member['firstname'] . ' ' . $member['lastname']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <?php
            $follower = (get_option('new_task_auto_follower_current_member') == '1') ? [get_staff_user_id()] : '';
            echo render_select('followers[]', $members, ['staffid', ['firstname', 'lastname']], 'task_single_followers', $follower, ['multiple' => true], [], '', '', false);
            ?>
        </div>
    </div>

    <div class="form-group">
        <div id="inputTagsWrapper">
            <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
                <?php echo _l('tags'); ?></label>
            <input type="text" class="tagsinput" id="tags" name="tags"
                value="<?php echo (isset($task) ? prep_tags_input(get_tags_in($task->id, 'task')) : ''); ?>"
                data-role="tagsinput">
        </div>
    </div>
    <p class="bold"><?php echo _l('task_add_edit_description'); ?></p>
    <?php
    // onclick and onfocus used for convert ticket to task too
    echo render_textarea('description', '', (isset($task) ? $task->description : ''), ['rows' => 6, 'placeholder' => _l('task_add_description'), 'data-task-ae-editor' => true, !is_mobile() ? 'onclick' : 'onfocus' => (!isset($task) || isset($task) && $task->description == '' ? 'init_editor(\'.tinymce-task\', {height:200, auto_focus: true});' : '')], [], 'no-mbot', 'tinymce-task'); ?>

</div>