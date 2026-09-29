<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h4 class="customer-profile-group-heading"><?= _l('appointment_appointments'); ?></h4>

<?php
$CI = &get_instance();
$CI->load->model('appointly/appointly_model');
$appointments = $CI->appointly_model->get_client_appointments($client->userid);

// Register JavaScript for client appointments tab through the app_admin_footer hook
hooks()->add_action('app_admin_footer', function () use ($client) {
?>
    <script>
        $(function() {
            console.log('Initializing contact search for client ID:', <?= $client->userid; ?>);

            // Load all contacts for this client and populate dropdown
            var $contactDropdown = $("#contact_id");
            if ($contactDropdown.length) {
                // First, load all contacts for this client
                $.ajax({
                    url: admin_url + "misc/get_relation_data",
                    type: "POST",
                    dataType: "json",
                    data: {
                        type: "contact",
                        rel_id: "",
                        q: "", // Empty query to get all contacts
                        extra: {
                            client_id: <?= $client->userid; ?>
                        }
                    },
                    success: function(response) {
                        console.log('Loaded contacts for client:', response);

                        // Clear existing options except the first one
                        $contactDropdown.find('option:not(:first)').remove();

                        // Add all contacts as options
                        if (response && response.length > 0) {
                            $.each(response, function(index, contact) {
                                var option = $('<option></option>')
                                    .attr('value', contact.id)
                                    .text(contact.name);

                                if (contact.subtext) {
                                    option.attr('data-subtext', contact.subtext);
                                }

                                $contactDropdown.append(option);
                            });
                        }

                        // Refresh the selectpicker to show new options
                        $contactDropdown.selectpicker('refresh');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading contacts:', error);
                    }
                });

                // Initialize selectpicker with search functionality
                $contactDropdown.selectpicker({
                    liveSearch: true,
                    size: 10
                });
            }

            // Debug: Intercept AJAX requests to see what's being sent
            $(document).ajaxSend(function(event, xhr, settings) {
                if (settings.url && settings.url.indexOf('get_relation_data') !== -1) {
                    console.log('AJAX request to get_relation_data:', {
                        url: settings.url,
                        data: settings.data,
                        type: settings.type
                    });
                }
            });

            // Debug: Check if the contact dropdown is properly initialized
            setTimeout(function() {
                var $contactDropdown = $("#contact_id");
                console.log('Contact dropdown found:', $contactDropdown.length > 0);
                console.log('Contact dropdown classes:', $contactDropdown.attr('class'));
                console.log('Contact dropdown options:', $contactDropdown.find('option').length);
            }, 1000);

            // Handle the create appointment button click
            $("#btn-new-appointment").on("click", function() {
                var contactId = $("#contact_id").val();
                var contactText = $("#contact_id option:selected").text();
                var url = admin_url + "appointly/appointments/create_page?client_id=<?= $client->userid; ?>";

                console.log('Selected contact ID:', contactId, 'Type:', typeof contactId);
                console.log('Selected contact text:', contactText);
                console.log('All available options:', $("#contact_id option").map(function() {
                    return {
                        value: this.value,
                        text: this.text
                    };
                }).get());

                if (contactId) {
                    url += "&contact_id=" + contactId;
                }

                console.log('Redirecting to:', url);
                window.location.href = url;
            });

            // Status filter functionality
            $(".status-filter").click(function() {
                $(".status-filter").removeClass("active");
                $(this).addClass("active");

                var status = $(this).data("status");
                if (status === "all") {
                    $("table tr").show();
                } else {
                    $("table tr").hide();
                    $("table tr[data-status=\"" + status + "\"]").show();
                    $("table thead tr").show(); // Always show header row
                }
            });
        });

        // Delete appointment function for profile pages
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
<?php
});
?>

<div class="tw-mb-4 tw-flex tw-justify-between tw-items-center">
    <div class="appointment-create-wrapper tw-flex tw-items-center">
        <div class="tw-mr-3">
            <select id="contact_id" name="contact_id" class="ajax-search" data-width="300px" data-live-search="true">
                <option value=""><?= _l('appointment_select_contact'); ?></option>
            </select>
        </div>
        <button type="button" class="btn btn-primary" id="btn-new-appointment">
            <i class="fa fa-plus tw-mr-1"></i> <?= _l('appointment_new_appointment'); ?>
        </button>
    </div>
</div>

<?php if (count($appointments) > 0) { ?>
    <div class="table-responsive">
        <table class="table dt-table tw-mt-2" data-order-col="1" data-order-type="desc">
            <thead>
                <tr>
                    <th><?= _l('appointment_subject'); ?></th>
                    <th><?= _l('appointment_date_and_time'); ?></th>
                    <th><?= _l('appointment_status'); ?></th>
                    <th><?= _l('appointment_service'); ?></th>
                    <th><?= _l('appointment_provider'); ?></th>
                    <th><?= _l('appointment_initiated_by'); ?></th>
                    <th><?= _l('appointment_location'); ?></th>
                    <th><?= _l('options'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment) { ?>
                    <tr data-status="<?= $appointment['status']; ?>">
                        <!-- Subject -->
                        <td class="tw-font-medium"><?= $appointment['subject']; ?></td>

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
                                $service_name = get_service_name($appointment['service_id']);
                                $CI->db->select('color');
                                $CI->db->where('id', $appointment['service_id']);
                                $service = $CI->db->get(db_prefix() . 'appointly_services')->row();
                                $serviceClass = $service->color ?? '#3B82F6';
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
                                        <img src="<?= staff_profile_image_url($appointment['created_by'], 'small'); ?>"
                                            class="tw-w-8 tw-h-8 tw-rounded-md tw-mr-2"
                                            alt="<?= $creator_name; ?>" />
                                        <span class="tw-text-sm tw-font-medium"><?= $creator_name; ?></span>
                                    </a>
                                </div>
                            <?php else: ?>
                                <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-neutral-50 tw-text-neutral-700">N/A</span>
                            <?php endif; ?>
                        </td>

                        <!-- Location -->
                        <td>
                            <?php if (!empty($appointment['address'])): ?>
                                <span class="tw-text-sm"><?= $appointment['address']; ?></span>
                            <?php else: ?>
                                <span class="tw-text-sm tw-text-neutral-500">N/A</span>
                            <?php endif; ?>
                        </td>

                        <!-- Options -->
                        <td>
                            <a href="<?= admin_url('appointly/appointments/view?appointment_id=' . $appointment['id']) ?>" class="btn btn-default btn-icon" title="<?= _l('view') ?>" data-toggle="tooltip">
                                <i class="fa fa-eye"></i>
                            </a>
                            <?php if (staff_can('delete', 'appointments') || $appointment['created_by'] == get_staff_user_id()): ?>
                                <button type="button" class="btn btn-danger btn-icon" onclick="deleteAppointmentFromProfile(<?= $appointment['id'] ?>, this)" title="<?= _l('delete') ?>" data-toggle="tooltip">
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
        <div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-6">
            <div class="tw-mb-4 tw-text-center">
                <i class="fa fa-calendar-plus-o fa-3x tw-text-neutral-500"></i>
                <h4 class="tw-mt-3 tw-font-medium"><?= _l('no_appointments_found'); ?></h4>
                <p class="tw-mb-5 tw-text-neutral-600"><?= _l('would_you_like_to_create_new_appointment_for_client'); ?></p>
            </div>
            <a href="<?= admin_url('appointly/appointments/create_page?client_id=' . $client->userid) ?>" class="btn btn-primary">
                <i class="fa fa-calendar-plus-o tw-mr-2"></i> <?= _l('appointment_new_appointment'); ?>
            </a>
        </div>
    </div>
<?php } ?>
