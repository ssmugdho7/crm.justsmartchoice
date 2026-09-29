<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    .appointment-stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 20px;
        color: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .appointment-stats-card:hover {
        transform: translateY(-5px);
    }

    .appointment-stats-card.completed {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .appointment-stats-card.upcoming {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .appointment-stats-card.cancelled {
        background: linear-gradient(135deg, #fd746c 0%, #ff9068 100%);
    }

    .appointment-stats-card.total {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .appointment-icon {
        font-size: 2.5rem;
        opacity: 0.9;
        flex-shrink: 0;
    }

    .appointment-content {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .appointment-number {
        font-size: 2.5rem;
        font-weight: bold;
        line-height: 1;
    }

    .appointment-label {
        font-size: 0.9rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .option-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        margin: 0 2px;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .option-icon i {
        font-size: 14px;
    }

    .option-icon.view {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #6c757d;
    }

    .option-icon.view:hover {
        background: #e9ecef;
        color: #495057;
    }

    .option-icon.reschedule {
        background: #e3f2fd;
        border: 1px solid #bbdefb;
        color: #1976d2;
    }

    .option-icon.reschedule:hover {
        background: #bbdefb;
        color: #0d47a1;
    }

    .option-icon.cancel {
        background: #ffebee;
        border: 1px solid #ffcdd2;
        color: #d32f2f;
    }

    .option-icon.cancel:hover {
        background: #ffcdd2;
        color: #b71c1c;
    }

    .option-icon.book-again {
        background: #e8f5e8;
        border: 1px solid #c8e6c9;
        color: #388e3c;
    }

    .option-icon.book-again:hover {
        background: #c8e6c9;
        color: #1b5e20;
    }

    .option-icon.disabled {
        opacity: 0.5;
        cursor: not-allowed !important;
        /* Remove pointer-events: none to allow tooltips */
    }

    .option-icon.disabled:hover {
        transform: none !important;
        background: inherit !important;
        color: inherit !important;
    }

    .appointment-filters {
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .appointment-filters .filter-btn {
        margin-right: 10px;
        margin-bottom: 5px;
    }

    .appointment-filters .filter-btn.active {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }

    /* Time slot button styles - COMPACT DESIGN LIKE CLIENT HASH */
    .reschedule-time-slot-btn {
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        padding: 8px 12px !important;
        font-size: 13px !important;
        min-height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5e7eb !important;
        border-radius: 6px !important;
        background-color: white !important;
        color: #374151 !important;
    }

    /* Appointment row styling */
    .appointment-past,
    .appointment-cancelled {
        background-color: #fef2f2 !important;
    }

    .appointment-urgent {
        background-color: #fffbeb !important;
    }

    .appointment-past:hover,
    .appointment-cancelled:hover {
        background-color: #fecaca !important;
    }

    .appointment-urgent:hover {
        background-color: #fef3c7 !important;
    }

    .reschedule-time-slot-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        background-color: #f9fafb !important;
        border-color: #d1d5db !important;
    }

    .reschedule-time-slot-btn.selected {
        background-color: #dbeafe !important;
        border-color: #3b82f6 !important;
        color: #1e40af !important;
    }

    /* No checkbox - just blue styling for selected state */

    /* Styling for past or cancelled appointment rows */
    .table-row-danger,
    tr.table-row-danger:hover {
        background-color: #fef2f2 !important;
        /* light red */
    }

    tr.table-row-danger:hover {
        background-color: #fee2e2 !important;
        /* darker red on hover */
    }

    .reschedule-time-slot-btn:hover {
        transform: translateY(-1px);
        background-color: #f9fafb !important;
        border-color: #d1d5db !important;
    }
</style>

<div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
    <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700 section-heading">
        <?= _l('appointment_appointments'); ?>
    </h4>
    <a href="<?= site_url('appointly/appointments_public/book'); ?>"
        class="btn btn-primary" target="_blank">
        <i class="fa-regular fa-plus tw-mr-1"></i>
        <?= _l('appointment_book_new'); ?>
    </a>
</div>

<!-- Statistics Cards -->
<div class="row tw-mb-6">
    <div class="col-md-3 col-sm-6">
        <div class="appointment-stats-card total">
            <div class="appointment-icon">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div class="appointment-content">
                <div class="appointment-number">
                    <?= count($appointments); ?>
                </div>
                <div class="appointment-label"><?= _l('appointment_total_appointments'); ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="appointment-stats-card completed">
            <div class="appointment-icon">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <div class="appointment-content">
                <div class="appointment-number">
                    <?= count(array_filter($appointments, function ($a) {
                        return $a['status'] === 'completed';
                    })); ?>
                </div>
                <div class="appointment-label"><?= _l('appointment_completed_appointments'); ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="appointment-stats-card upcoming">
            <div class="appointment-icon">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="appointment-content">
                <div class="appointment-number">
                    <?= count(array_filter($appointments, function ($a) {
                        return in_array($a['status'], ['pending', 'in-progress']) && strtotime($a['date']) >= strtotime('today');
                    })); ?>
                </div>
                <div class="appointment-label"><?= _l('appointment_upcoming_appointments'); ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="appointment-stats-card cancelled">
            <div class="appointment-icon">
                <i class="fa-solid fa-times-circle"></i>
            </div>
            <div class="appointment-content">
                <div class="appointment-number">
                    <?= count(array_filter($appointments, function ($a) {
                        return in_array($a['status'], ['cancelled', 'no-show']);
                    })); ?>
                </div>
                <div class="appointment-label"><?= _l('appointment_cancelled_appointments'); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Appointments Table -->
<div class="row">
    <div class="col-md-12">
        <div class="panel_s">
            <div class="panel-body">
                <?php if (empty($appointments)) { ?>
                    <div class="tw-text-center tw-py-8">
                        <i class="fa-solid fa-calendar-xmark tw-text-4xl tw-text-neutral-400 tw-mb-4"></i>
                        <h3 class="tw-text-lg tw-font-medium tw-text-neutral-700 tw-mb-2">
                            <?= _l('appointment_no_appointments_found'); ?>
                        </h3>
                        <p class="tw-text-neutral-600 tw-mb-4">
                            <?= _l('appointment_client_dashboard_description'); ?>
                        </p>
                        <a href="<?= site_url('appointly/appointments_public/book'); ?>"
                            class="btn btn-primary" target="_blank">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?= _l('appointment_book_new'); ?>
                        </a>
                    </div>
                <?php } else { ?>
                    <!-- Filters -->
                    <div class="appointment-filters">
                        <button type="button" class="btn btn-default filter-btn active" data-filter="all">
                            <?= _l('appointment_all'); ?>
                        </button>
                        <button type="button" class="btn btn-default filter-btn" data-filter="today">
                            <?= _l('appointment_today'); ?>
                        </button>
                        <button type="button" class="btn btn-default filter-btn" data-filter="upcoming">
                            <?= _l('appointment_upcoming'); ?>
                        </button>
                        <button type="button" class="btn btn-default filter-btn" data-filter="completed">
                            <?= _l('appointment_completed'); ?>
                        </button>
                        <button type="button" class="btn btn-default filter-btn" data-filter="cancelled">
                            <?= _l('appointment_cancelled'); ?>
                        </button>
                    </div>

                    <table class="table dt-table table-client-appointments" data-order-col="1" data-order-type="desc">
                        <thead>
                            <tr>
                                <th class="tw-w-[15%]"><?= _l('appointment_subject'); ?></th>
                                <th class="tw-w-[15%]"><?= _l('appointment_service'); ?></th>
                                <th><?= _l('appointment_date_time'); ?></th>
                                <th><?= _l('appointment_status'); ?></th>
                                <th><?= _l('appointment_staff_attendees'); ?></th>
                                <th><?= _l('appointment_service_price'); ?></th>
                                <th><?= _l('invoice'); ?></th>
                                <th class="tw-text-center"><?= _l('options'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment) {
                                // Simplified logic for row and indicator styling
                                $timestamp = strtotime($appointment['date'] . ' ' . $appointment['start_hour']);
                                $is_past = $timestamp < time();
                                $is_cancelled = in_array($appointment['status'], ['cancelled', 'no-show']);

                                $row_class = '';
                                $row_style = '';
                                $date_indicator_class = '';

                                if ($is_past || $is_cancelled) {
                                    $row_class = 'table-row-danger';
                                    $row_style = 'background-color: #fef2f2;'; // Light red
                                    $date_indicator_class = 'tw-bg-red-500';
                                }
                            ?>
                                <tr data-status="<?= e($appointment['status']); ?>"
                                    data-date="<?= e($appointment['date']); ?>"
                                    data-is-today="<?= date('Y-m-d') === $appointment['date'] ? '1' : '0'; ?>"
                                    data-is-upcoming="<?= (in_array($appointment['status'], ['pending', 'in-progress']) && strtotime($appointment['date']) >= strtotime('today')) ? '1' : '0'; ?>"
                                    class="<?= $row_class; ?>"
                                    style="<?= $row_style; ?>">
                                    <td>
                                        <strong><?= e($appointment['subject']); ?></strong>
                                        <?php if (!empty($appointment['description'])) { ?>
                                            <br><small class="tw-text-neutral-600"><?= e(substr($appointment['description'], 0, 50) . (strlen($appointment['description']) > 50 ? '...' : '')); ?></small>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($appointment['service_name'])) { ?>
                                            <div class="tw-flex tw-items-center tw-gap-2 tw-w-full">
                                                <?php if (!empty($appointment['service_color'])) { ?>
                                                    <span class="tw-wh-5 tw-rounded-full tw-flex-shrink-0" style="background-color: <?= e($appointment['service_color']); ?>"></span>
                                                <?php } ?>
                                                <div class="tw-flex-1">
                                                    <div class="tw-font-medium"><?= e($appointment['service_name']); ?></div>
                                                    <?php if (!empty($appointment['service_duration'])) { ?>
                                                        <div class="tw-text-sm tw-text-neutral-600"><?= e($appointment['service_duration']); ?> <?= _l('appointly_duration_minutes'); ?></div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        <?php } else { ?>
                                            <span class="tw-text-neutral-500">-</span>
                                        <?php } ?>
                                    </td>
                                    <td data-order="<?= e($appointment['date']); ?>">
                                        <div class="tw-flex tw-items-center">
                                            <?php if (!empty($date_indicator_class)): ?>
                                                <span class="tw-w-2 tw-h-2 tw-rounded-md <?= $date_indicator_class ?> tw-mr-2 tw-flex-shrink-0"></span>
                                            <?php endif; ?>
                                            <div>
                                                <div class="tw-font-medium"><?= _d($appointment['date']); ?></div>
                                                <div class="tw-text-sm tw-text-neutral-600"><?= e($appointment['start_hour']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($appointment['status']) {
                                            case 'pending':
                                                $status_class = 'warning';
                                                break;
                                            case 'in-progress':
                                                $status_class = 'info';
                                                break;
                                            case 'completed':
                                                $status_class = 'success';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'danger';
                                                break;
                                            case 'no-show':
                                                $status_class = 'default';
                                                break;
                                            default:
                                                $status_class = 'primary';
                                        }
                                        ?>
                                        <span class="label label-<?= $status_class; ?>">
                                            <?php
                                            $status = !empty($appointment['status']) ? $appointment['status'] : 'pending';

                                            $status_mapping = [
                                                'pending' => 'appointment_status_pending',
                                                'in-progress' => 'appointment_status_in-progress',
                                                'completed' => 'appointment_status_completed',
                                                'cancelled' => 'appointment_status_cancelled',
                                                'no-show' => 'appointment_status_no-show',
                                                'approved' => 'appointment_status_pending',
                                                'confirmed' => 'appointment_status_pending'
                                            ];

                                            $lang_key = isset($status_mapping[$status]) ? $status_mapping[$status] : 'appointment_status_pending';
                                            $status_text = _l($lang_key);

                                            if ($status_text === $lang_key) {
                                                $status_text = ucfirst(str_replace(['_', '-'], ' ', $status));
                                            }

                                            echo $status_text;
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        // Get staff name - check for provider_id first, then staff_id
                                        $staff_id = !empty($appointment['provider_id']) ? $appointment['provider_id'] : (!empty($appointment['staff_id']) ? $appointment['staff_id'] : null);
                                        if ($staff_id) {
                                            $CI = &get_instance();
                                            $CI->load->model('staff_model');
                                            $staff = $CI->staff_model->get($staff_id);
                                            if ($staff) {
                                                echo e($staff->firstname . ' ' . $staff->lastname);
                                            } else {
                                                echo '-';
                                            }
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($appointment['service_price']) && $appointment['service_price'] > 0) { ?>
                                            <span class="tw-font-medium tw-text-green-600">
                                                <?= app_format_money($appointment['service_price'], get_base_currency()); ?>
                                            </span>
                                        <?php } else { ?>
                                            <span class="tw-text-neutral-500">-</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($appointment['invoice_id']) && !empty($appointment['invoice_hash'])) { ?>
                                            <div class="tw-flex tw-flex-col tw-gap-1">
                                                <a href="<?= site_url('invoice/' . $appointment['invoice_id'] . '/' . $appointment['invoice_hash']); ?>"
                                                    target="_blank"
                                                    class="tw-inline-flex tw-items-center tw-gap-1 tw-text-blue-600 hover:tw-text-blue-800 tw-text-sm tw-font-medium">
                                                    <i class="fa fa-file-invoice tw-text-xs"></i>
                                                    #<?= format_invoice_number($appointment['invoice_id']); ?>
                                                </a>
                                                <?php
                                                $status_class = '';
                                                $status_text = '';
                                                switch ($appointment['invoice_status']) {
                                                    case 1:
                                                        $status_class = 'warning';
                                                        $status_text = _l('invoice_status_unpaid');
                                                        break;
                                                    case 2:
                                                        $status_class = 'success';
                                                        $status_text = _l('invoice_status_paid');
                                                        break;
                                                    case 6:
                                                        $status_class = 'default';
                                                        $status_text = _l('invoice_status_draft');
                                                        break;
                                                    default:
                                                        $status_class = 'info';
                                                        $status_text = _l('invoice_status_' . $appointment['invoice_status']);
                                                }
                                                ?>
                                                <span class="label label-<?= $status_class; ?> tw-text-xs">
                                                    <?= $status_text; ?>
                                                </span>
                                            </div>
                                        <?php } else { ?>
                                            <span class="tw-text-neutral-500 tw-text-sm">-</span>
                                        <?php } ?>
                                    </td>
                                    <td class="tw-text-center">
                                        <div class="tw-flex tw-items-center tw-justify-center tw-space-x-1">
                                            <button type="button"
                                                class="option-icon view view-appointment"
                                                data-id="<?= $appointment['id']; ?>"
                                                data-toggle="tooltip"
                                                title="<?= _l('view'); ?>">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>

                                            <a href="<?= site_url('appointly/appointments_public/download_ics/' . $appointment['id']); ?>"
                                                class="option-icon download-ics"
                                                data-toggle="tooltip"
                                                title="<?= _l('appointment_download_ics_tooltip'); ?>">
                                                <i class="fa-solid fa-calendar-plus"></i>
                                            </a>

                                            <?php if (in_array($appointment['status'], ['pending', 'in-progress']) && strtotime($appointment['date']) >= strtotime('today')) { ?>
                                                <?php
                                                // Check if appointment already has pending requests
                                                $hasPendingReschedule = !empty($appointment['pending_reschedule']) ||
                                                    (isset($appointment['has_pending_reschedule']) && $appointment['has_pending_reschedule'] == 1);
                                                $hasPendingCancellation = isset($appointment['has_pending_cancellation']) && $appointment['has_pending_cancellation'] == 1;
                                                ?>

                                                <?php if ($hasPendingCancellation) { ?>
                                                    <span class="option-icon cancel disabled"
                                                        data-toggle="tooltip"
                                                        title="<?= _l('appointment_cancellation_pending_review'); ?>">
                                                        <i class="fa-solid fa-exclamation-triangle"></i>
                                                    </span>
                                                <?php } else { ?>
                                                    <?php if ($hasPendingReschedule) { ?>
                                                        <span class="option-icon reschedule disabled"
                                                            data-toggle="tooltip"
                                                            title="<?= _l('appointment_reschedule_pending'); ?>">
                                                            <i class="fa-solid fa-calendar"></i>
                                                        </span>
                                                    <?php } else { ?>
                                                        <button type="button"
                                                            class="option-icon reschedule reschedule-appointment"
                                                            data-id="<?= $appointment['id']; ?>"
                                                            data-toggle="tooltip"
                                                            title="<?= _l('appointment_request_reschedule'); ?>">
                                                            <i class="fa-solid fa-calendar"></i>
                                                        </button>
                                                    <?php } ?>

                                                    <button type="button"
                                                        class="option-icon cancel cancel-appointment"
                                                        data-id="<?= $appointment['id']; ?>"
                                                        data-toggle="tooltip"
                                                        title="<?= _l('appointment_cancel'); ?>">
                                                        <i class="fa-solid fa-times"></i>
                                                    </button>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- View Appointment Modal -->
<div class="modal fade" id="viewAppointmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= _l('appointment_details'); ?></h4>
            </div>
            <div class="modal-body" id="appointment-details-content">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelAppointmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= _l('appointment_cancel'); ?></h4>
            </div>
            <form id="cancelAppointmentForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cancel_reason"><?= _l('appointment_cancel_reason'); ?></label>
                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3"
                            placeholder="<?= _l('appointment_cancel_reason_placeholder'); ?>" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                    <button type="submit" class="btn btn-danger"><?= _l('appointment_cancel'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reschedule Appointment Modal -->
<div class="modal fade" id="rescheduleAppointmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= _l('appointment_request_reschedule'); ?></h4>
            </div>
            <form id="rescheduleAppointmentForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="reschedule_date"><?= _l('appointment_new_date'); ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="reschedule_date" name="reschedule_date" required readonly>
                                    <div class="input-group-addon">
                                        <i class="fa-regular fa-calendar calendar-icon"></i>
                                    </div>
                                </div>
                                <input type="hidden" id="reschedule_date_field" name="reschedule_date_field">
                                <div id="reschedule_date_loading" class="tw-hidden tw-text-center tw-mt-2">
                                    <i class="fa fa-spinner fa-spin"></i> <?= _l('loading'); ?>...
                                </div>
                            </div>
                        </div>
                        <!-- Hidden field to store selected time -->
                        <input type="hidden" id="reschedule_time_field" name="reschedule_time_field">
                    </div>

                    <!-- Time Slots Section -->
                    <div id="reschedule-time-slots-section" class="tw-hidden">
                        <div class="form-group">
                            <label><?= _l('appointment_available_times'); ?> <span class="text-danger">*</span></label>
                            <div id="reschedule-slot-loading" class="tw-text-center tw-py-4 tw-hidden">
                                <i class="fa fa-spinner fa-spin"></i> <?= _l('loading'); ?>...
                            </div>
                            <div id="reschedule-time-slots-container" class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-3 tw-gap-2 tw-mt-2">
                                <!-- Time slots will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reschedule_reason"><?= _l('appointment_reschedule_reason'); ?></label>
                        <textarea class="form-control" id="reschedule_reason" name="reschedule_reason" rows="3"
                            placeholder="<?= _l('appointment_reschedule_request_details'); ?>"></textarea>
                    </div>

                    <!-- Alert area -->
                    <div id="reschedule-alert" class="tw-hidden tw-p-4 tw-rounded-md tw-mb-4">
                        <!-- Alert content will be set by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                    <button type="submit" class="btn btn-primary" id="rescheduleSubmitBtn"><?= _l('appointment_request_reschedule'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize tooltips - including disabled elements
        $('[data-toggle="tooltip"]').tooltip({
            container: 'body',
            trigger: 'hover focus'
        });
        // Filter functionality
        $('.filter-btn').on('click', function() {
            var filter = $(this).data('filter');

            // Update active state
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');

            // Get all table rows (excluding header)
            var rows = $('.table-client-appointments tbody tr');

            // Apply filter
            if (filter === 'all') {
                // Show all rows
                rows.show();
            } else if (filter === 'today') {
                // Show only today's appointments
                rows.each(function() {
                    var isToday = $(this).data('is-today') == '1';
                    if (isToday) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            } else if (filter === 'upcoming') {
                // Show only upcoming appointments
                rows.each(function() {
                    var isUpcoming = $(this).data('is-upcoming') == '1';
                    if (isUpcoming) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            } else {
                // Filter by status
                rows.each(function() {
                    var status = $(this).data('status');
                    if (status === filter) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }

            // Update entries count display
            updateEntriesCount();
        });

        // Function to update entries count
        function updateEntriesCount() {
            var totalRows = $('.table-client-appointments tbody tr').length;
            var visibleRows = $('.table-client-appointments tbody tr:visible').length;

            // Update the DataTable info if it exists
            var infoElement = $('.dataTables_info');
            if (infoElement.length) {
                if (visibleRows === totalRows) {
                    infoElement.text('Showing 1 to ' + totalRows + ' of ' + totalRows + ' entries');
                } else {
                    infoElement.text('Showing 1 to ' + visibleRows + ' of ' + totalRows + ' entries (filtered)');
                }
            }
        }

        // View appointment details
        $('.view-appointment').on('click', function() {
            var appointmentId = $(this).data('id');

            $.ajax({
                url: '<?= site_url('appointly/appointment_clients/view_single'); ?>',
                type: 'POST',
                data: {
                    id: appointmentId
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        $('#appointment-details-content').html(data.html);
                        $('#viewAppointmentModal').modal('show');
                    } else {
                        alert(data.message || 'Error loading appointment details');
                    }
                },
                error: function() {
                    alert('Error loading appointment details');
                }
            });
        });

        // Cancel appointment
        $('.cancel-appointment').on('click', function() {
            var appointmentId = $(this).data('id');
            $('#cancelAppointmentForm').data('appointment-id', appointmentId);
            $('#cancelAppointmentModal').modal('show');
        });

        $('#cancelAppointmentForm').on('submit', function(e) {
            e.preventDefault();

            var appointmentId = $(this).data('appointment-id');
            var reason = $('#cancel_reason').val();

            if (!reason.trim()) {
                alert('<?= _l('appointment_cancellation_notes_required'); ?>');
                return;
            }

            // Show loading state
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('<?= _l('appointment_processing'); ?>...');

            $.ajax({
                url: '<?= site_url('appointly/appointment_clients/cancel_request'); ?>',
                type: 'POST',
                data: {
                    appointment_id: appointmentId,
                    reason: reason,
                    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                },
                success: function(response) {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        $('#cancelAppointmentModal').modal('hide');
                        alert('<?= _l('appointment_cancel_request_sent'); ?>');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        alert(data.message || '<?= _l('appointment_cancel_request_failed'); ?>');
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('<?= _l('appointment_cancel_request_failed'); ?>');
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });

        // Reschedule appointment
        $('.reschedule-appointment').on('click', function() {
            // Check if button is disabled
            if ($(this).hasClass('disabled') || $(this).prop('disabled')) {
                return false;
            }

            var appointmentId = $(this).data('id');
            $('#rescheduleAppointmentForm').data('appointment-id', appointmentId);

            // Reset form when opening modal
            resetRescheduleModal();

            // Initialize the reschedule date picker when modal opens
            initializeRescheduleDatePicker();

            $('#rescheduleAppointmentModal').modal('show');
        });

        // Prevent clicks on disabled reschedule spans
        $('.reschedule.disabled').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        // Reset reschedule modal
        function resetRescheduleModal() {
            // Clear form fields
            $('#reschedule_date').val('');
            $('#reschedule_date_field').val('');
            $('#reschedule_time_field').val('');
            $('#reschedule_reason').val('');

            // Hide time slots section
            $('#reschedule-time-slots-section').addClass('tw-hidden');
            $('#reschedule-time-slots-container').empty();

            // Remove any error highlighting
            $('#reschedule-time-slots-container').removeClass('tw-border-red-500 tw-border-2');

            // Clear any selected time slot buttons
            $('.reschedule-time-slot-btn').removeClass('selected tw-bg-blue-100 tw-border-blue-500');
        }

        // Also reset when modal is hidden
        $('#rescheduleAppointmentModal').on('hidden.bs.modal', function() {
            resetRescheduleModal();
        });

        $('#rescheduleAppointmentForm').on('submit', function(e) {
            e.preventDefault();

            var appointmentId = $(this).data('appointment-id');
            var newDate = $('#reschedule_date_field').val();
            var newTime = $('#reschedule_time_field').val();
            var reason = $('#reschedule_reason').val();

            if (!newDate) {
                alert('<?= _l('appointment_reschedule_date_required'); ?>');
                $('#reschedule_date').focus();
                return;
            }

            if (!newTime) {
                alert('Please select an available time slot');
                // Highlight the time slots container if visible
                if (!$('#reschedule-time-slots-section').hasClass('tw-hidden')) {
                    $('#reschedule-time-slots-container').addClass('tw-border-red-500 tw-border-2');
                    setTimeout(function() {
                        $('#reschedule-time-slots-container').removeClass('tw-border-red-500 tw-border-2');
                    }, 3000);
                }
                return;
            }

            // Check if the new date/time is in the future
            var selectedDateTime = new Date(newDate + 'T' + newTime);
            var now = new Date();
            if (selectedDateTime <= now) {
                alert('<?= _l('appointment_reschedule_future_datetime'); ?>');
                return;
            }

            // Show loading state
            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('<?= _l('appointment_processing'); ?>...');

            $.ajax({
                url: '<?= site_url('appointly/appointment_clients/reschedule_request'); ?>',
                type: 'POST',
                data: {
                    appointment_id: appointmentId,
                    new_date: newDate,
                    new_time: newTime,
                    reason: reason,
                    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                },
                success: function(response) {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success) {
                        $('#rescheduleAppointmentModal').modal('hide');
                        alert('<?= _l('appointment_reschedule_request_submitted'); ?>');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        alert(data.message || '<?= _l('appointment_reschedule_request_failed'); ?>');
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('<?= _l('appointment_reschedule_request_failed'); ?>');
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });

        // Initialize reschedule date picker
        function initializeRescheduleDatePicker() {
            // Show loading
            $('#reschedule_date_loading').removeClass('tw-hidden');

            // Get blocked days first
            $.ajax({
                url: '<?= site_url('appointly/appointment_clients/get_blocked_days'); ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var blockedDays = [];
                    if (response && response.success && response.blocked_days) {
                        blockedDays = response.blocked_days.map(function(date) {
                            return date.trim();
                        });
                    }

                    // Initialize datepicker
                    var datePickerOptions = {
                        format: 'Y-m-d',
                        timepicker: false,
                        datepicker: true,
                        scrollInput: false,
                        lazyInit: false,
                        minDate: 0, // No past dates
                        dayOfWeekStart: 0,
                        onSelectDate: function(ct, $input) {
                            var date = $input.val();
                            if (date) {
                                $('#reschedule_date_field').val(date);
                                loadRescheduleTimeSlots(date);
                                $('#reschedule-time-slots-section').removeClass('tw-hidden');
                            }
                        },
                        beforeShowDay: function(date) {
                            var dateStr = date.getFullYear() + '-' +
                                ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                                ('0' + date.getDate()).slice(-2);

                            // Check if date is blocked
                            if (blockedDays.indexOf(dateStr) !== -1) {
                                return [false, 'blocked-date'];
                            }

                            return [true, ''];
                        }
                    };

                    // Destroy existing datepicker if any
                    if ($('#reschedule_date').data('xdsoft_datetimepicker')) {
                        $('#reschedule_date').datetimepicker('destroy');
                    }

                    // Initialize new datepicker
                    $('#reschedule_date').datetimepicker(datePickerOptions);
                    $('#reschedule_date_loading').addClass('tw-hidden');

                },
                error: function() {
                    console.error('Error loading blocked days');
                    $('#reschedule_date_loading').addClass('tw-hidden');

                    // Initialize with basic settings
                    var basicOptions = {
                        format: 'Y-m-d',
                        timepicker: false,
                        datepicker: true,
                        scrollInput: false,
                        lazyInit: false,
                        minDate: 0,
                        onSelectDate: function(ct, $input) {
                            var date = $input.val();
                            if (date) {
                                $('#reschedule_date_field').val(date);
                                loadRescheduleTimeSlots(date);
                                $('#reschedule-time-slots-section').removeClass('tw-hidden');
                            }
                        }
                    };

                    if ($('#reschedule_date').data('xdsoft_datetimepicker')) {
                        $('#reschedule_date').datetimepicker('destroy');
                    }
                    $('#reschedule_date').datetimepicker(basicOptions);
                }
            });
        }

        // Load reschedule time slots - EXACT COPY FROM CLIENT HASH
        function loadRescheduleTimeSlots(date) {
            

            // Show loading
            $('#reschedule-slot-loading').removeClass('tw-hidden');
            $('#reschedule-time-slots-container').empty();

            // Get appointment data for the current appointment being rescheduled
            var appointmentId = $('#rescheduleAppointmentForm').data('appointment-id');

            var ajaxData = {
                appointment_id: appointmentId,
                date: date
            };

            // Add CSRF token
            ajaxData["<?= $this->security->get_csrf_token_name(); ?>"] = "<?= $this->security->get_csrf_hash(); ?>";

            $.ajax({
                url: '<?= site_url('appointly/appointment_clients/get_available_times'); ?>',
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                success: function(response) {
                    $('#reschedule-slot-loading').addClass('tw-hidden');

                    if (response && response.success && response.times && response.times.length > 0) {
                        var hasAvailableSlots = false;
                        var container = $('#reschedule-time-slots-container');

                        response.times.forEach(function(slot) {
                            if (slot.available !== false) {
                                hasAvailableSlots = true;

                                var timeSlotBtn = $('<button>', {
                                    type: 'button',
                                    class: 'reschedule-time-slot-btn',
                                    'data-start-time': slot.start_time,
                                    'data-end-time': slot.end_time,
                                    'data-text': slot.start_time + ' - ' + slot.end_time,
                                    text: slot.start_time + ' - ' + slot.end_time
                                });

                                timeSlotBtn.on('click', function(e) {
                                    e.preventDefault();

                                    // Remove selected class from all buttons
                                    $('.reschedule-time-slot-btn').removeClass('selected tw-bg-blue-100 tw-border-blue-500');

                                    // Add selected class to this button
                                    $(this).addClass('selected tw-bg-blue-100 tw-border-blue-500');

                                    // Store selected time
                                    $('#reschedule_time_field').val($(this).data('start-time'));

                                    console.log('Selected reschedule time:', $(this).data('start-time'));
                                });

                                container.append(timeSlotBtn);
                            }
                        });

                        if (!hasAvailableSlots) {
                            container.html('<div class="tw-col-span-full tw-text-center tw-py-4 tw-text-neutral-500"><?= _l('appointment_no_available_times'); ?></div>');
                        }
                    } else {
                        $('#reschedule-time-slots-container').html('<div class="tw-col-span-full tw-text-center tw-py-4 tw-text-neutral-500"><?= _l('appointment_no_available_times'); ?></div>');
                    }
                },
                error: function() {
                    $('#reschedule-slot-loading').addClass('tw-hidden');
                    $('#reschedule-time-slots-container').html('<div class="tw-col-span-full tw-text-center tw-py-4 tw-text-red-600"><?= _l('appointment_error_loading_times'); ?></div>');
                }
            });
        }

        // Calendar icon click handler for reschedule date
        $(document).on('click', '#rescheduleAppointmentModal .calendar-icon', function() {
            $('#reschedule_date').focus();
        });

        // Reset reschedule modal when closed
        $('#rescheduleAppointmentModal').on('hidden.bs.modal', function() {
            // Reset form fields
            $('#reschedule_date').val('');
            $('#reschedule_date_field').val('');
            $('#reschedule_time_field').val('');
            $('#reschedule_reason').val('');

            // Hide time slots section
            $('#reschedule-time-slots-section').addClass('tw-hidden');
            $('#reschedule-time-slots-container').empty();

            // Hide alerts
            $('#reschedule-alert').addClass('tw-hidden');

            // Destroy datepicker if it exists
            if ($('#reschedule_date').data('xdsoft_datetimepicker')) {
                $('#reschedule_date').datetimepicker('destroy');
            }
        });
    });
</script>

<style>
    .tw-text-blue-600 {
        color: rgb(29 78 216/var(--tw-text-opacity)) !important;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .table-responsive {
            font-size: 14px;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }
    }

    /* Action buttons styling */
    .btn-group-actions .btn {
        margin-right: 3px;
    }

    .btn-group-actions .btn:last-child {
        margin-right: 0;
    }

    .tw-wh-5 {
        width: 0.875rem;
        height: 0.875rem;
    }
</style>
