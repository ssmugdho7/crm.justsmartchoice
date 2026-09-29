<?php

defined('BASEPATH') or exit('No direct script access allowed');

init_head();

$appointly_default_table_filter = get_meta('staff', get_staff_user_id(), 'appointly_default_table_filter');

if (! $appointly_default_table_filter) {
    $appointly_default_table_filter = 'all';
}

$filters = [
    'status_pending',
    'status_in-progress',
    'status_completed',
    'status_cancelled',
    'status_no-show',
    'upcoming',

    'internal',
    'external',
    'recurring',
    'lead_related',
    'internal_staff',
    'google_calendar_synced',
    'today',
    'tomorrow',
    'this_week',
    'next_week',
    'this_month',
    'my_appointments',
    'assigned_to_me'
];

$appointly_show_summary = get_option('appointly_show_summary');
$appointly_outlook_client_id = get_option('appointly_outlook_client_id');
$edit_appointment_id = ($this->session->userdata('from_view_id')) ?: 0;
?>

<div id="wrapper">
    <div class="col-md-12 tw-px-0">
        <div class="content">
            <?php include_appointment_view('widgets', 'today_appointments'); ?>
        </div>
    </div>
    <div class="content">
        <?php if ($appointly_show_summary == 1) : ?>
            <div class="col-md-12 tw-px-0">
                <div class="panel_s">
                    <div class="panel-body" style="margin-top: -30px;">
                        <?php include_appointment_view('tables', 'summary'); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- pending cancellations -->
        <?php include_appointment_view('view_includes', 'pending_cancellations', compact('pending_cancellations')); ?>

        <!-- pending reschedules -->
        <?php include_appointment_view('view_includes', 'pending_reschedules', compact('pending_reschedules')); ?>

        <div class="col-md-12 tw-px-0">
            <div class="panel_s">
                <div class="panel-body">
                    <div class="_buttons">
                        <?php if (staff_can('create', 'appointments')) { ?>
                            <div class="dropdown pull-left">
                                <!-- Main Create Button - Updated to use new page URL instead of modal -->
                                <a href="<?= admin_url('appointly/appointments/create_page'); ?>" class="btn btn-default" id="createNewAppointment">
                                    <i class="fa-solid fa-circle-plus"></i>
                                    <?= _l("appointment_new_appointment") ?>
                                </a>
                            </div>
                        <?php } else { ?>
                            <button class="btn btn-primary disabled">
                                <?= _l("appointment_new_appointment") ?>
                            </button>
                        <?php } ?>

                        <div class="_filters _hidden_inputs hidden">
                            <?php
                            foreach ($filters as $filter) {
                                echo form_hidden(
                                    $filter,
                                    $filter === $appointly_default_table_filter ? $appointly_default_table_filter : null
                                );
                            }
                            ?>
                        </div>
                        <div class="btn-group pull-right btn-with-tooltip-group _filter_data" data-toggle="tooltip"
                            data-title="<?php
                                        echo _l('filter_by'); ?>">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-filter" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left width300">
                                <li class="filter-group <?= ($appointly_default_table_filter == 'all') ? 'active' : ''; ?>" data-toggle="tooltip" data-title="<?= _l('appointly_not_including_two_way_synced_appointments'); ?>">
                                    <a href="#" data-cview="all"
                                        onclick="dt_custom_view('','.table-appointments',''); return false;">
                                        <?php
                                        echo _l('all'); ?>
                                    </a>
                                </li>

                                <!-- Status filters -->
                                <li class="divider"></li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'status_pending') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="status_pending" onclick="dt_custom_view('status_pending','.table-appointments', 'status_pending',true); return false;">
                                        <?= _l('appointment_status_pending'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'status_in-progress') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="status_in-progress" onclick="dt_custom_view('status_in-progress','.table-appointments', 'status_in-progress',true); return false;">
                                        <?= _l('appointment_status_in-progress'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'status_completed') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="status_completed" onclick="dt_custom_view('status_completed','.table-appointments', 'status_completed',true); return false;">
                                        <?= _l('appointment_completed'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'status_cancelled') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="status_cancelled" onclick="dt_custom_view('status_cancelled','.table-appointments', 'status_cancelled',true); return false;">
                                        <?= _l('appointment_status_cancelled'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'status_no-show') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="status_no-show" onclick="dt_custom_view('status_no-show','.table-appointments', 'status_no-show',true); return false;">
                                        <?= _l('appointment_status_no-show'); ?>
                                    </a>
                                </li>

                                <!-- Time-based filters -->
                                <li class="divider"></li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'today') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="today" onclick="dt_custom_view('today','.table-appointments', 'today',true); return false;">
                                        <?= _l('appointment_today'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'tomorrow') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="tomorrow" onclick="dt_custom_view('tomorrow','.table-appointments', 'tomorrow',true); return false;">
                                        <?= _l('appointment_tomorrow'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'this_week') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="this_week" onclick="dt_custom_view('this_week','.table-appointments', 'this_week',true); return false;">
                                        <?= _l('appointment_this_week'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'next_week') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="next_week" onclick="dt_custom_view('next_week','.table-appointments', 'next_week',true); return false;">
                                        <?= _l('appointment_next_week'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'this_month') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="this_month" onclick="dt_custom_view('this_month','.table-appointments', 'this_month',true); return false;">
                                        <?= _l('appointment_this_month'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'upcoming') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="upcoming" onclick="dt_custom_view('upcoming','.table-appointments', 'upcoming',true); return false;">
                                        <?= _l('appointment_upcoming'); ?>
                                    </a>
                                </li>
                                </li>

                                <!-- Assignment-based filters -->
                                <li class="divider"></li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'my_appointments') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="my_appointments" onclick="dt_custom_view('my_appointments','.table-appointments', 'my_appointments',true); return false;">
                                        <?= _l('appointment_my_appointments'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'assigned_to_me') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="assigned_to_me" onclick="dt_custom_view('assigned_to_me','.table-appointments', 'assigned_to_me',true); return false;">
                                        <?= _l('appointment_assigned_to_me'); ?>
                                    </a>
                                </li>

                                <!-- Source-based filters -->
                                <li class="divider"></li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'internal') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="internal" onclick="dt_custom_view('internal','.table-appointments', 'internal',true); return false;">
                                        <?= _l('appointment_internal'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'external') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="external" onclick="dt_custom_view('external','.table-appointments', 'external',true); return false;">
                                        <?= _l('appointment_external'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'lead_related') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="lead_related" onclick="dt_custom_view('lead_related','.table-appointments', 'lead_related',true); return false;">
                                        <?= _l('appointment_lead_related'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'internal_staff') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="internal_staff" onclick="dt_custom_view('internal_staff','.table-appointments', 'internal_staff',true); return false;">
                                        <?= _l('appointment_internal_staff'); ?>
                                    </a>
                                </li>

                                <!-- Other filters -->
                                <li class="divider"></li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'recurring') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="recurring" onclick="dt_custom_view('recurring','.table-appointments', 'recurring',true); return false;">
                                        <?= _l('appointment_recurring'); ?>
                                    </a>
                                </li>
                                <li class="filter-group <?= ($appointly_default_table_filter == 'google_calendar_synced') ? 'active' : ''; ?>">
                                    <a href="#" data-cview="google_calendar_synced" onclick="dt_custom_view('google_calendar_synced','.table-appointments', 'google_calendar_synced',true); return false;">
                                        <?= _l('appointly_google_synced_title'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Calendar Integration Buttons - Properly implemented -->
                        <div class="pull-right dropdown tw-mr-2">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa-solid fa-calendar tw-mr-2"></i>
                                Calendar Integrations
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right" style="width: 280px; padding: 8px;">
                                <!-- Google Calendar -->
                                <li style="margin-bottom: 8px;">
                                    <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-neutral-50 tw-rounded-lg">
                                        <div class="tw-flex tw-items-center">
                                            <span class="tw-mr-2">G</span>
                                            <span class="tw-font-medium"><?= _l('appointment_google_calendar'); ?></span>
                                        </div>
                                        <?php if (!appointlyGoogleAuth()): ?>
                                            <a href="<?= site_url('appointly/google/auth/login'); ?>" class="tw-text-primary-600 tw-font-medium">
                                                <?= _l('appointment_connect'); ?> <i class="fa fa-arrow-right tw-ml-1"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= site_url('appointly/google/auth/logout'); ?>"
                                                class="tw-text-success-600 tw-font-medium"
                                                onclick="return confirm('<?= _l('appointments_google_revoke_confirm'); ?>');">
                                                <i class="fa fa-check-circle tw-mr-1"></i> <?= _l('appointment_connected'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </li>

                                <!-- Outlook Calendar -->
                                <li>
                                    <div class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-neutral-50 tw-rounded-lg">
                                        <div class="tw-flex tw-items-center">
                                            <i class="fa-regular fa-envelope tw-mr-2"></i>
                                            <span class="tw-font-medium"><?= _l('appointment_outlook_calendar'); ?></span>
                                        </div>
                                        <a href="#" id="sign_in_outlook" class="tw-text-primary-600 tw-font-medium">
                                            <?= _l('appointment_connect'); ?> <i class="fa fa-arrow-right tw-ml-1"></i>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <?php if (appointlyGoogleAuth() && get_option('appointments_googlesync_show_in_table') == '1') : ?>
                            <!-- Google Calendar Quick Filter -->
                            <div class="btn-group pull-right mright5">
                                <button type="button" id="google_calendar_filter" class="btn btn-default" data-toggle="tooltip"
                                    data-title="<?= _l('appointments_synced_from_google'); ?>" onclick="toggleGoogleCalendarFilter(this)">
                                    <i class="fa-solid fa-sync-alt"></i>
                                    <span class="google-filter-text"><?= _l('appointments_synced_from_google'); ?></span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="btn-group pull-right btn-with-tooltip-group _filter_data" data-toggle="tooltip"
                            data-title="<?php
                                        echo _l('filter_by'); ?>"
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php
                        render_datatable([
                            [
                                'name' => _l('appointment_subject')
                            ],

                            [
                                'name' => _l('appointment_meeting_date')
                            ],

                            [
                                'name' => _l('appointment_initiated_by')
                            ],
                            [
                                'name' => _l('appointment_service')
                            ],
                            [
                                'name' => _l('appointment_provider')
                            ],
                            [
                                'name' => _l('appointment_source')
                            ],
                            [
                                'name' => _l('appointment_status')
                            ],
                            [
                                'name' => _l('appointly_created_at')
                            ],
                        ], 'appointments'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="modal_wrapper"></div>
<?php
init_tail(); ?>
<?php
require 'modules/appointly/assets/js/index_main_js.php';
require 'modules/appointly/assets/js/outlook_js.php';

?>
<style>
    /* Styling for past appointment rows */
    .table-appointments tbody tr.past-appointment-row {
        background-color: rgba(239, 68, 68, 0.05) !important;
        /* Very light red background */
        border-left: 3px solid rgba(239, 68, 68, 0.3);
        /* Subtle red left border */
    }

    .table-appointments tbody tr.past-appointment-row:hover {
        background-color: rgba(239, 68, 68, 0.08) !important;
        /* Slightly darker on hover */
    }

    /* Ensure text remains readable */
    .table-appointments tbody tr.past-appointment-row td {
        color: inherit;
    }
</style>

<script>
    // Reschedule request functions
    function approveReschedule(rescheduleId) {
        if (confirm("<?= _l('appointment_confirm_approve_reschedule'); ?>")) {
            $.ajax({
                url: '<?= admin_url("appointly/appointments/approve_reschedule"); ?>',
                type: 'POST',
                data: {
                    reschedule_id: rescheduleId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert_float('success', response.message);
                        // Reload the page to update the pending reschedules list
                        location.reload();
                    } else {
                        alert_float('danger', response.message);
                    }
                },
            });
        }
    }

    function denyReschedule(rescheduleId) {
        var reason = prompt("<?= _l('appointment_reschedule_denial_reason_prompt'); ?>");
        if (reason !== null && reason.trim() !== '') {
            $.ajax({
                url: '<?= admin_url("appointly/appointments/deny_reschedule"); ?>',
                type: 'POST',
                data: {
                    reschedule_id: rescheduleId,
                    denial_reason: reason.trim()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert_float('success', response.message);
                        // Reload the page to update the pending reschedules list
                        location.reload();
                    } else {
                        alert_float('danger', response.message);
                    }
                },
            });
        } else if (reason !== null) {
            alert_float('warning', "<?= _l('appointment_reschedule_denial_reason_required'); ?>");
        }
    }

    // Cancellation approval function
    function approveCancellation(appointmentId) {
        if (confirm("<?= _l('appointment_confirm_approve_cancellation'); ?>")) {
            $.ajax({
                url: '<?= admin_url("appointly/appointments/cancel_appointment"); ?>',
                type: 'POST',
                data: {
                    id: appointmentId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert_float('success', "<?= _l('appointment_cancellation_approved'); ?>");
                        // Reload the page to update the pending cancellations list
                        location.reload();
                    } else {
                        alert_float('danger', "<?= _l('appointment_cancellation_approval_failed'); ?>");
                    }
                }
            });
        }
    }

    $('#service_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var duration = selectedOption.data('duration');
        if (duration) {
            updateEndTime(duration);
        }
    });

    // Google Calendar Filter Toggle
    function toggleGoogleCalendarFilter(button) {
        var $button = $(button);
        var isActive = $button.hasClass('active');

        if (isActive) {
            // Remove filter
            $button.removeClass('active btn-info').addClass('btn-default');
            $('.google-filter-text').text('<?= _l('appointments_synced_from_google'); ?>');
            dt_custom_view('', '.table-appointments', '');
        } else {
            // Apply filter
            $button.removeClass('btn-default').addClass('active btn-info');
            $('.google-filter-text').text('<?= _l('appointment_hide_google_calendar'); ?>');
            dt_custom_view('google_calendar_synced', '.table-appointments', 'google_calendar_synced', true);
        }
    }
</script>

</body>

</html>
