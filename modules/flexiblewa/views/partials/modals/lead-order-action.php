<input type="hidden" id="flexiblewa_action_type" name="flexiblewa_action_type" value="lead">
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
                    <option value="lead_added"><?php echo _flexiblewa_lang('when_lead_is_added'); ?></option>
                    <?php foreach ($statuses as $status) { ?>
                        <option value="lead_status_<?php echo $status['id']; ?>"><?php echo _flexiblewa_lang('lead_status') . ' - ' . $status['name']; ?></option>
                    <?php } ?>
                    <option value="lead_marked_as_lost"><?php echo _flexiblewa_lang('lead_marked_as_lost'); ?></option>
                    <option value="lead_marked_as_junk"><?php echo _flexiblewa_lang('lead_marked_as_junk'); ?></option>
                    <option value="lead_converted_to_customer"><?php echo _flexiblewa_lang('lead_converted_to_customer'); ?></option>
                </select>
                <div class="flexiblewa_action_sequence_container"></div>
            </div>
        </div>
    </div>
</div>