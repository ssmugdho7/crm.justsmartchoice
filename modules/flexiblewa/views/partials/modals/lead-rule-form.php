<div class="modal fade flexiblewa_form" id="flexiblewa_lead_rule_config" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <?php echo form_open(admin_url('flexiblewa/add_generic_rule'), [
            'enctype' => 'multipart/form-data'
        ]); ?>
        <input type="hidden" name="rule_type" value="lead">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <?php echo _l('flexiblewa_add_rule'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="container fwa-container">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="row">
                                <div class="col-sm-12">
                                    <?php echo render_input('title', 'flexiblewa_rule_name', '', 'text', [
                                        'required' => 'required'
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-8 text-center">
                            <h4 class="bold">
                                <?php echo _l('flexiblewa_add_action'); ?>
                            </h4>
                            <small>
                                <?php echo _l('flexiblewa_add_action_desc'); ?>
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 flexiblewa-rule-rhs">
                            <div class="">
                                <h5 class="bold">
                                    <?php echo _flexiblewa_lang('when'); ?>
                                </h5>
                                <select name="when" id="when" class="form-control">
                                    <option value="lead_added"><?php echo _flexiblewa_lang('when_lead_is_added'); ?></option>
                                    <?php foreach ($statuses as $status) { ?>
                                        <option value="lead_status_<?php echo $status['id']; ?>"><?php echo _flexiblewa_lang('lead_status') . ' - ' . $status['name']; ?></option>
                                    <?php } ?>
                                    <option value="lead_marked_as_lost"><?php echo _flexiblewa_lang('lead_marked_as_lost'); ?></option>
                                    <option value="lead_marked_as_junk"><?php echo _flexiblewa_lang('lead_marked_as_junk'); ?></option>
                                    <option value="lead_converted_to_customer"><?php echo _flexiblewa_lang('lead_converted_to_customer'); ?></option>
                                </select>
                                <h5 class="bold tw-mt-4">
                                    <?php echo _flexiblewa_lang('i_want_to'); ?>
                                </h5>
                            </div>
                            <hr />
                            <div class="action-row-section">
                                <div class="fwa-title">
                                    <h5><?php echo _flexiblewa_lang('create_new'); ?></h5>
                                </div>
                                <div class="fwa-content">
                                    <p>
                                        <button class="btn btn-link action-btn flexiblewa-action-btn" type="button"
                                            data-id="<?php echo FLEXIBLEWA_ADD_NEW_REMINDER_ACTION ?>">
                                            <i class="fa fa-bell"></i> <span class="">
                                                <?php echo _l('flexiblewa_new_reminder'); ?>
                                            </span>
                                        </button>
                                    </p>

                                    <!-- add a new task -->
                                    <p>
                                        <button class="btn btn-link action-btn flexiblewa-action-btn" type="button"
                                            data-id="<?php echo FLEXIBLEWA_ADD_NEW_TASK_ACTION ?>">
                                            <i class="fa fa-tasks"></i> <span class="">
                                                <?php echo _l('flexiblewa_new_task'); ?>
                                            </span>
                                        </button>
                                    </p>
                                    <!-- add new notes -->
                                    <p>
                                        <button class="btn btn-link action-btn flexiblewa-action-btn" type="button"
                                            data-id="<?php echo FLEXIBLEWA_ADD_NEW_NOTE_ACTION ?>">
                                            <i class="fa fa-sticky-note"></i> <span class="">
                                                <?php echo _l('flexiblewa_new_note'); ?>
                                            </span>
                                        </button>
                                    </p>
                                </div>
                            </div>
                            <hr />
                            <div class="action-row-section">
                                <div class="fwa-title">
                                    <h5><?php echo _flexiblewa_lang('communication'); ?></h5>
                                </div>
                                <div class="fwa-content">
                                   <!-- send email -->
                                   <p>
                                        <button class="btn btn-link action-btn flexiblewa-action-btn" type="button"
                                            data-id="<?php echo FLEXIBLEWA_SEND_EMAIL_ACTION ?>">
                                            <i class="fa fa-envelope"></i> <span class="">
                                                <?php echo _l('flexiblewa_send_email'); ?>
                                            </span>
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-8 flexiblewa-rule-lhs  tw-pt-5 mtop3">
                            <h4 class="text-white text-center">
                                <?php echo _l('flexiblewa_action_notice'); ?>
                            </h4>
                            <div>
                                <span class="tw-animate-spin"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo _l('close'); ?>
                </button>
                <button type="submit" class="btn btn-primary">
                    <?php echo _l('flexiblewa_create_rule'); ?>
                </button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->