<input type="hidden" id="flexiblewa_action_type" name="flexiblewa_action_type" value="project">
<div class="modal fade" id="flexiblewa_action_sequence" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('flexiblewa_order_action'); ?></h4>
            </div>
            <div class="modal-body">
                <select name="when" id="when" class="form-control">
                    <option value=""><?php echo _flexiblewa_lang('select_when_event'); ?></option>
                    <option value="project_added"><?php echo _flexiblewa_lang('when_project_is_added'); ?></option>
                    <?php foreach ($statuses as $status) { ?>
                        <option value="project_status_<?php echo $status['id']; ?>"><?php echo _flexiblewa_lang('project_status') . ' - ' . $status['name']; ?></option>
                    <?php } ?>
                </select>
                <div class="flexiblewa_action_sequence_container"></div>
            </div>
        </div>
    </div>
</div>