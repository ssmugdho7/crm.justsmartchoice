<div class='row'>
    <div class="col-md-6 col-md-offset-3">
        <input type='hidden' name='rule_action' value='<?php echo FLEXIBLEWA_ADD_NEW_REMINDER_ACTION ?>' />
        <h4 class="tw-mb-4"><?php echo _l('flexiblewa_reminder') ?></h4>
    </div>
    <div class="col-md-3 col-md-offset-3">
        <div class='form-group'>
            <div class="input-group">
                <span class="input-group-addon" id="time_count_addon">
                    <i class='fa fa-plus'></i>
                </span>
                <input id='time_count' name='time_count' type="number" class="form-control" min="0.01" step='0.01' value='1' aria-describedby="time_count_addon" required>
            </div>
        </div>
    </div>
    <div class='col-md-3'>
        <?php echo render_select('period', flexiblewa_get_periods(true), ['id', 'name'], '', 'hours', [
            'required' => 'required'
        ]); ?>
    </div>
    <div class="col-md-6 col-md-offset-3">
        <?php echo render_select('reminder_user_id', flexiblewa_get_staff_members(), ['staffid', ['firstname', 'lastname']], 'flexiblewa_remind_who', '', ['required' => true], [], '', '', false); ?>
    </div>
    <!-- description -->
    <div class="col-md-6 col-md-offset-3">
        <div class="form-group">
            <label for="reminder_message"><?php echo _l('flexiblewa_reminder_message'); ?></label>
            <textarea name="reminder_message" id="reminder_message" class="form-control" rows="3"></textarea>
        </div>
    </div>
</div>