
<input type='hidden' name='rule_action' value='<?php echo FLEXIBLEWA_ADD_NEW_DISCUSSION_ACTION ?>' />
<div class="flexiblewa-discussion-form">
    <h4 class="tw-mb-4"><?php echo _l('flexiblewa_discussion'); ?></h4>
    <div class="col-md-12">
    <?= render_input('subject', 'project_discussion_subject'); ?>
    <?= render_textarea('description', 'project_discussion_description'); ?>
    <div class="checkbox checkbox-primary">
        <input type="checkbox" name="show_to_customer" checked id="show_to_customer">
        <label
                for="show_to_customer"><?= _l('project_discussion_show_to_customer'); ?></label>
        </div>
    </div>
</div>
