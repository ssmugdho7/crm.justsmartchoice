    <div class="modal fade" id="appointmentModal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><?php echo _l('appointment_edit_appointment'); ?></h4>
                </div>

                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 tw-px-4 tw-pt-2">
                    <div class="tw-flex tw-items-center">
                        <?php if (function_exists('appointlyGoogleAuth') && appointlyGoogleAuth()) { ?>
                            <div class="tw-flex tw-items-center tw-gap-3">
                                <?php if (isset($history['google_event_id']) && $history['google_event_id']) { ?>
                                    <div class="tw-flex tw-items-center label label-info">
                                        <?php if (isset($history['google_calendar_link']) && $history['google_calendar_link']) : ?>
                                            <a href="<?= $history['google_calendar_link']; ?>" target="_blank" data-toggle="tooltip"
                                                title="<?= _l('appointments_added_to_google_calendar'); ?> - <?= _l('appointment_open_link'); ?>"
                                                class="tw-flex tw-items-center tw-gap-1 tw-px-3 tw-py-1 tw-rounded-full tw-bg-[#4285f4]/10 hover:tw-bg-[#4285f4]/20 tw-text-[#4285f4]">
                                                <i class="fa-brands fa-google"></i>
                                                <span class="tw-text-sm">Google Calendar</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php } ?>

                                <?php if (
                                    isset($history['outlook_event_id'], $history['outlook_added_by_id'])
                                    && $history['outlook_event_id']
                                    && $history['outlook_added_by_id']
                                    == get_staff_user_id()
                                ) : ?>
                                    <div class="tw-flex tw-items-center label label-info">
                                        <?php if (isset($history['outlook_calendar_link']) && $history['outlook_calendar_link']) : ?>
                                            <a href="<?= $history['outlook_calendar_link']; ?>" target="_blank" data-toggle="tooltip"
                                                title="<?= _l('appointment_is_added_to_outlook'); ?> - <?= _l('appointment_open_link'); ?>"
                                                class="tw-flex tw-items-center tw-gap-1 tw-px-3 tw-py-1 tw-rounded-full tw-bg-[#00a1f1]/10 hover:tw-bg-[#00a1f1]/20 tw-text-[#00a1f1]">
                                                <i class="fa fa-envelope"></i>
                                                <span class="tw-text-sm">Outlook Calendar</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if (isset($history['source'])) : ?>
                        <?php
                        $sourceClass = '';
                        $sourceIcon = '';
                        $sourceLabel = '';

                        switch ($history['source']) {
                            case 'internal':
                                $sourceClass = 'badge tw-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full tw-bg';
                                $sourceIcon = '<i class="fa-solid fa-user-tie tw-mr-2"></i>';
                                $sourceLabel = _l('appointment_source_internal_client');
                                break;
                            case 'external':
                                $sourceClass = 'badge tw-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full';
                                $sourceIcon = '<i class="fa-solid fa-globe tw-mr-2"></i>';
                                $sourceLabel = _l('appointment_source_external_contact');
                                break;
                            case 'lead_related':
                                $sourceClass = 'badge tw-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full';
                                $sourceIcon = '<i class="fa-solid fa-lightbulb tw-mr-2"></i>';
                                $sourceLabel = _l('appointment_source_lead');
                                break;
                            case 'internal_staff':
                                $sourceClass = 'badge tw-flex tw-items-center tw-px-3 tw-py-1 tw-rounded-full';
                                $sourceIcon = '<i class="fa-solid fa-user-group tw-mr-2"></i>';
                                $sourceLabel = _l('appointment_source_internal_staff');
                                break;
                        }
                        ?>
                        <div class="<?php echo $sourceClass; ?> tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-flex tw-items-center tw-shadow-sm"
                            style="<?php if (!$history['google_event_id'] || !$history['outlook_event_id']) { ?> margin-bottom:-44px; <?php } ?>">
                            <?php echo $sourceIcon . $sourceLabel; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <input type="hidden" id="ms-access-token" value="" />
                <input type="hidden" id="ms-outlook-event-id" value="<?= $history['outlook_event_id']
                                                                            ?? ''; ?>" />

                <?php echo form_open('appointly/appointments/update', ['id' => 'appointment-form']); ?>
                <div class="modal-body">
                    <input type="hidden" name="source" value="<?= $history['source'] ??
                                                                    ''; ?>">
                    <input type="hidden" name="google_event_id" value="<?= $history['google_event_id']
                                                                            ?? ''; ?>">
                    <input type="hidden" name="google_added_by_id" value="<?= $history['google_added_by_id']
                                                                                ?? ''; ?>">
                    <input type="hidden" name="google" value="1">

                    <div class="row">
                        <input type="text" hidden value="<?= $history['appointment_id']
                                                                ?? ''; ?>" name="appointment_id">
                        <input type="text" hidden value="<?= $history['source'] ?? ''; ?>" name="source">
                        <?php if (
                            isset($history['source'], $history['email'])
                            && $history['source'] == 'lead_related'
                        ) : ?>
                            <input type="text" hidden value="<?= $history['email']; ?>" name="email">
                        <?php endif; ?>
                        <input type="text" hidden value="<?= $history['approved'] ??
                                                                ''; ?>" name="approved">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="subject"><?= _l('appointment_subject'); ?></label>
                                <input type="text" class="form-control" name="subject" id="subject" value="<?= $history['subject'] ?? ''; ?>">
                            </div>

                            <!-- Service Selection - Hide for internal staff meetings -->
                            <?php if (isset($history['source']) && $history['source'] !== 'internal_staff'): ?>
                                <div class="form-group staffonly-hide" id="service_field">
                                    <label for="service_id"><?= _l('service'); ?></label>
                                    <select name="service_id" id="service_id" class="selectpicker" data-width="100%" data-live-search="true">
                                        <option value=""><?= _l('dropdown_non_selected_tex'); ?></option>
                                        <?php foreach ($services as $service) { ?>
                                            <option value="<?= $service['id']; ?>"
                                                data-content="<span class='service-option' style='border-left: 3px solid <?= $service['color']; ?>; padding-left: 5px;'>
                                                <?= $service['name']; ?>
                                                <small class='text-muted'>(<?= $service['duration']; ?> <?= _l('minutes'); ?>)</small>
                                            </span>"
                                                <?= (isset($history['service_id']) && $history['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Provider Selection - Hide for internal staff meetings -->
                                <div class="form-group staffonly-hide" id="provider_field">
                                    <label for="provider_id"><?= _l('appointment_provider'); ?></label>
                                    <select name="provider_id" id="provider_id" class="selectpicker" data-width="100%" data-live-search="true">
                                        <option value=""><?= _l('appointment_select_provider'); ?></option>
                                        <?php
                                        // Populate available providers
                                        foreach ($staff_members as $staff) {
                                            $selected = (isset($history['provider_id']) && $history['provider_id'] == $staff['staffid']) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $staff['staffid']; ?>" <?= $selected ?>>
                                                <?= $staff['firstname'] . ' ' . $staff['lastname']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="description"><?= _l('appointment_description'); ?></label>
                                <textarea name="description" class="form-control" id="description" rows="5"><?= $history['description'] ?? ''; ?></textarea>
                            </div>

                            <?php if (isset($history['source'])): ?>
                                <div class="form-group">
                                    <label><?= _l('appointment_related'); ?></label>
                                    <?php if ($history['source'] == 'lead_related'): ?>
                                        <div class="tw-flex tw-items-center tw-mb-3">
                                            <span class="tw-bg-amber-50 tw-px-3 tw-py-2 tw-rounded-md tw-text-amber-700 tw-border tw-border-amber-200 tw-flex tw-items-center tw-shadow-sm tw-w-full">
                                                <i class="fa-solid fa-lightbulb tw-mr-2"></i>
                                                <?= _l('appointment_source_lead'); ?>
                                            </span>
                                        </div>
                                        <div id="rel_id_wrapper" class="form-group select-placeholder">
                                            <label for="rel_id" class="control-label"><?= _l('appointment_source_lead'); ?></label>
                                            <select name="contact_id" id="rel_id" class="ajax-search" data-width="100%" data-live-search="true">
                                                <?php
                                                if (isset($history['contact_id'])):
                                                    $CI = &get_instance();
                                                    $CI->load->model('leads_model');
                                                    $lead = $CI->leads_model->get($history['contact_id']);
                                                    if ($lead) {
                                                        echo '<option value="' . $lead->id . '" selected>' . $lead->name . '</option>';
                                                    }
                                                endif;
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group mtop15">
                                            <label><?= _l('client_email'); ?></label>
                                            <input type="text" class="form-control" id="lead_email" name="email" value="<?= $history['email'] ?? ''; ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label><?= _l('client_phonenumber'); ?></label>
                                            <input type="text" class="form-control" id="lead_phone" name="phone" value="<?= $history['phone'] ?? ''; ?>" readonly>
                                        </div>
                                    <?php elseif ($history['source'] == 'internal'): ?>
                                        <div class="tw-flex tw-items-center tw-mb-3">
                                            <span class="tw-bg-blue-50 tw-px-3 tw-py-2 tw-rounded-md tw-text-blue-700 tw-border tw-border-blue-200 tw-flex tw-items-center tw-shadow-sm tw-w-full">
                                                <i class="fa-solid fa-user-tie tw-mr-2"></i>
                                                <?= _l('appointment_source_internal_client'); ?>
                                            </span>
                                        </div>
                                        <div id="rel_id_wrapper" class="form-group select-placeholder">
                                            <label for="rel_id" class="control-label"><?= _l('appointment_source_internal_client'); ?></label>
                                            <select name="contact_id" id="rel_id" class="ajax-search" data-width="100%" data-live-search="true">
                                                <?php
                                                if (isset($history['contact_id']) && $history['contact_id'] !== ''):
                                                    $CI = &get_instance();
                                                    $CI->load->model('clients_model');
                                                    $contact = $CI->clients_model->get_contact($history['contact_id']);
                                                    if ($contact):
                                                        $contact_name = $contact->firstname . ' ' . $contact->lastname;
                                                        $company = '';
                                                        if ($contact->userid) {
                                                            $client = $CI->clients_model->get($contact->userid);
                                                            if ($client) {
                                                                $company = ' (' . $client->company . ')';
                                                            }
                                                        }
                                                ?>
                                                        <option value="<?= $history['contact_id']; ?>" selected>
                                                            <?= $contact_name . $company; ?>
                                                        </option>
                                                <?php endif;
                                                endif; ?>
                                            </select>
                                        </div>
                                        <div class="form-group mtop15">
                                            <label><?= _l('client_email'); ?></label>
                                            <input type="text" class="form-control" id="contact_email" name="email" value="<?= isset($contact) ? $contact->email : (isset($history['email']) ? $history['email'] : ''); ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label><?= _l('client_phonenumber'); ?></label>
                                            <input type="text" class="form-control" id="contact_phone" name="phone" value="<?= isset($contact) ? $contact->phonenumber : (isset($history['phone']) ? $history['phone'] : ''); ?>" readonly>
                                        </div>
                                    <?php elseif ($history['source'] == 'external'): ?>
                                        <div class="tw-flex tw-items-center tw-mb-3">
                                            <span class="tw-bg-purple-50 tw-px-3 tw-py-2 tw-rounded-md tw-text-purple-700 tw-border tw-border-purple-200 tw-flex tw-items-center tw-shadow-sm tw-w-full">
                                                <i class="fa-solid fa-globe tw-mr-2"></i>
                                                <?= _l('appointment_source_external_contact'); ?>
                                            </span>
                                        </div>
                                        <div class="form-group mtop15">
                                            <label><?= _l('client_firstname'); ?></label>
                                            <input type="text" class="form-control" name="name" value="<?= $history['name'] ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label><?= _l('client_email'); ?></label>
                                            <input type="text" class="form-control" name="email" value="<?= $history['email'] ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label><?= _l('client_phonenumber'); ?></label>
                                            <input type="text" class="form-control" name="phone" value="<?= $history['phone'] ?? ''; ?>">
                                        </div>
                                    <?php elseif ($history['source'] == 'internal_staff'): ?>
                                        <div class="tw-flex tw-items-center tw-mb-3">
                                            <span class="tw-bg-green-50 tw-px-3 tw-py-2 tw-rounded-md tw-text-green-700 tw-border tw-border-green-200 tw-flex tw-items-center tw-shadow-sm tw-w-full">
                                                <i class="fa-solid fa-user-group tw-mr-2"></i>
                                                <?= _l('appointment_staff_only'); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <?php
                                if (isset($history['date']) && isset($history['start_hour'])) {
                                    echo render_datetime_input(
                                        'date',
                                        'appointment_date_and_time',
                                        _dt($history['date'] . ' ' . $history['start_hour']),
                                        ["readonly" => "readonly"],
                                        [],
                                        '',
                                        'appointment-date'
                                    );
                                } else {
                                    echo render_datetime_input(
                                        'date',
                                        'appointment_date_and_time',
                                        '',
                                        ["readonly" => "readonly"],
                                        [],
                                        '',
                                        'appointment-date'
                                    );
                                }
                                ?>
                            </div>

                            <div class="form-group">
                                <label for="address"><?= _l('appointment_meeting_location') . ' ' . _l('appointment_optional'); ?></label>
                                <input type="text" class="form-control" value="<?= $history['address']                                                                              ??
                                                                                    ''; ?>" name="address" id="address">
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
                                                        <?= (isset($history['timezone']) && $history['timezone'] == $timezone) ||
                                                            (!isset($history['timezone']) && get_option('default_timezone') == $timezone) ?
                                                            'selected' : ''; ?>>
                                                        <?= $timezone; ?>
                                                    </option>
                                                <?php } ?>
                                            </optgroup>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <?php if (isset($staff_members)): ?>
                                <div class="form-group">
                                    <?php
                                    $selected_staff = $history['selected_staff'] ??
                                        [];
                                    echo render_select('attendees[]', $staff_members, ['staffid', ['firstname', 'lastname']], 'appointment_select_attendees', $selected_staff, ['multiple' => true], [], '', '', false);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php
                            if (isset($history['appointment_id']) && get_custom_fields('appointly')) {
                                echo "<hr>";
                                echo render_custom_fields('appointly', $history['appointment_id']);
                                echo "<hr>";
                            }
                            ?>

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12 mbot5">
                                        <?= _l('appointment_modal_notification_info'); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="checkbox">
                                            <input type="checkbox" name="by_sms" id="by_sms" <?= (isset($history['by_sms']) && $history['by_sms'] == 1) ? 'checked' : '' ?>>
                                            <label for="by_sms"><?= _l('appoontment_sms_notification'); ?></label>
                                        </div>
                                        <div class="checkbox">
                                            <input type="checkbox" name="by_email" id="by_email" <?= (isset($history['by_email']) && $history['by_email'] == 1) ? 'checked' : '' ?>>
                                            <label for="by_email"><?= _l('appointment_email_notification_text'); ?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group appointment-reminder<?= (isset($history['by_sms']) && isset($history['by_email']) && $history['by_sms'] == null && $history['by_email'] == null) ? ' hide' : '' ?>">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="reminder_before"><?php echo _l('event_notification'); ?></label>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="reminder_before" value="<?php echo isset($history['reminder_before']) ? $history['reminder_before'] : ''; ?>" id="reminder_before">
                                            <span class="input-group-addon">
                                                <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('reminder_notification_placeholder'); ?>"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <select name="reminder_before_type" id="reminder_before_type" class="selectpicker" data-width="100%">
                                            <option value="minutes" <?= (isset($history['reminder_before_type']) && $history['reminder_before_type'] == 'minutes') ? ' selected' : '' ?>><?php echo _l('minutes'); ?></option>
                                            <option value="hours" <?= (isset($history['reminder_before_type']) && $history['reminder_before_type'] == 'hours') ? ' selected' : '' ?>><?php echo _l('hours'); ?></option>
                                            <option value="days" <?= (isset($history['reminder_before_type']) && $history['reminder_before_type'] == 'days') ? ' selected' : '' ?>><?php echo _l('days'); ?></option>
                                            <option value="weeks" <?= (isset($history['reminder_before_type']) && $history['reminder_before_type'] == 'weeks') ? ' selected' : '' ?>><?php echo _l('weeks'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <span class="font-medium pleft5"><?= _l('appointment_client_notes'); ?></span>
                                </div>
                                <div class="col-md-12 mtop8">
                                    <textarea name="notes" class="form-control" rows="5"><?= $history['notes'] ?? ''; ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="tw-flex tw-gap-2 tw-items-center tw-justify-between">
                        <div>
                            <?php if (
                                isset($history) &&
                                (! isset($history['google_event_id'], $history['google_added_by_id'])
                                    || $history['google_added_by_id']
                                    != get_staff_user_id())
                                && appointlyGoogleAuth()
                            ) { ?>
                                <button type="button"
                                    data-toggle="tooltip"
                                    title="<?= _l('appointment_google_not_added_yet'); ?>"
                                    onclick="addEventToGoogleCalendar(this)"
                                    class="btn btn-primary">
                                    <?= _l('appointment_add_to_calendar'); ?>&nbsp;
                                    <i class="fa-brands fa-google" aria-hidden="true"></i>
                                </button>
                            <?php } ?>

                            <?php if (
                                isset($history) &&
                                (! isset($history['outlook_event_id'], $history['outlook_added_by_id'])
                                    || $history['outlook_added_by_id']
                                    != get_staff_user_id())
                            ) { ?>
                                <button type="button"
                                    data-toggle="tooltip"
                                    id="addToOutlookBtn"
                                    title="<?= _l('appointment_outlook_not_added_yet'); ?>"
                                    onclick="addEventToOutlookCalendar(this, '<?= isset($history['appointment_id']) ? $history['appointment_id'] : ''; ?>')"
                                    class="btn btn-primary">
                                    <?= _l('appointment_add_to_outlook'); ?>&nbsp;
                                    <i class="fa fa-envelope" aria-hidden="true"></i>
                                </button>
                            <?php } ?>
                        </div>
                        <div>
                            <button type="button" class="btn btn-default close_btn" data-dismiss="modal"><?php echo _l('close'); ?></button>
                            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>

    <?php require('modules/appointly/assets/js/modals/update_js.php'); ?>
