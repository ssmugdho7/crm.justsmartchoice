<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(function() {
        // Status filter functionality
        $('.status-filter').click(function() {
            $('.status-filter').removeClass('active');
            $(this).addClass('active');

            var status = $(this).data('status');
            if (status === 'all') {
                $('.lead-appointments-table tr').show();
            } else {
                $('.lead-appointments-table tr').hide();
                $('.lead-appointments-table tr[data-status="' + status + '"]').show();
                $('.lead-appointments-table thead tr').show();
            }
        });
    });

    function deleteAppointmentFromProfile(appointmentId, element) {
        if (confirm("<?= str_replace('"', '\\"', _l('appointment_are_you_sure')); ?>")) {
            $.ajax({
                url: admin_url + "appointly/appointments/delete/" + appointmentId,
                type: "POST",
                dataType: "json",
                data: {
                    <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
                },
                beforeSend: function() {
                    $(element).prop("disabled", true).html("<i class=\"fa fa-spinner fa-spin\"></i>");
                },
                success: function(response) {
                    if (response && response.success) {
                        alert_float("success", response.message || "<?= str_replace('"', '\\"', _l('appointment_deleted')); ?>");
                        // Remove the row from the table
                        $(element).closest("tr").fadeOut(300, function() {
                            $(this).remove();
                            // Check if table is empty and show message
                            if ($("table tbody tr:visible").length === 0) {
                                location.reload(); // Reload to show "no appointments" message
                            }
                        });
                    } else {
                        console.error('Error deleting appointment: ' + response);
                        $(element).prop("disabled", false).html("<i class=\"fa fa-trash\"></i>");
                    }
                }
            });
        }
    }
</script>
<!-- Lead Appointments Table -->
<div class="panel_s">
    <div class="panel-body">
        <div class="tw-mb-4 tw-flex tw-justify-between tw-items-center">
            <!-- Quick filter buttons -->
            <div class="btn-group">
                <button type="button" class="btn btn-default btn-sm status-filter active" data-status="all">
                    <?= _l('all'); ?>
                </button>
                <button type="button" class="btn btn-default btn-sm status-filter" data-status="pending">
                    <?= _l('appointment_status_pending'); ?>
                </button>
                <button type="button" class="btn btn-default btn-sm status-filter" data-status="in-progress">
                    <?= _l('appointment_status_in-progress'); ?>
                </button>
                <button type="button" class="btn btn-default btn-sm status-filter" data-status="completed">
                    <?= _l('appointment_status_completed'); ?>
                </button>
                <button type="button" class="btn btn-default btn-sm status-filter" data-status="cancelled">
                    <?= _l('appointment_status_cancelled'); ?>
                </button>
            </div>

            <!-- New appointment button for lead -->
            <a href="<?= admin_url('appointly/appointments/create_page?rel_type=lead_related&rel_id=' . $lead->id); ?>" class="btn btn-primary">
                <i class="fa fa-plus tw-mr-1"></i> <?= _l('appointment_new_appointment'); ?>
            </a>
        </div>

        <?php if (count($appointments) > 0) { ?>
            <div class="table-responsive lead-appointments-table">
                <table class="table dt-table" data-order-col="1" data-order-type="desc">
                    <thead>
                        <tr>
                            <th><?= _l('appointment_date_and_time'); ?></th>
                            <th><?= _l('appointment_status'); ?></th>
                            <th><?= _l('appointment_service'); ?></th>
                            <th><?= _l('appointment_provider'); ?></th>
                            <th><?= _l('appointment_initiated_by'); ?></th>
                            <th><?= _l('options'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment) { ?>
                            <tr data-status="<?= $appointment['status']; ?>">
                                <!-- Date and Time -->
                                <td>
                                    <div class="tw-flex tw-items-center">
                                        <?php
                                        // Get date status class
                                        $timestamp = strtotime($appointment['date'] . ' ' . $appointment['start_hour']);
                                        $now = time();
                                        $diff = $timestamp - $now;

                                        if ($diff < 0) {
                                            $dateClass = 'tw-bg-red-500';
                                        } elseif ($diff < 24 * 3600) {
                                            $dateClass = 'tw-bg-amber-500';
                                        } elseif ($diff < 7 * 24 * 3600) {
                                            $dateClass = 'tw-bg-blue-500';
                                        } else {
                                            $dateClass = 'tw-bg-green-500';
                                        }
                                        ?>
                                        <span class="tw-w-2 tw-h-2 tw-rounded-md <?= $dateClass ?> tw-mr-2"></span>
                                        <span class="tw-text-sm"><?= _dt($appointment['date'] . ' ' . $appointment['start_hour']); ?></span>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td>
                                    <?php
                                    // Determine status class
                                    $statusClass = '';
                                    $statusText = '';

                                    switch ($appointment['status']) {
                                        case 'cancelled':
                                            $statusClass = 'tw-bg-danger-200 tw-text-danger-700';
                                            $statusText = _l('appointment_status_cancelled');
                                            break;
                                        case 'completed':
                                            $statusClass = 'tw-bg-success-50 tw-text-success-700';
                                            $statusText = _l('appointment_status_completed');
                                            break;
                                        case 'in-progress':
                                            $statusClass = 'tw-bg-primary-50 tw-text-primary-700';
                                            $statusText = _l('appointment_status_in-progress');
                                            break;
                                        case 'confirmed':
                                        case 'approved':
                                            $statusClass = 'tw-bg-success-50 tw-text-success-700';
                                            $statusText = _l('appointment_approved');
                                            break;
                                        case 'pending':
                                            $statusClass = 'tw-bg-warning-50 tw-text-warning-700';
                                            $statusText = _l('appointment_status_pending');
                                            break;
                                        case 'no-show':
                                            $statusClass = 'tw-bg-danger-200 tw-text-danger-700';
                                            $statusText = _l('appointment_status_no-show');
                                            break;
                                        default:
                                            $statusClass = 'tw-bg-neutral-50 tw-text-neutral-700';
                                            $statusText = ucfirst(str_replace('-', ' ', $appointment['status']));
                                    }

                                    echo '<div class="tw-inline-block">
                                        <span class="tw-px-3 tw-py-1 tw-text-sm tw-font-medium tw-rounded-md ' . $statusClass . '">
                                            ' . $statusText . '
                                        </span>
                                    </div>';
                                    ?>
                                </td>

                                <!-- Service -->
                                <td>
                                    <?php if (!empty($appointment['service_id'])):
                                        $CI = &get_instance();
                                        $service_name = get_service_name($appointment['service_id']);
                                        $CI->db->select('color');
                                        $CI->db->where('id', $appointment['service_id']);
                                        $service = $CI->db->get(db_prefix() . 'appointly_services')->row();
                                        $serviceClass = !empty($service) ? $service->color : '#3B82F6';
                                    ?>
                                        <a href="<?= admin_url('appointly/services/service/' . $appointment['service_id']); ?>" class="text-primary">
                                            <span class="tw-px-2 tw-py-1 tw-text-xs tw-rounded-md tw-text-white" style="background-color: <?= $serviceClass; ?>;">
                                                <?= $service_name; ?>
                                            </span>
                                        </a>
                                    <?php else: ?>
                                        <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-neutral-50 tw-text-neutral-700">N/A</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Provider -->
                                <td>
                                    <?php if (!empty($appointment['provider_id'])):
                                        $provider = get_staff($appointment['provider_id']);
                                        $provider_name = $provider ? $provider->firstname . ' ' . $provider->lastname : 'N/A';
                                    ?>
                                        <div class="tw-flex tw-items-center">
                                            <a href="<?= admin_url('staff/profile/' . $appointment['provider_id']); ?>" class="text-primary">
                                                <span class="tw-text-sm tw-font-medium"><?= $provider_name; ?></span>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-neutral-50 tw-text-neutral-700">N/A</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Organizer (created by) -->
                                <td>
                                    <?php if (!empty($appointment['created_by'])):
                                        $creator = get_staff($appointment['created_by']);
                                        $creator_name = $creator ? $creator->firstname . ' ' . $creator->lastname : 'N/A';
                                    ?>
                                        <div class="tw-flex tw-items-center">
                                            <a href="<?= admin_url('staff/profile/' . $appointment['created_by']); ?>" class="text-primary">
                                                <span class="tw-text-sm tw-font-medium"><?= $creator_name; ?></span>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-neutral-50 tw-text-neutral-700">N/A</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Options -->
                                <td>
                                    <a href="<?= admin_url('appointly/appointments/view?appointment_id=' . $appointment['id']); ?>" class="btn btn-default btn-icon" title="<?= _l('view'); ?>" data-toggle="tooltip">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <?php if (staff_can('delete', 'appointments') || $appointment['created_by'] == get_staff_user_id()): ?>
                                        <button type="button" class="btn btn-danger btn-icon" onclick="deleteAppointmentFromProfile(<?= $appointment['id']; ?>, this)" title="<?= _l('delete'); ?>" data-toggle="tooltip">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="alert alert-info">
                <div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-4">
                    <p class="tw-mb-4"><?= _l('no_appointments_found'); ?></p>
                    <p class="tw-mb-4"><?= _l('would_you_like_to_create_new_appointment_for_lead'); ?></p>
                    <a href="<?= admin_url('appointly/appointments/create_page?rel_type=lead_related&rel_id=' . $lead->id); ?>" class="btn btn-primary">
                        <i class="fa fa-calendar-plus-o tw-mr-1"></i> <?= _l('appointment_new_appointment'); ?>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
