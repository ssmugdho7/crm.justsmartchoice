<?php

defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="tw-p-4 tw-mt-3 tw-mb-3 sm:tw-p-6 lg:tw-p-8 tw-w-full">
                    <div class="tw-mb-4">
                        <a href="<?= admin_url('appointly/appointments'); ?>"
                            class="btn btn-default tw-px-2 hover:tw-bg-neutral-100">
                            <i class="fa fa-arrow-left tw-mr-2"></i>
                            <?= _l('appointment_back_to_appointments'); ?>
                        </a>

                        <!-- Status Dropdown -->
                        <?php
                        // Status styling
                        $status_icon = '';
                        $status_text = '';

                        switch ($appointment['status']) {
                            case 'cancelled':
                                $status_icon = 'fa fa-ban';
                                $status_text = _l('appointment_status_cancelled');
                                break;
                            case 'no-show':
                                $status_icon = 'fa fa-user-slash';
                                $status_text = _l('appointment_status_no-show');
                                break;
                            case 'in-progress':
                                $status_icon = 'fa fa-clock';
                                $status_text = _l('appointment_status_in-progress');
                                break;
                            case 'pending':
                                $status_icon = 'fa fa-hourglass-half';
                                $status_text = _l('appointment_status_pending');
                                break;
                            case 'completed':
                                $status_icon = 'fa fa-check-circle';
                                $status_text = _l('appointment_status_completed');
                                break;
                            default:
                                $status_icon = 'fa fa-clock';
                                $status_text = _l('appointment_status_pending');
                        }
                        ?>

                        <?php
                        if (staff_can('edit', 'appointments') && $appointment['status'] != 'completed' && $appointment['status'] != 'cancelled'): ?>
                            <div class="dropdown tw-inline-block">
                                <button class="btn btn-default tw-px-2 hover:tw-bg-neutral-100 dropdown-toggle" type="button" data-toggle="dropdown">
                                    <i class="<?= $status_icon ?> tw-mr-1 
                                        <?php
                                        switch ($appointment['status']) {
                                            case 'in-progress':
                                                echo 'tw-text-blue-600';
                                                break;
                                            case 'pending':
                                                echo 'tw-text-amber-600';
                                                break;
                                            case 'completed':
                                                echo 'tw-text-green-600';
                                                break;
                                            case 'cancelled':
                                                echo 'tw-text-red-600';
                                                break;
                                            case 'no-show':
                                                echo 'tw-text-gray-600';
                                                break;
                                            default:
                                                echo 'tw-text-amber-600';
                                        }
                                        ?>"></i> <?= $status_text ?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <?php
                                    if ($appointment['status'] != 'in-progress'): ?>
                                        <li><a href="#" onclick="changeStatusFromDropdown('in-progress');return false;"><i class="fa fa-clock tw-mr-2"></i><?= _l('appointment_status_in-progress'); ?></a></li><?php
                                                                                                                                                                                                            endif; ?>
                                    <?php
                                    if ($appointment['status'] != 'pending'): ?>
                                        <li><a href="#" onclick="changeStatusFromDropdown('pending');return false;"><i class="fa fa-hourglass-half tw-mr-2"></i><?= _l('appointment_status_pending'); ?></a></li><?php
                                                                                                                                                                                                                endif; ?>
                                    <?php
                                    if ($appointment['status'] != 'completed'): ?>
                                        <li><a href="#" onclick="changeStatusFromDropdown('completed');return false;"><i class="fa fa-check-circle tw-mr-2"></i><?= _l('appointment_status_completed'); ?></a></li><?php
                                                                                                                                                                                                                endif; ?>
                                    <?php
                                    if ($appointment['status'] != 'cancelled'): ?>
                                        <li><a href="#" onclick="changeStatusFromDropdown('cancelled');return false;"><i class="fa fa-ban tw-mr-2"></i><?= _l('appointment_status_cancelled'); ?></a></li><?php
                                                                                                                                                                                                        endif; ?>
                                    <?php
                                    if ($appointment['status'] != 'no-show'): ?>
                                        <li><a href="#" onclick="changeStatusFromDropdown('no-show');return false;"><i class="fa fa-user-slash tw-mr-2"></i><?= _l('appointment_status_no-show'); ?></a></li><?php
                                                                                                                                                                                                            endif; ?>
                                </ul>
                            </div>
                        <?php
                        else: ?>
                            <button class="btn btn-default tw-px-2" disabled>
                                <i class="<?= $status_icon ?> tw-mr-1 
                                    <?php
                                    switch ($appointment['status']) {
                                        case 'in-progress':
                                            echo 'tw-text-blue-600';
                                            break;
                                        case 'pending':
                                            echo 'tw-text-amber-600';
                                            break;
                                        case 'completed':
                                            echo 'tw-text-green-600';
                                            break;
                                        case 'cancelled':
                                            echo 'tw-text-red-600';
                                            break;
                                        case 'no-show':
                                            echo 'tw-text-gray-600';
                                            break;
                                        default:
                                            echo 'tw-text-amber-600';
                                    }
                                    ?>"></i>
                                <span class="<?= $appointment['status'] == 'cancelled' ? 'tw-text-danger-700' : ''; ?>"><?= $status_text ?></span>
                            </button>
                        <?php
                        endif; ?>

                        <?php
                        if (staff_can('edit', 'appointments')): ?>
                            <a href="<?= admin_url('appointly/appointments/update_page/' . $appointment['id']); ?>"
                                class="btn btn-default tw-px-2 hover:tw-bg-neutral-100">
                                <i class="fa fa-edit tw-mx-1"></i>
                                <?= _l('edit'); ?>
                            </a>
                        <?php
                        endif; ?>
                    </div>

                    <script>
                        function changeStatusFromDropdown(newStatus) {
                            var url = window.location.search;
                            const params = new URLSearchParams(window.location.search);
                            const id = params.get("appointment_id");
                            console.log('Changing status for appointment ID:', id, 'to status:', newStatus);
                            $.ajax({
                                url: "change_appointment_status",
                                type: "POST",
                                dataType: "json",
                                data: {
                                    id: id,
                                    status: newStatus
                                },
                                success: function(response) {
                                    if (response && response.success === true) {
                                        alert_float("success", response.message || "Status changed");
                                        location.reload();
                                    } else {
                                        var message = response && response.message ? response.message : "Status change failed";
                                        alert_float("warning", message);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Status change error:', error);
                                    alert_float("danger", "Error changing status: " + error);
                                }
                            });
                        }
                    </script>



                    <!-- Main Content -->
                    <div class="tw-bg-white tw-rounded-md tw-shadow-sm tw-border tw-border-solid tw-border-neutral-300/80">
                        <div class="tw-p-6 tw-pb-0" style="margin-top: -11px;">
                            <div class="horizontal-tabs">
                                <!-- Action Buttons -->
                                <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2" style="float:right;">
                                    <!-- Enhanced Video Meeting Button with Dropdown -->
                                    <?php if (!empty($appointment['google_meet_link'])): ?>
                                        <div class="btn-group">
                                            <a href="<?= $appointment['google_meet_link'] ?>" target="_blank" class="btn btn-success btn-sm">
                                                <i class="fa fa-video tw-mr-1"></i>
                                                <?= _l('appointment_google_meet_quick_join') ?>
                                            </a>
                                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu tw-w-56" role="menu">
                                                <li>
                                                    <a href="<?= $appointment['google_meet_link']; ?>" target="_blank">
                                                        <i class="fa fa-external-link tw-mr-2"></i>
                                                        <?= _l('appointment_google_meet_join_before_start'); ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" onclick="copyGoogleMeetLink('<?= addslashes($appointment['google_meet_link']) ?>'); return false;">
                                                        <i class="fa fa-copy tw-mr-2"></i>
                                                        <?= _l('appointment_google_meet_copy_link'); ?>
                                                    </a>
                                                </li>
                                                <li class="divider"></li>

                                                <li>
                                                    <a href="#" data-toggle="modal" data-target="#googleMeetDetailsModal">
                                                        <i class="fa fa-info-circle tw-mr-2"></i>
                                                        <?= _l('appointment_google_meet_meeting_details'); ?>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php
                                    if (! empty($appointment['google_event_id']) || ! empty($appointment['outlook_event_id'])): ?>
                                        <div class="tw-flex tw-gap-2">
                                            <?php
                                            if (! empty($appointment['google_event_id'])): ?>
                                                <a href="<?= $appointment['google_calendar_link'] ?>" target="_blank"
                                                    class="btn btn-default btn-sm"
                                                    data-toggle="tooltip" title="<?= _l('appointment_view_in_calendar'); ?>">
                                                    <i class="fab fa-google"></i>
                                                </a>
                                            <?php
                                            endif; ?>

                                            <?php
                                            if (! empty($appointment['outlook_event_id'])): ?>
                                                <a href="<?= $appointment['outlook_calendar_link'] ?>" target="_blank"
                                                    class="btn btn-default btn-sm"
                                                    data-toggle="tooltip" title="<?= _l('appointments_outlook_view_in_calendar'); ?>">
                                                    <i class="fab fa-microsoft"></i>
                                                </a>
                                            <?php
                                            endif; ?>
                                        </div>
                                    <?php
                                    endif; ?>

                                    <a data-toggle="tooltip"
                                        title="<?= _l('appointment_public_url') ?>"
                                        href="<?= $appointment['public_url'] ?>"
                                        target="_blank"
                                        class="btn btn-default btn-sm">
                                        <i class="fa fa-external-link"></i>
                                    </a>

                                    <a href="<?= admin_url('appointly/appointments/download_ics/' . $appointment['id']); ?>"
                                        class="btn btn-default btn-sm"
                                        data-toggle="tooltip"
                                        title="<?= _l('appointment_download_ics_tooltip'); ?>">
                                        <i class="fa fa-calendar-plus"></i>
                                    </a>

                                    <button type="button"
                                        class="btn btn-default btn-sm"
                                        onclick="sendAppointmentReminders()"
                                        data-toggle="tooltip"
                                        title="<?= _l('appointment_send_early_reminders_label'); ?>">
                                        <i class="fa fa-bell"></i>
                                    </button>
                                </div>
                                <ul class="nav nav-tabs nav-tabs-horizontal">
                                    <li class="active">
                                        <a href="#tab_info" data-toggle="tab">
                                            <i class="fas fa-info-circle tw-mr-2"></i><?= _l('appointment_general_details'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#tab_session_overview" data-toggle="tab">
                                            <i class="fa-solid fa-clipboard-list tw-mr-2"></i><?= _l('appointment_session_overview'); ?>
                                        </a>
                                    </li>
                                    <li class="tw-cursor-pointer tw-text-neutral-700 tw-mt-2.5 tw-ml-2" title="This is planned for next update, will wait for suggestion from clients."
                                        data-toggle="tooltip">
                                        <i class="fa-regular fa-folder tw-mr-2"></i><?= _l('appointment_files'); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-content tw-p-6 tw-pt-0">
                            <div class="tab-pane active" id="tab_info">
                                <?php
                                $gridClass = 'tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6'; ?>
                                <?php
                                if ($appointment['source'] == 'internal_staff'): ?>
                                    <?php
                                    $gridClass = 'tw-grid tw-grid-cols-1 tw-gap-6'; ?>
                                <?php
                                endif; ?>
                                <div class="<?= $gridClass; ?>">
                                    <div class="tw-space-y-5">
                                        <!-- Service -->
                                        <?php
                                        if (isset($appointment['service'])): ?>
                                            <div id="service-section" class="tw-flex tw-items-center tw-gap-3 tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4">
                                                <div class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-bg-neutral-100 tw-text-neutral-700">
                                                    <i class="fa-solid fa-briefcase"></i>
                                                </div>
                                                <div>
                                                    <div class=" tw-text-neutral-700">
                                                        <?= _l('appointly_service_name_label'); ?>
                                                        <a href="<?= admin_url('appointly/services/service/' . $appointment['service']->id); ?>"
                                                            target="_blank"
                                                            data-toggle="tooltip"
                                                            title="<?= _l('appointly_service_details'); ?>"
                                                            class="tw-ml-2 tw-text-neutral-700 hover:tw-text-neutral-700">
                                                            <i class="fa fa-external-link-square"></i>
                                                        </a>
                                                    </div>
                                                    <div class="">
                                                        <span style="color: <?= $appointment['service']->color; ?>"><?= $appointment['service']->name; ?></span>
                                                        <span class="tw-text-neutral-700">
                                                            (<?= $appointment['service']->duration; ?> <?= _l('appointly_duration_minutes'); ?>)
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        endif; ?>

                                        <!-- Provider -->
                                        <?php
                                        if (isset($appointment['provider'])): ?>
                                            <div class="tw-flex tw-items-center tw-gap-3 tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4">
                                                <div class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-bg-neutral-100 tw-text-neutral-700">
                                                    <i class="fa-solid fa-user-tie"></i>
                                                </div>
                                                <div>
                                                    <div class=" tw-text-neutral-700"><?= _l('appointment_provider'); ?></div>
                                                    <div class="">
                                                        <a href="<?= admin_url('staff/profile/' . $appointment['provider']['staffid']); ?>">
                                                            <?= $appointment['provider']['full_name']; ?>
                                                        </a>
                                                        <?php
                                                        if (! empty($appointment['provider']['email'])): ?>
                                                            <div class=" tw-text-neutral-600">
                                                                <a href="mailto:<?= $appointment['provider']['email']; ?>"
                                                                    class="tw-text-neutral-700 hover:tw-text-neutral-600">
                                                                    <?= $appointment['provider']['email']; ?>
                                                                </a>
                                                            </div>
                                                        <?php
                                                        endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        endif; ?>

                                        <!-- Initiated by -->
                                        <div class="tw-flex tw-items-center tw-gap-3 tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4">
                                            <div class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-bg-neutral-100 tw-text-neutral-700">
                                                <i class="fa-solid fa-user-tie"></i>
                                            </div>
                                            <div>
                                                <div class=" tw-text-neutral-700"><?= _l('appointment_initiated_by'); ?></div>
                                                <div class="">
                                                    <a href="<?= admin_url('staff/profile/' . $appointment['created_by']); ?>">
                                                        <?= ($appointment['created_by']) ? get_staff_full_name($appointment['created_by']) : $appointment['name']; ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact Info with Source Badge -->
                                        <?php
                                        if ($appointment['source'] !== 'internal_staff'): ?>
                                            <div class="tw-flex tw-items-center tw-gap-3 tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4">
                                                <div class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-bg-neutral-100 tw-text-neutral-700">
                                                    <i class="fa-solid fa-address-card"></i>
                                                </div>
                                                <div class="tw-flex-1">
                                                    <div class="tw-flex tw-justify-between tw-items-center">
                                                        <div class="tw-text-neutral-700"><?= _l('appointment_contact'); ?></div>
                                                        <?php
                                                        // Source styling
                                                        $source_text = '';
                                                        $source_icon = '';
                                                        $source_bg_class = '';
                                                        $source_text_class = '';

                                                        if ($appointment['source'] == 'lead_related') {
                                                            $source_text = _l('appointment_lead_related');
                                                            $source_icon = 'fa fa-bullseye';
                                                            $source_bg_class = 'tw-bg-warning-100';
                                                            $source_text_class = 'tw-text-warning-800';
                                                        } elseif ($appointment['source'] == 'internal') {
                                                            $source_text = _l('appointment_source_internal');
                                                            $source_icon = 'fa fa-building';
                                                            $source_bg_class = 'tw-bg-primary-100';
                                                            $source_text_class = 'tw-text-primary-800';
                                                        } elseif ($appointment['source'] == 'external') {
                                                            $source_text = _l('appointment_source_external_text');
                                                            $source_icon = 'fa fa-globe';
                                                            $source_bg_class = 'tw-bg-success-100';
                                                            $source_text_class = 'tw-text-success-800';
                                                        } elseif ($appointment['source'] == 'internal_staff') {
                                                            $source_text = _l('appointment_internal_staff');
                                                            $source_icon = 'fa fa-users';
                                                            $source_bg_class = 'tw-bg-info-100';
                                                            $source_text_class = 'tw-text-info-800';
                                                        } else {
                                                            $source_text = ucfirst(str_replace('_', ' ', $appointment['source']));
                                                            $source_icon = 'fa fa-question-circle';
                                                            $source_bg_class = 'tw-bg-neutral-100';
                                                            $source_text_class = 'tw-text-neutral-800';
                                                        }
                                                        ?>
                                                        <span class="tw-inline-flex tw-items-center <?= $source_bg_class ?> <?= $source_text_class ?> tw-text-sm tw-font-medium tw-px-3 tw-py-1 tw-rounded-full">
                                                            <i class="<?= $source_icon ?> tw-mr-1"></i> <?= $source_text ?>
                                                        </span>
                                                    </div>
                                                    <div class="tw-space-y-1">
                                                        <div class=""><?= $appointment['name']; ?></div>
                                                        <a href="mailto:<?= $appointment['email']; ?>"
                                                            class="tw-text-primary-600 hover:tw-text-primary-700  tw-flex tw-items-center tw-gap-1">
                                                            <i class="fa-regular fa-envelope"></i>
                                                            <?= $appointment['email']; ?>
                                                        </a>
                                                        <?php
                                                        if ($appointment['phone']): ?>
                                                            <a href="tel:<?= $appointment['phone']; ?>"
                                                                class="tw-text-primary-600 hover:tw-text-primary-700  tw-flex tw-items-center tw-gap-1">
                                                                <i class="fa-solid fa-phone"></i>
                                                                <?= $appointment['phone']; ?>
                                                            </a>
                                                        <?php
                                                        endif; ?>

                                                        <?php
                                                        // Display client's full address if available
                                                        if (!empty($appointment['details']['full_address'])): ?>
                                                            <div class="tw-text-sm tw-text-neutral-600 tw-flex tw-items-start tw-gap-1">
                                                                <i class="fa-solid fa-map-marker-alt tw-mt-0.5"></i>
                                                                <span><?= $appointment['details']['full_address']; ?></span>
                                                            </div>
                                                        <?php
                                                        endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        endif; ?>
                                    </div>

                                    <!-- Right Column -->
                                    <div class="tw-space-y-5">
                                        <!-- Meeting Purpose (always at the top of right column) -->
                                        <div class="tw-flex tw-items-center tw-gap-3 tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4 tw-mb-4">
                                            <div class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-bg-neutral-100 tw-text-neutral-700">
                                                <i class="fa-solid fa-bullseye"></i>
                                            </div>
                                            <div>
                                                <div class=" tw-text-neutral-700"><?= _l('appointment_subject') ?></div>
                                                <div class=""><?= e($appointment['subject']) ?></div>
                                            </div>
                                        </div>

                                        <!-- Cancellation Notes -->
                                        <?php
                                        if (! empty($appointment['cancel_notes'])): ?>
                                            <div class="tw-flex tw-items-center tw-gap-3 tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4 tw-mb-4 tw-bg-red-50">
                                                <div class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-bg-red-100 tw-text-red-700">
                                                    <i class="fa-solid fa-ban"></i>
                                                </div>
                                                <div class="tw-flex-1">
                                                    <div class="tw-text-danger-700 tw-font-medium"><?= _l('appointment_cancellation_requested'); ?></div>
                                                    <div class="tw-text-danger-600"><?= htmlspecialchars($appointment['cancel_notes']); ?></div>
                                                </div>
                                            </div>
                                        <?php
                                        endif; ?>

                                        <!-- Date & Time -->
                                        <div class="tw-flex tw-items-center tw-gap-3 tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4 tw-mb-4">
                                            <div class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-bg-neutral-100 tw-text-neutral-700">
                                                <i class="fa-regular fa-calendar-check"></i>
                                            </div>
                                            <div class="tw-w-full">
                                                <div class=" tw-text-neutral-700 tw-mb-3 tw-flex tw-items-center tw-justify-between">
                                                    <span><?= _l('appointment_date_time'); ?></span>
                                                </div>

                                                <div class="tw-flex tw-flex-wrap tw-gap-3">
                                                    <!-- Date -->
                                                    <div class="tw-flex tw-items-center tw-gap-2">
                                                        <div class="tw-bg-primary-50 tw-text-primary-700 tw-px-3 tw-py-2 tw-rounded-md tw-font-semibold tw-flex tw-items-center tw-shadow-sm">
                                                            <i class="fa-regular fa-calendar tw-mr-2"></i>
                                                            <?= _d($appointment['date']); ?>
                                                        </div>
                                                    </div>

                                                    <!-- Time -->
                                                    <div class="tw-flex tw-items-center tw-gap-2">
                                                        <div class="tw-bg-secondary-50 tw-text-secondary-700 tw-px-3 tw-py-2 tw-rounded-md tw-font-semibold tw-flex tw-items-center tw-shadow-sm">
                                                            <i class="fa-regular fa-clock tw-mr-2"></i>
                                                            <?php
                                                            $time_format = get_option('time_format') == 24 ? 'H:i' : 'g:i A';
                                                            ?>
                                                            <span class="">
                                                                <?= date($time_format, strtotime($appointment['start_hour'])); ?>
                                                                <?php
                                                                if (! empty($appointment['end_hour'])): ?>
                                                                    <span class="tw-mx-2 tw-text-secondary-400">—</span>
                                                                    <?= date($time_format, strtotime($appointment['end_hour'])); ?>
                                                                <?php
                                                                elseif (isset($appointment['service']) && ! empty($appointment['service']->duration)): ?>
                                                                    <?php
                                                                    $end_time = strtotime("+{$appointment['service']->duration} " . _l('minutes'), strtotime($appointment['start_hour']));
                                                                    ?>
                                                                    <span class="tw-mx-2 tw-text-secondary-400">—</span>
                                                                    <?= date($time_format, $end_time); ?>
                                                                <?php
                                                                endif; ?>
                                                            </span>
                                                        </div>

                                                        <?php
                                                        // Calculate duration for display
                                                        $duration_text = '';
                                                        if (! empty($appointment['end_hour'])) {
                                                            $start            = strtotime($appointment['start_hour']);
                                                            $end              = strtotime($appointment['end_hour']);
                                                            $duration_minutes = round(($end - $start) / 60);
                                                            $duration_text    = $duration_minutes . ' ' . _l('minutes');
                                                            if ($duration_minutes >= 60) {
                                                                $hours         = floor($duration_minutes / 60);
                                                                $mins          = $duration_minutes % 60;
                                                                $duration_text = $hours . ' ' . _l('hours');
                                                                if ($mins > 0) {
                                                                    $duration_text .= ' ' . $mins . ' ' . _l('minutes');
                                                                }
                                                            }
                                                        } elseif (isset($appointment['service']) && ! empty($appointment['service']->duration)) {
                                                            $duration_minutes = $appointment['service']->duration;
                                                            $duration_text    = $duration_minutes . ' ' . _l('minutes');
                                                            if ($duration_minutes >= 60) {
                                                                $hours         = floor($duration_minutes / 60);
                                                                $mins          = $duration_minutes % 60;
                                                                $duration_text = $hours . ' ' . _l('hours');
                                                                if ($mins > 0) {
                                                                    $duration_text .= ' ' . $mins . ' ' . _l('minutes');
                                                                }
                                                            }
                                                        }
                                                        ?>

                                                        <?php
                                                        if (! empty($duration_text)): ?>
                                                            <div class="tw-bg-neutral-100 tw-text-neutral-700 tw-px-3 tw-py-2 tw-rounded-md tw-flex tw-items-center">
                                                                <i class="fa-solid fa-hourglass-half tw-mr-2 tw-text-neutral-700"></i>
                                                                <span class=""><?= $duration_text; ?></span>
                                                            </div>
                                                        <?php
                                                        endif; ?>
                                                    </div>

                                                    <!-- Timezone -->
                                                    <div class="tw-flex tw-items-center">
                                                        <div class="tw-bg-info-50 tw-text-info-700 tw-px-3 tw-py-2 tw-rounded-md tw-flex tw-items-center tw-shadow-sm">
                                                            <i class="fa-solid fa-globe tw-mr-2"></i>
                                                            <span class="">
                                                                <?php
                                                                $timezone = ! empty($appointment['timezone']) ?
                                                                    htmlspecialchars($appointment['timezone']) :
                                                                    get_option('default_timezone');

                                                                // Try to make timezone display more user-friendly
                                                                $timezone_parts   = explode('/', $timezone);
                                                                $display_timezone = end($timezone_parts);
                                                                $display_timezone = str_replace('_', ' ', $display_timezone);
                                                                ?>
                                                                <?= $display_timezone; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Location -->
                                        <?php if (!empty($appointment['address'])): ?>
                                            <div class="tw-flex tw-items-center tw-gap-3 tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4 tw-mb-4">
                                                <div class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-bg-neutral-100 tw-text-neutral-700">
                                                    <i class="fa fa-globe"></i>
                                                </div>
                                                <div class="tw-w-full">
                                                    <div class="tw-bg-orange-50 tw-text-orange-800 tw-px-3 tw-py-2 tw-rounded-md tw-font-medium tw-flex tw-items-center tw-shadow-sm">
                                                        <i class="fa-solid fa-location-dot tw-mr-2"></i>
                                                        <span class="tw-break-all"><?= $appointment['address']; ?></span>
                                                    </div>
                                                    <?php if (get_option('google_api_key')): ?>
                                                        <div class="tw-mt-2">
                                                            <a href="https://maps.google.com/?q=<?= urlencode($appointment['address']); ?>"
                                                                target="_blank"
                                                                class="tw-text-sm tw-text-primary-600 hover:tw-text-primary-700 tw-flex tw-items-center tw-gap-1">
                                                                <i class="fa-solid fa-external-link-alt"></i>
                                                                <?= _l('appointment_view_on_map'); ?>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        // Find non-empty custom fields first
                                        $non_empty_custom_fields = [];
                                        if (total_rows(db_prefix() . 'customfields', ['fieldto' => 'appointly', 'active' => 1]) > 0 && isset($appointment['id'])) {
                                            foreach (get_custom_fields('appointly') as $field) {
                                                $value = get_custom_field_value($appointment['id'], $field['id'], 'appointly');
                                                if (is_array($value)) {
                                                    $isEmpty = count(array_filter($value, function ($v) {
                                                        return trim($v) !== '';
                                                    })) === 0;
                                                } else {
                                                    $isEmpty = trim((string) $value) === '';
                                                }
                                                if (! $isEmpty) {
                                                    $non_empty_custom_fields[] = [
                                                        'field' => $field,
                                                        'value' => $value,
                                                    ];
                                                }
                                            }
                                        }
                                        ?>
                                        <?php
                                        if (count($non_empty_custom_fields) > 0) { ?>
                                            <div class="tw-bg-white tw-rounded-md tw-shadow-sm tw-p-4 tw-mb-8 tw-border tw-border-neutral-200 tw-border-solid">
                                                <div class="tw-flex tw-items-center tw-mb-4">
                                                    <span class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-bg-neutral-100 tw-text-neutral-700 tw-text-base tw-mr-2">
                                                        <i class="fa-solid fa-tags"></i>
                                                    </span>
                                                    <span class="tw-text-base tw-text-neutral-800 tw-font-medium"><?= _l('custom_fields'); ?></span>
                                                </div>
                                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
                                                    <?php
                                                    foreach ($non_empty_custom_fields as $cf) {
                                                        $field       = $cf['field'];
                                                        $value       = $cf['value'];
                                                        $field_label = e($field['name']);
                                                    ?>
                                                        <div>
                                                            <div class="tw-text-neutral-700 tw-font-medium tw-mb-1"><?= $field_label; ?></div>
                                                            <div class="tw-text-neutral-800 tw-break-words">
                                                                <?php
                                                                if (is_array($value)) {
                                                                    echo implode(', ', array_map('e', $value));
                                                                } else {
                                                                    echo e($value);
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    <?php
                                                    } ?>
                                                </div>
                                            </div>
                                        <?php
                                        } ?>

                                        <!-- Email Tracking -->
                                        <?php
                                        if ($appointment['source'] !== 'internal_staff'): ?>
                                            <div class="tw-flex tw-items-center tw-gap-3 tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4">
                                                <div class="tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-md tw-bg-neutral-100 tw-text-neutral-700">
                                                    <i class="fa-regular fa-envelope-open"></i>
                                                </div>
                                                <div>
                                                    <div class=" tw-text-neutral-700"><?= _l('appointment_email_tracking'); ?></div>
                                                    <?php
                                                    if (! empty($appointment['email_tracking']) && $appointment['email_tracking'][0]['opened'] == 1): ?>
                                                        <div class="tw-text-success-600  tw-flex tw-items-center tw-gap-1">
                                                            <i class="fa-solid fa-check-circle"></i>
                                                            <?= _l('appointment_email_read_at'); ?>: <?= _dt($appointment['email_tracking'][0]['date_opened']); ?>
                                                        </div>
                                                    <?php
                                                    else: ?>
                                                        <div class="tw-text-neutral-400  tw-flex tw-items-center tw-gap-1">
                                                            <i class="fa-regular fa-clock"></i>
                                                            <?= _l('appointment_email_not_read'); ?>
                                                        </div>
                                                    <?php
                                                    endif; ?>
                                                </div>
                                            </div>
                                        <?php
                                        endif; ?>
                                    </div>
                                </div>

                                <!-- Attendees Section -->
                                <div class="tw-mt-8 tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4">
                                    <span class="tw-text-lg tw-p-4">
                                        <i class="fa-solid fa-users tw-mr-2 tw-mt-4"></i>
                                        <?= _l('appointment_staff_attendees'); ?>
                                    </span>

                                    <?php
                                    if (! empty($appointment['attendees'])): ?>
                                        <div class="tw-flex tw-flex-wrap tw-gap-6 tw-p-4">
                                            <?php
                                            foreach ($appointment['attendees'] as $staffid):
                                                $staff_info = get_staff($staffid);
                                                if (! $staff_info) {
                                                    continue;
                                                }
                                            ?>
                                                <div class="tw-flex tw-items-center tw-gap-4 tw-rounded-md">
                                                    <div class="tw-flex-shrink-0">
                                                        <img src="<?= getStaffProfileImage($staffid) . '?v=' . time(); ?>"
                                                            class="tw-w-16 tw-h-16 tw-rounded-md tw-object-cover" style="width: 64px; height: 64px;"
                                                            alt="<?= $staff_info->firstname . ' ' . $staff_info->lastname; ?>" />
                                                    </div>
                                                    <div>
                                                        <a href="<?= admin_url('staff/profile/' . $staffid); ?>"
                                                            class=" tw-text-primary-600 hover:tw-text-primary-700 tw-block">
                                                            <?= $staff_info->firstname . ' ' . $staff_info->lastname; ?>
                                                        </a>
                                                        <?php
                                                        if (! empty($staff_info->email)): ?>
                                                            <a href="mailto:<?= $staff_info->email; ?>"
                                                                class=" tw-text-neutral-700 hover:tw-text-neutral-700 tw-block tw-mt-1">
                                                                <i class="fa-regular fa-envelope tw-mr-1"></i>
                                                                <?= $staff_info->email; ?>
                                                            </a>
                                                        <?php
                                                        endif; ?>
                                                        <?php
                                                        if (! empty($staff_info->phonenumber)): ?>
                                                            <div class=" tw-text-neutral-700 tw-mt-1">
                                                                <i class="fa-solid fa-phone tw-mr-1"></i>
                                                                <?= $staff_info->phonenumber; ?>
                                                            </div>
                                                        <?php
                                                        endif; ?>
                                                    </div>
                                                </div>
                                            <?php
                                            endforeach; ?>
                                        </div>
                                    <?php
                                    else: ?>
                                        <div class="tw-text-center tw-text-neutral-700 tw-py-4">
                                            <?= _l('appointment_no_assigned_staff_found'); ?>
                                        </div>
                                    <?php
                                    endif; ?>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab_session_overview">
                                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                                    <!-- Session Overview Section -->
                                    <div class="tw-bg-white tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4">
                                        <div class="tw-flex tw-items-center tw-gap-2 tw-mb-4">
                                            <i class="fa-solid fa-align-left tw-text-blue-600"></i>
                                            <h5 class="tw-text-lg tw-font-semibold tw-text-neutral-800"><?= _l('appointment_description'); ?></h5>
                                        </div>
                                        <div class="tw-rounded-md tw-p-4 tw-border tw-border-blue-200 tw-min-h-[120px]">
                                            <?php if (staff_can('edit', 'appointments')): ?>
                                                <div id="description_display" class="tw-text-neutral-700" style="<?= !empty($appointment['description']) ? '' : 'display: none;' ?>">
                                                    <?= !empty($appointment['description']) ? $appointment['description'] : ''; ?>
                                                </div>
                                                <div id="description_placeholder" class="tw-text-neutral-500 tw-italic tw-cursor-pointer" style="<?= !empty($appointment['description']) ? 'display: none;' : '' ?>" onclick="editDescription()">
                                                    Click to add description...
                                                </div>
                                                <div id="description_editor" style="display: none;">
                                                    <textarea id="appointment_description" data-appointment-id="<?= $appointment['id']; ?>"><?= isset($appointment['description']) ? $appointment['description'] : ''; ?></textarea>
                                                    <div class="tw-mt-2 tw-flex tw-gap-2">
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="saveDescription()">Save</button>
                                                        <button type="button" class="btn btn-default btn-sm" onclick="cancelDescription()">Cancel</button>
                                                    </div>
                                                </div>
                                                <div id="description_edit_btn" class="tw-mt-2" style="<?= !empty($appointment['description']) ? '' : 'display: none;' ?>">
                                                    <button type="button" class="btn btn-default btn-sm" onclick="editDescription()">
                                                        <i class="fa fa-edit"></i> Edit Description
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <div class="tw-text-neutral-700">
                                                    <?= !empty($appointment['description']) ? $appointment['description'] : '<span class="tw-text-neutral-500 tw-italic">No description available</span>'; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Notes Section -->
                                    <div class="tw-bg-white tw-border tw-border-neutral-200 tw-border-solid tw-rounded-md tw-p-4">
                                        <div class="tw-flex tw-items-center tw-gap-2 tw-mb-4">
                                            <i class="fa-regular fa-note-sticky tw-text-green-600"></i>
                                            <h5 class="tw-text-lg tw-font-semibold tw-text-neutral-800"><?= _l('appointment_notes'); ?></h5>
                                        </div>
                                        <div class="tw-rounded-md tw-p-4 tw-border tw-border-green-200 tw-min-h-[120px]">
                                            <?php if (staff_can('edit', 'appointments')): ?>
                                                <div id="notes_display" class="tw-text-neutral-700" style="<?= !empty($appointment['notes']) ? '' : 'display: none;' ?>">
                                                    <?= !empty($appointment['notes']) ? $appointment['notes'] : ''; ?>
                                                </div>
                                                <div id="notes_placeholder" class="tw-text-neutral-500 tw-italic tw-cursor-pointer" style="<?= !empty($appointment['notes']) ? 'display: none;' : '' ?>" onclick="editNotes()">
                                                    Click to add notes...
                                                </div>
                                                <div id="notes_editor" style="display: none;">
                                                    <textarea id="appointment_notes" data-appointment-id="<?= $appointment['id']; ?>"><?= isset($appointment['notes']) ? clear_textarea_breaks($appointment['notes']) : ''; ?></textarea>
                                                    <div class="tw-mt-2 tw-flex tw-gap-2">
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="saveNotes()">Save</button>
                                                        <button type="button" class="btn btn-default btn-sm" onclick="cancelNotes()">Cancel</button>
                                                    </div>
                                                </div>
                                                <div id="notes_edit_btn" class="tw-mt-2" style="<?= !empty($appointment['notes']) ? '' : 'display: none;' ?>">
                                                    <button type="button" class="btn btn-default btn-sm" onclick="editNotes()">
                                                        <i class="fa fa-edit"></i> Edit Notes
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <div class="tw-text-neutral-700">
                                                    <?= !empty($appointment['notes']) ? $appointment['notes'] : '<span class="tw-text-neutral-500 tw-italic">No notes available</span>'; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Provider Section -->
                    <?php
                    if ($appointment['source'] !== 'internal_staff'): ?>
                        <div class="tw-bg-white tw-rounded-md tw-shadow-sm tw-mt-8">
                            <div class="tw-p-6">
                                <h4 class="tw-text-lg  tw-mb-6"><?= _l('appointment_provider'); ?></h4>

                                <?php
                                if (isset($appointment['provider'])): ?>
                                    <!-- Mobile Layout: Stack vertically -->
                                    <div class="tw-block md:tw-hidden">
                                        <!-- Provider Info -->
                                        <div class="tw-flex tw-items-start tw-gap-4 tw-mb-6">
                                            <div class="tw-flex-shrink-0">
                                                <div class="tw-w-20 tw-h-20 tw-rounded-md tw-overflow-hidden tw-bg-neutral-100">
                                                    <img src="<?= $appointment['provider']['profile_image']; ?>"
                                                        alt="<?= $appointment['provider']['full_name']; ?>"
                                                        class="tw-w-full tw-h-full tw-object-cover" />
                                                </div>
                                            </div>
                                            <div class="tw-flex-grow">
                                                <h5 class="tw-text-lg tw-mb-1">
                                                    <a href="<?= admin_url('staff/profile/' . $appointment['provider']['staffid']); ?>">
                                                        <?= $appointment['provider']['full_name']; ?>
                                                    </a>
                                                </h5>
                                                <?php
                                                if (! empty($appointment['provider']['email'])): ?>
                                                    <p class="tw-text-neutral-600">
                                                        <a href="mailto:<?= $appointment['provider']['email']; ?>"
                                                            class="tw-text-neutral-700 tw-underline">
                                                            <?= $appointment['provider']['email']; ?>
                                                        </a>
                                                    </p>
                                                <?php
                                                endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Desktop Layout: Side by side -->
                                    <div class="tw-hidden md:tw-flex tw-items-start tw-gap-6">
                                        <!-- Provider Info -->
                                        <div class="tw-flex-shrink-0" style="width:183px;">
                                            <div class="tw-w-24 tw-h-24 tw-rounded-md tw-overflow-hidden tw-bg-neutral-100">
                                                <img src="<?= $appointment['provider']['profile_image']; ?>"
                                                    alt="<?= $appointment['provider']['full_name']; ?>"
                                                    class="tw-w-full tw-h-full tw-object-cover" />
                                            </div>
                                        </div>

                                        <div class="tw-flex-grow tw-w-full">
                                            <!-- Provider Details -->
                                            <div class="tw-mb-4">
                                                <h5 class=" tw-text-lg tw-mb-1">
                                                    <a href="<?= admin_url('staff/profile/' . $appointment['provider']['staffid']); ?>">
                                                        <?= $appointment['provider']['full_name']; ?>
                                                    </a>
                                                </h5>
                                                <?php
                                                if (! empty($appointment['provider']['email'])): ?>
                                                    <p class="tw-text-neutral-600 ">
                                                        <a href="mailto:<?= $appointment['provider']['email']; ?>"
                                                            class="tw-text-neutral-700 tw-underline">
                                                            <?= $appointment['provider']['email']; ?>
                                                        </a>
                                                    </p>
                                                <?php
                                                endif; ?>
                                            </div>

                                            <!-- Working Hours (Desktop) -->
                                            <?php
                                            if (isset($appointment['provider']) && $appointment['provider_id']):
                                                $CI = &get_instance();
                                                $days = [
                                                    'Monday'    => _l('appointly_day_monday'),
                                                    'Tuesday'   => _l('appointly_day_tuesday'),
                                                    'Wednesday' => _l('appointly_day_wednesday'),
                                                    'Thursday'  => _l('appointly_day_thursday'),
                                                    'Friday'    => _l('appointly_day_friday'),
                                                    'Saturday'  => _l('appointly_day_saturday'),
                                                    'Sunday'    => _l('appointly_day_sunday'),
                                                ];

                                                $appointment_day_name = date('l', strtotime($appointment['date']));
                                            ?>
                                                <div class="tw-mt-4">
                                                    <div class="tw-flex tw-items-center tw-gap-2">
                                                        <div class="tw-flex tw-gap-2 tw-items-baseline">
                                                            <p class="tw-text-lg tw-text-neutral-700 tw-font-semibold"><?= _l('appointly_working_hours') ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-3 tw-bg-gray-50 tw-rounded-md tw-p-4">
                                                        <?php
                                                        // Get provider's working hours
                                                        $CI->db->where('staff_id', $appointment['provider_id']);
                                                        $staff_working_hours = $CI->db->get(db_prefix() . 'appointly_staff_working_hours')->result_array();

                                                        // Index by weekday for easier access
                                                        $provider_hours = [];
                                                        foreach ($staff_working_hours as $hours) {
                                                            $provider_hours[$hours['weekday']] = $hours;
                                                        }

                                                        // Get company schedule for days not defined in staff hours
                                                        $CI->db->order_by('FIELD(weekday, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")');
                                                        $company_schedule = $CI->db->get(db_prefix() . 'appointly_company_schedule')->result_array();

                                                        // Display schedule for each day
                                                        foreach ($days as $day_name => $day_label):
                                                            $is_appointment_day = ($day_name == $appointment_day_name);

                                                            // Determine if this day has custom staff hours or uses company schedule
                                                            if (isset($provider_hours[$day_name]) && $provider_hours[$day_name]['use_company_schedule'] == 0) {
                                                                $is_available = $provider_hours[$day_name]['is_available'];
                                                                $start_time   = $provider_hours[$day_name]['start_time'];
                                                                $end_time     = $provider_hours[$day_name]['end_time'];
                                                            } else {
                                                                // Find company schedule for this day
                                                                $day_schedule = array_filter($company_schedule, function ($schedule) use ($day_name) {
                                                                    return $schedule['weekday'] == $day_name;
                                                                });

                                                                if (! empty($day_schedule)) {
                                                                    $day_schedule = reset($day_schedule);
                                                                    $is_available = $day_schedule['is_enabled'];
                                                                    $start_time   = $day_schedule['start_time'];
                                                                    $end_time     = $day_schedule['end_time'];
                                                                } else {
                                                                    $is_available = false;
                                                                    $start_time   = '09:00:00';
                                                                    $end_time     = '17:00:00';
                                                                }
                                                            }

                                                            // Format times for display
                                                            $start_display = date(get_option('time_format') == 24 ? 'H:i' : 'g:i A', strtotime($start_time));
                                                            $end_display   = date(get_option('time_format') == 24 ? 'H:i' : 'g:i A', strtotime($end_time));
                                                        ?>
                                                            <div class="tw-border tw-border-solid tw-rounded-md tw-bg-white/20 tw-py-2 tw-px-4 tw-block tw-shadow-sm tw-border-neutral-900/20 tw-text-neutral-700 hover:tw-text-neutral-800">
                                                                <span class=" <?= $is_appointment_day ? 'tw-text-primary-600' : 'tw-text-neutral-700' ?>">
                                                                    <?= $day_label; ?>
                                                                </span>
                                                                <span class=" <?= $is_appointment_day ? 'tw-text-primary-600 ' : 'tw-text-neutral-600' ?>">
                                                                    <?php
                                                                    if ($is_available): ?>
                                                                        <?= $start_display . ' - ' . $end_display; ?>
                                                                    <?php
                                                                    else: ?>
                                                                        <span class="tw-text-neutral-400 tw-italic"><?= _l('appointly_closed'); ?></span>
                                                                    <?php
                                                                    endif; ?>
                                                                </span>
                                                            </div>
                                                        <?php
                                                        endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php
                                            endif; ?>
                                        </div>
                                    </div>

                                    <!-- Working Hours (Mobile - appears below provider info) -->
                                    <?php
                                    if (isset($appointment['provider']) && $appointment['provider_id']):
                                        $CI = &get_instance();
                                        $days = [
                                            'Monday'    => _l('appointly_day_monday'),
                                            'Tuesday'   => _l('appointly_day_tuesday'),
                                            'Wednesday' => _l('appointly_day_wednesday'),
                                            'Thursday'  => _l('appointly_day_thursday'),
                                            'Friday'    => _l('appointly_day_friday'),
                                            'Saturday'  => _l('appointly_day_saturday'),
                                            'Sunday'    => _l('appointly_day_sunday'),
                                        ];

                                        $appointment_day_name = date('l', strtotime($appointment['date']));
                                    ?>
                                        <div class="tw-block md:tw-hidden tw-mt-4">
                                            <div class="tw-flex tw-items-center tw-gap-2 tw-mb-3">
                                                <div class="tw-flex tw-gap-2 tw-items-baseline">
                                                    <p class="tw-text-lg tw-text-neutral-700 tw-font-semibold"><?= _l('appointly_working_hours') ?></p>
                                                </div>
                                            </div>
                                            <div class="tw-grid tw-grid-cols-1 tw-gap-3 tw-bg-gray-50 tw-rounded-md tw-p-4">
                                                <?php
                                                // Get provider's working hours
                                                $CI->db->where('staff_id', $appointment['provider_id']);
                                                $staff_working_hours = $CI->db->get(db_prefix() . 'appointly_staff_working_hours')->result_array();

                                                // Index by weekday for easier access
                                                $provider_hours = [];
                                                foreach ($staff_working_hours as $hours) {
                                                    $provider_hours[$hours['weekday']] = $hours;
                                                }

                                                // Get company schedule for days not defined in staff hours
                                                $CI->db->order_by('FIELD(weekday, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")');
                                                $company_schedule = $CI->db->get(db_prefix() . 'appointly_company_schedule')->result_array();

                                                // Display schedule for each day
                                                foreach ($days as $day_name => $day_label):
                                                    $is_appointment_day = ($day_name == $appointment_day_name);

                                                    // Determine if this day has custom staff hours or uses company schedule
                                                    if (isset($provider_hours[$day_name]) && $provider_hours[$day_name]['use_company_schedule'] == 0) {
                                                        $is_available = $provider_hours[$day_name]['is_available'];
                                                        $start_time   = $provider_hours[$day_name]['start_time'];
                                                        $end_time     = $provider_hours[$day_name]['end_time'];
                                                    } else {
                                                        // Find company schedule for this day
                                                        $day_schedule = array_filter($company_schedule, function ($schedule) use ($day_name) {
                                                            return $schedule['weekday'] == $day_name;
                                                        });

                                                        if (! empty($day_schedule)) {
                                                            $day_schedule = reset($day_schedule);
                                                            $is_available = $day_schedule['is_enabled'];
                                                            $start_time   = $day_schedule['start_time'];
                                                            $end_time     = $day_schedule['end_time'];
                                                        } else {
                                                            $is_available = false;
                                                            $start_time   = '09:00:00';
                                                            $end_time     = '17:00:00';
                                                        }
                                                    }

                                                    // Format times for display
                                                    $start_display = date(get_option('time_format') == 24 ? 'H:i' : 'g:i A', strtotime($start_time));
                                                    $end_display   = date(get_option('time_format') == 24 ? 'H:i' : 'g:i A', strtotime($end_time));
                                                ?>
                                                    <div class="tw-border tw-border-solid tw-rounded-md tw-bg-white/20 tw-py-2 tw-px-4 tw-block tw-shadow-sm tw-border-neutral-900/20 tw-text-neutral-700 hover:tw-text-neutral-800">
                                                        <span class="<?= $is_appointment_day ? 'tw-text-primary-600' : 'tw-text-neutral-700' ?>">
                                                            <?= $day_label; ?>
                                                        </span>
                                                        <span class="tw-float-right <?= $is_appointment_day ? 'tw-text-primary-600' : 'tw-text-neutral-600' ?>">
                                                            <?php
                                                            if ($is_available): ?>
                                                                <?= $start_display . ' - ' . $end_display; ?>
                                                            <?php
                                                            else: ?>
                                                                <span class="tw-text-neutral-400 tw-italic"><?= _l('appointly_closed'); ?></span>
                                                            <?php
                                                            endif; ?>
                                                        </span>
                                                    </div>
                                                <?php
                                                endforeach; ?>
                                            </div>
                                        </div>
                                    <?php
                                    endif; ?>
                                <?php
                                else: ?>
                                    <p class="tw-text-neutral-600">
                                        <?= _l('appointment_no_provider_assigned'); ?>
                                    </p>
                                <?php
                                endif; ?>

                                <!-- Feedback Section -->
                                <?php
                                if (isset($appointment['status']) && $appointment['status'] == 'completed') : ?>
                                    <div class="tw-bg-white tw-rounded-md tw-shadow-sm tw-mt-8">
                                        <div class="tw-p-6">
                                            <h4 class="tw-text-lg ">
                                                <i class="fa-solid fa-star tw-mr-2"></i>
                                                <?= _l('appointment_feedback_label'); ?>
                                            </h4>

                                            <?php
                                            if ($appointment['feedback'] !== null) : ?>
                                                <!-- Display existing feedback -->
                                                <div class="tw-mt-4 tw-bg-neutral-50 tw-p-4 tw-rounded-md tw-border tw-border-neutral-200">
                                                    <div class="tw-flex tw-items-center" style="<?php
                                                                                                if (!empty($appointment['feedback_comment'])): ?>tw-mb-3<?php endif; ?>">
                                                        <div class="tw-mr-2  tw-text-neutral-700">
                                                            <?= _l('appointment_feedback_label'); ?>:
                                                        </div>
                                                        <div class="tw-flex">
                                                            <?php
                                                            for ($i = 0; $i < 5; $i++) : ?>
                                                                <div class="tw-mx-1">
                                                                    <i class="fa fa-star <?= ($appointment['feedback'] > $i) ? 'tw-text-yellow-400' : 'tw-text-neutral-300'; ?> tw-text-xl"></i>
                                                                </div>
                                                            <?php
                                                            endfor; ?>
                                                        </div>
                                                        <div class="tw-ml-3 tw-bg-green-50 tw-text-green-700 tw-px-2 tw-py-1 tw-rounded-full ">
                                                            <?= $appointment['feedback'] ?>/5
                                                        </div>
                                                    </div>
                                                    <?php
                                                    if (! empty($appointment['feedback_comment'])) : ?>
                                                        <div class="tw-mt-3">
                                                            <div class="tw-bg-white tw-p-3 tw-rounded-md tw-border tw-border-neutral-200">
                                                                <p class="tw-whitespace-pre-line tw-text-sm"><?= $appointment['feedback_comment']; ?></p>
                                                            </div>
                                                        </div>
                                                    <?php
                                                    endif; ?>
                                                </div>
                                            <?php
                                            else : ?>
                                                <div class="tw-mt-4 tw-text-neutral-600">
                                                    <?= _l('appointment_no_feedback_provided'); ?>
                                                </div>

                                                <?php
                                                if (staff_can('edit', 'appointments')) : ?>
                                                    <div class="tw-mt-4">
                                                        <button type="button"
                                                            onclick="requestFeedback(<?= $appointment['id']; ?>)"
                                                            class="btn btn-default">
                                                            <i class="fa-regular fa-comment tw-mr-2"></i>
                                                            <?= _l('appointments_request_feedback'); ?>
                                                        </button>
                                                    </div>
                                                <?php
                                                endif; ?>
                                            <?php
                                            endif; ?>
                                        </div>
                                    </div>
                                <?php
                                endif; ?>
                            </div>
                        </div>
                    <?php
                    endif; ?>

                    <!-- Location Map -->
                    <?php
                    if (! empty($appointment['address']) && get_option('google_api_key')): ?>
                        <div class="tw-bg-white tw-rounded-md tw-shadow-sm tw-mt-8">
                            <div class="tw-p-6">
                                <h4 class="tw-text-lg  tw-mb-6"><?= _l('appointment_meeting_location'); ?></h4>
                                <div class="tw-rounded-md tw-overflow-hidden tw-h-[400px] tw-border tw-border-neutral-200">
                                    <iframe
                                        width="100%"
                                        height="100%"
                                        frameborder="0"
                                        src="https://www.google.com/maps/embed/v1/place?key=<?= get_option('google_api_key') ?>&q=<?= urlencode($appointment['address']) ?>"
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    <?php
                    endif; ?>
                </div>
            </div><!-- /.col-md-12 -->
        </div><!-- /.row -->
    </div><!-- /.content -->
</div><!-- /#wrapper -->

<!-- Video Meeting Custom Email Modal -->
<?php
if (! empty($appointment['google_meet_link'])): ?>
    <div class="modal fade" id="customEmailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-envelope"></i>
                        <?= _l('appointment_google_meet_send_invitation'); ?>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><?= _l('appointment_google_meet_send_to'); ?></label>
                        <div class="well well-sm">
                            <strong><?= _l('appointment_google_meet_primary_recipient'); ?>:</strong>
                            <?= ! empty($appointment['email']) ? $appointment['email'] : _l('appointment_no_email_provided'); ?>
                            <?php
                            if (! empty($appointment['name'])): ?>
                                (<?= $appointment['name']; ?>)
                            <?php
                            endif; ?>
                        </div>

                        <?php
                        if (! empty($google_meet_attendees) && count($google_meet_attendees) > 0): ?>
                            <div class="form-group mtop15">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" id="notify_attendees_checkbox" checked>
                                    <label for="notify_attendees_checkbox">
                                        <?= _l('appointment_google_meet_also_notify_attendees'); ?>
                                    </label>
                                </div>
                                <small class="text-muted"><?= implode(', ', $google_meet_attendees); ?></small>
                            </div>
                        <?php
                        endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="google_meet_notify_message"><?= _l('appointment_google_meet_email_message'); ?></label>
                        <?php
                        // Convert HTML breaks to actual line breaks for better display
                        $message_text = str_replace(['<br><br>', '<br>'], ["\n\n", "\n"], _l('appointment_meet_message'));
                        $full_message = $message_text . $appointment['google_meet_link'];
                        ?>
                        <textarea class="form-control" name="google_meet_notify_message" id="google_meet_notify_message" rows="8"><?= $full_message; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                    <button type="button" class="btn btn-primary" id="submit_google_meet_email_btn" onclick="sendAppointmentRemindersEmail()">
                        <?= _l('send'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="googleMeetDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Clean Header -->
                <div class="modal-header">
                    <div class="pull-left">
                        <div class="media">
                            <div class="media-left">
                                <i class="fab fa-google" style="font-size: 24px; color: #4285F4; margin-right: 15px; margin-top: 5px;"></i>
                            </div>
                            <div class="media-body">
                                <h4 class="modal-title">
                                    <?= _l('appointment_google_meet_meeting_details'); ?>
                                </h4>
                                <p style="margin: 0; font-size: 13px; color: #777;">
                                    <?= _l('appointment_google_meet_quick_join'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Copy Link Section -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-link"></i> <?= _l('appointment_google_meet_copy_link'); ?>
                        </div>
                        <div class="panel-body">
                            <div class="input-group">
                                <input type="text" class="form-control" id="meetLinkInput"
                                    value="<?= $appointment['google_meet_link']; ?>" readonly>
                                <span class="input-group-btn">
                                    <button class="btn btn-info" onclick="copyGoogleMeetLink('<?= addslashes($appointment['google_meet_link']); ?>')">
                                        <i class="fa fa-copy"></i> <?= _l('copy'); ?>
                                    </button>
                                </span>
                            </div>

                            <!-- Copy Success Message -->
                            <div id="copySuccessMessage" class="hidden" style="margin-top: 10px;">
                                <div class="alert alert-success" style="margin: 0; padding: 8px 15px;">
                                    <i class="fa fa-check-circle"></i> <?= _l('appointment_google_meet_link_copied') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Features -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-cogs"></i> <?= _l('appointment_google_meet_meeting_details') ?>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- HD Video & Audio -->
                                    <div class="media" style="margin-bottom: 15px;">
                                        <div class="media-left">
                                            <i class="fa fa-video text-success" style="font-size: 18px; width: 25px;"></i>
                                        </div>
                                        <div class="media-body">
                                            <div style="font-weight: 500;">
                                                <?= _l('appointment_google_meet_hd_video_audio') ?>
                                            </div>
                                            <small class="text-success">
                                                <?= _l('appointment_google_meet_always_enabled') ?>
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Recording Status -->
                                    <div class="media">
                                        <div class="media-left">
                                            <i class="fa fa-record-vinyl <?php
                                                                            echo get_option('appointly_google_meet_recording') == '1' ? 'text-info' : 'text-muted'; ?>" style="font-size: 18px; width: 25px;"></i>
                                        </div>
                                        <div class="media-body">
                                            <div style="font-weight: 500;">
                                                <?php
                                                if (get_option('appointly_google_meet_recording') == '1'): ?>
                                                    <?= _l('appointment_google_meet_recording_enabled'); ?>
                                                <?php
                                                else: ?>
                                                    <?= _l('appointment_google_meet_recording_disabled'); ?>
                                                <?php
                                                endif; ?>
                                            </div>
                                            <small class="<?php
                                                            echo get_option('appointly_google_meet_recording') == '1' ? 'text-info' : 'text-muted'; ?>">
                                                <?php
                                                if (get_option('appointly_google_meet_recording') == '1'): ?>
                                                    <?= _l('appointly_enabled') ?>
                                                <?php
                                                else: ?>
                                                    <?= _l('appointly_disable') ?>
                                                <?php
                                                endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <!-- Screen Sharing -->
                                    <div class="media" style="margin-bottom: 15px;">
                                        <div class="media-left">
                                            <i class="fa fa-desktop text-success" style="font-size: 18px; width: 25px;"></i>
                                        </div>
                                        <div class="media-body">
                                            <div style="font-weight: 500;">
                                                <?= _l('appointment_google_meet_share_screen'); ?>
                                            </div>
                                            <small class="text-success">
                                                <?= _l('appointment_google_meet_always_enabled'); ?>
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Waiting Room Status -->
                                    <div class="media">
                                        <div class="media-left">
                                            <i class="fa fa-hourglass-half <?php
                                                                            echo get_option('appointly_google_meet_waiting_room') == '1' ? 'text-warning' : 'text-muted'; ?>" style="font-size: 18px; width: 25px;"></i>
                                        </div>
                                        <div class="media-body">
                                            <div style="font-weight: 500;">
                                                <?php
                                                if (get_option('appointly_google_meet_waiting_room') == '1'): ?>
                                                    <?= _l('appointment_google_meet_waiting_room_enabled_status'); ?>
                                                <?php
                                                else: ?>
                                                    <?= _l('appointment_google_meet_waiting_room_disabled'); ?>
                                                <?php
                                                endif; ?>
                                            </div>
                                            <small class="<?php
                                                            echo get_option('appointly_google_meet_waiting_room') == '1' ? 'text-warning' : 'text-muted'; ?>">
                                                <?php
                                                if (get_option('appointly_google_meet_waiting_room') == '1'): ?>
                                                    <?= _l('appointly_enabled') ?>
                                                <?php
                                                else: ?>
                                                    <?= _l('appointly_disable') ?>
                                                <?php
                                                endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-bolt"></i> <?= _l('appointment_google_meet_quick_actions') ?>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="<?= $appointment['google_meet_link'] ?>" target="_blank"
                                        class="btn btn-success btn-block">
                                        <i class="fa fa-video"></i> <?= _l('appointment_google_meet_join_meeting') ?>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-primary btn-block"
                                        data-toggle="modal" data-target="#customEmailModal" data-dismiss="modal">
                                        <i class="fa fa-envelope"></i> <?= _l('appointment_google_meet_send_invitation_btn') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clean Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> <?= _l('close'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php
endif; ?>

<!-- Hidden Modal Wrapper / JS Inclusions -->
<?php
init_tail(); ?>

<script>
    window.google_meet_attendees = <?= json_encode($google_meet_attendees); ?>;
</script>

<?php
require 'modules/appointly/assets/js/outlook_js.php'; ?>
<?php
require 'modules/appointly/assets/js/tables_appointment_js.php'; ?>
<style>
    /* Fix checkbox clickability */
    #customEmailModal .checkbox label {
        cursor: pointer;
        position: relative;
    }

    #customEmailModal .checkbox input[type="checkbox"] {
        cursor: pointer;
        position: absolute;
        left: 0;
        top: 3px;
    }
</style>
