<?php
$rel_type = $this->input->post('rel_type');
$rel_id = $this->input->post('rel_id');
?>
<div class="modal fade" id="newAppointmentModal" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= _l('close'); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= _l('appointment_new_appointment'); ?></h4>
            </div>

            <?= form_open('appointly/appointments/create', ['id' => 'appointment-form']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Calendar Integration Checkboxes -->
                        <?php if (appointlyGoogleAuth() && get_option('appointly_google_client_secret')) : ?>
                            <div class="checkbox pull-right mtop1 label label-default tw-px-2 tw-py-1">
                                <div class="tw-flex tw-items-center tw-mt-1">
                                    <input type="checkbox" name="google" id="google" class="tw-mr-2">
                                    <label data-toggle="tooltip" class="tw-ml-6" title="<?= _l('appointment_add_to_google_calendar'); ?>"
                                        for="google">
                                        <?= _l('appointment_add_to_google_calendar'); ?>
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Subject Field -->
                        <?= render_input('subject', 'appointment_subject'); ?>

                        <!-- Service Selection -->
                        <div class="form-group staffonly-hide" id="service_field">
                            <label for="service_id"><?= _l('service'); ?></label>
                            <select name="service_id" id="service_id" class="selectpicker" data-width="100%"
                                data-live-search="true" required>
                                <option value=""><?= _l('dropdown_non_selected_tex'); ?></option>
                                <?php foreach ($services as $service) { ?>
                                    <option value="<?= $service['id']; ?>" data-content="<span class='service-option' style='border-left: 3px solid <?= $service['color']; ?>; padding-left: 5px;'>
                                                <?= $service['name']; ?>
                                                <small class='text-muted'>(<?= $service['duration']; ?> <?= _l('minutes'); ?>)</small>
                                            </span>">
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- Provider Selection -->
                        <div class="form-group staffonly-hide" id="provider_field">
                            <label for="provider_id"><?= _l('appointment_provider'); ?></label>
                            <select name="provider_id" id="provider_id" class="selectpicker" data-width="100%"
                                data-live-search="true" required disabled>
                                <option value=""><?= _l('appointment_select_provider'); ?></option>
                            </select>
                        </div>

                        <input type="hidden" name="duration" id="appointment_duration" value="">

                        <!-- Description -->
                        <?= render_textarea('description', 'appointment_description', '', ['rows' => 5]); ?>

                        <!-- Appointment Type -->
                        <div class="form-group select-placeholder">
                            <label for="rel_type"><?= _l('proposal_related'); ?></label>
                            <select name="rel_type" id="rel_type" class="selectpicker" data-width="100%"
                                data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>" onchange="toggleStaffOnlyFields()">
                                <option value=""></option>
                                <option id="lead_related" value="lead_related"><?= _l('lead'); ?></option>
                                <option id="external" value="external"><?= _l('appointments_source_external_label'); ?></option>
                                <option id="internal" value="internal"><?= _l('appointment_source_internal'); ?></option>
                                <option id="internal_staff" value="internal_staff">Staff Only</option>
                            </select>
                        </div>

                        <!-- Related Contact Fields -->
                        <div class="form-group select-placeholder hide client-fields" id="rel_id_wrapper">
                            <input type="text" hidden name="rel_lead_type" id="rel_lead_type" value="leads">
                            <label for="rel_id"><?= _l('leads'); ?></label>
                            <div id="rel_id_select">
                                <select name="rel_id" id="rel_id" class="ajax-search" data-width="100%"
                                    data-live-search="true">
                                    <?php if ($rel_id && $rel_type) {
                                        $rel_data = get_relation_data($rel_type, $rel_id);
                                        $rel_val = get_relation_values($rel_data, $rel_type);
                                        echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Contact Selection -->
                        <div class="form-group hidden client-fields" id="select_contacts">
                            <?= render_select('contact_id', $contacts, ['contact_id', ['firstname', 'lastname', 'company']], 'appointment_select_single_contact', '', [], [], '', '', true); ?>
                        </div>

                        <!-- External Contact Details -->
                        <div class="form-group hidden client-fields" id="div_name">
                            <label for="name"><?= _l('appointment_name'); ?></label>
                            <input type="text" class="form-control" name="name" id="name">
                        </div>
                        <div class="form-group hidden client-fields" id="div_email">
                            <label for="email"><?= _l('appointment_email'); ?></label>
                            <input type="email" class="form-control" name="email" id="email">
                        </div>
                        <div class="form-group hidden client-fields" id="div_phone">
                            <label for="phone"><?= _l('appointment_phone'); ?>
                                (<?= _l('appointment_your_phone_example'); ?>)</label>
                            <input type="text" class="form-control" name="phone" id="phone">
                        </div>

                        <!-- Date/Time Selection -->
                        <div class="col-md-6 no-padding">
                            <?php echo render_datetime_input('date', 'appointment_date_and_time', '', ['readonly' => "readonly"], [], '', 'appointment-date'); ?>
                        </div>

                        <!-- Location and Duration -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address"><?= _l('appointment_meeting_location'); ?></label>
                                    <input type="text" class="form-control" name="address" id="address">
                                </div>
                            </div>
                        </div>
                        <div class="tw-mb-6">
                            <div class="tw-relative">
                                <label for="timezone"><?= _l('timezone'); ?></label>
                                <select name="timezone"
                                    id="timezone"
                                    class="form-control selectpicker"
                                    data-live-search="true">
                                    <?php foreach (get_timezones_list() as $region => $timezones) { ?>
                                        <optgroup label="<?= $region; ?>">
                                            <?php foreach ($timezones as $timezone) { ?>
                                                <option value="<?= $timezone; ?>"
                                                    <?= get_option('default_timezone') == $timezone ? 'selected' : ''; ?>>
                                                    <?= $timezone; ?>
                                                </option>
                                            <?php } ?>
                                        </optgroup>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <!-- Attendees -->
                        <div class="form-group">
                            <?= render_select('attendees[]', $staff_members, ['staffid', ['firstname', 'lastname']], 'appointment_select_attendees', [get_staff_user_id()], ['multiple' => true], [], '', '', false); ?>
                        </div>

                        <!-- Recurring Options -->
                        <?php $this->load->view('view_includes/recurring_wrapper'); ?>

                        <!-- Custom Fields -->
                        <?php
                        $rel_cf_id = (isset($appointment) ? $appointment['appointment_id'] : false);
                        echo render_custom_fields('appointly', $rel_cf_id);
                        ?>

                        <!-- Notifications -->
                        <div class="form-group mtop10">
                            <div class="row">
                                <div class="col-md-12 mbot5">
                                    <?= _l('appointment_modal_notification_info'); ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="checkbox">
                                        <input type="checkbox" name="by_sms" id="by_sms">
                                        <label for="by_sms"><?= _l('appoontment_sms_notification'); ?></label>
                                    </div>
                                    <div class="checkbox">
                                        <input type="checkbox" name="by_email" id="by_email">
                                        <label for="by_email"><?= _l('appoontment_email_notification'); ?></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reminder Settings -->
                        <div class="form-group appointment-reminder hide">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="reminder_before"><?= _l('appointments_reminder_time_value'); ?></label>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="reminder_before"
                                            id="reminder_before">
                                        <span class="input-group-addon">
                                            <i class="fa fa-question-circle" data-toggle="tooltip"
                                                data-title="<?= _l('reminder_notification_placeholder'); ?>"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <select name="reminder_before_type" id="reminder_before_type" class="selectpicker"
                                        data-width="100%">
                                        <option value="minutes"><?= _l('minutes'); ?></option>
                                        <option value="hours"><?= _l('hours'); ?></option>
                                        <option value="days"><?= _l('days'); ?></option>
                                        <option value="weeks"><?= _l('weeks'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row">
                            <div class="col-md-12">
                                <span class="font-medium pleft5"><?= _l('appointment_client_notes'); ?></span>
                            </div>
                            <div class="col-md-12 mtop8">
                                <textarea name="notes" class="form-control" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default close_btn"
                    data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?= _l('submit'); ?></button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<script>
    var appointmentData = <?= json_encode([
                                'services' => $services,
                                'staff_members' => $staff_members,
                                'lang' => [
                                    'service_selection_required' => _l('service_selection_required'),
                                    'provider_selection_required' => _l('appointment_select_provider'),
                                    'no_providers_available' => _l('service_no_providers'),
                                ]
                            ]); ?>;

    function toggleStaffOnlyFields() {
        var source = document.getElementById('rel_type').value;
        var clientFields = document.querySelectorAll('.client-fields');
        var staffOnlyFields = document.querySelectorAll('.staffonly-hide');
        if (source === 'internal_staff') {
            clientFields.forEach(f => f.style.display = 'none');
            staffOnlyFields.forEach(f => f.style.display = 'none');
        } else {
            clientFields.forEach(f => f.style.display = '');
            staffOnlyFields.forEach(f => f.style.display = '');
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        toggleStaffOnlyFields();
        document.getElementById('rel_type').addEventListener('change', toggleStaffOnlyFields);
    });
</script>

<?php require('modules/appointly/assets/js/modals/create_js.php'); ?>
