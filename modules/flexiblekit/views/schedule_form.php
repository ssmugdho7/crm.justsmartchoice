<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12" id="flexstage-add-edit-wrapper">
                <div class="row">
                    <div class="col-md-8">
                        <div class="panel_s">
                            <div class="panel-body ">
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                                    <?php echo $title; ?>
                                </h4>
                                <?php echo validation_errors('<div class="alert alert-danger alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="right:0px"><span aria-hidden="true">&times;</span></button>', '</div>'); ?>

                                <?php
                                $attributes = ['id' => 'flexibleschedule_form'];
                                ?>
                                <?php echo form_open($this->uri->uri_string(), $attributes); ?>
                                <?php echo render_input('flexibleschedule_contact_type', '', $schedule['flexibleschedule_contact_type'], 'hidden') ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php echo render_input('flexibleschedule_subject', 'flexiblekit_subject', $schedule['flexibleschedule_subject'], 'text', ['required' => 'required', 'placeholder' => _l('flexiblekit_subject_placeholder')]); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $statuses = flexiblekit_get_schedule_statuses() ?>
                                        <?php echo render_select('flexibleschedule_status', $statuses, ['id', 'label'], 'flexiblekit_status', $schedule['flexibleschedule_status'], ['required' => true]); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_datetime_input('flexibleschedule_start_datetime', 'flexiblekit_start_datetime', $schedule['flexibleschedule_start_datetime'], ['placeholder' => _l('flexiblekit_start_datetime_placeholder'), 'required' => true]); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_datetime_input('flexibleschedule_end_datetime', 'flexiblekit_end_datetime', $schedule['flexibleschedule_end_datetime'], ['placeholder' => _l('flexiblekit_end_datetime_placeholder'), 'required' => true]); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php echo render_input('flexibleschedule_contact_name', 'flexiblekit_contact', $schedule['flexibleschedule_contact_name'], 'text', [
                                            'disabled' => 'disabled'
                                        ]); ?>
                                    </div>
                                    <?php $from = isset($_GET['from']) ? $_GET['from'] : ''; ?>
                                    <?php if($from): ?>
                                    <input type="hidden" name="from" value="<?php echo $from ?>" />
                                    <?php endif; ?>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary display-block tw-mx-auto">
                                            <?php echo _l('flexiblekit_submit'); ?>
                                        </button>
                                    </div>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
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