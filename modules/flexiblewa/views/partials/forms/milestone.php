<input type="hidden" name="rule_action" value="<?php echo FLEXIBLEWA_ADD_NEW_MILESTONE_ACTION ?>">
<div class="flexiblewa-milestone-form">
    <h4 class="tw-mb-4"><?php echo _l('flexiblewa_milestone'); ?></h4>
</div>
<div class="col-md-12">
    <div id="additional_milestone"></div>
    <?= render_input('name', 'milestone_name'); ?>
    <div class="row">
        <div class="col-md-6">
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
    </div>
    <div class="col-md-12">
            <div class="alert alert-info">
                <?php echo _l('flexiblewa_task_add_edit_start_date_help'); ?>
            </div>
        </div>
    <?= render_textarea('description', 'milestone_description'); ?>
    <div class="checkbox">
        <input type="checkbox" id="description_visible_to_customer"
            name="description_visible_to_customer">
        <label
            for="description_visible_to_customer"><?= _l('description_visible_to_customer'); ?></label>
    </div>
    <div class="checkbox">
        <input type="checkbox" id="hide_from_customer" name="hide_from_customer">
        <label for="hide_from_customer">
            <i class="fa-regular fa-circle-question" data-toggle="tooltip"
                title="<?= _l('hide_milestone_from_customer_help') ?>"></i>
            <?= _l('hide_milestone_from_customer'); ?>
        </label>
    </div>
</div>