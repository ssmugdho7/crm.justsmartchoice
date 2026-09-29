<!--suppress JSJQueryEfficiency -->
<script>
    var appointly_please_wait = "<?= _l("wait_text"); ?>";
    var appointly_lang_finished = "<?= _l('appointment_marked_as_finished'); ?>";
    var appointly_lang_approved = "<?= _l('appointment_marked_as_approved'); ?>";
    var appointly_lang_cancelled = "<?= _l('appointment_is_cancelled'); ?>";
    var appointly_lang_no_show = "<?= _l('appointment_marked_as_no_show'); ?>";
    var appointly_mark_as_ongoing = "<?= _l('appointment_marked_as_ongoing'); ?>";
    var appointly_are_you_sure_mark_as_cancelled = "<?= _l('appointment_are_you_sure_to_cancel'); ?>";
    var appointly_are_you_sure_mark_as_no_show = "<?= _l('appointment_are_you_sure_mark_as_no_show'); ?>";
    var appointment_are_you_sure_mark_as_ongoing = "<?= _l('appointment_are_you_sure_to_mark_as_ongoing'); ?>";
    var appointly_are_you_early_reminders = "<?= _l('appointly_are_you_early_reminders'); ?>";
    var appointly_reminders_sent = "<?= _l('appointly_reminders_sent'); ?>";
    var filters = <?php echo json_encode($filters); ?>;
    var customBlockedDates = <?php
                                $bd = get_option('appointly_blocked_days');
                                echo $bd ? json_encode(json_decode($bd, true)) : '[]';
                                ?>;

    $(function() {
        $(document).on('click', '.finished-no-click-action', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

        var apointmentsServerParams = {};

        // Get all filters as params
        for (var filter in filters) {
            apointmentsServerParams[filters[filter]] = "[name=\"" + filters[filter] + "\"]";
        }

        // custom parameter for Google Calendar filter
        apointmentsServerParams['custom_view'] = function() {
            return $('input[name="custom_view"]').val();
        };

        let appointmentsTable = initDataTable(".table-appointments",
            admin_url + 'appointly/appointments/table',
            [],
            [6], // No non-sortable columns
            apointmentsServerParams,
            [2, "desc"], // Default sort by appointment date, descending
            undefined, {
                // Error handling for DataTables
                "error": function(xhr, error, thrown) {
                    console.error("DataTables error:", error, thrown);
                    alert_float('danger', 'Error loading appointments: ' + (thrown || 'Unknown error'));
                    // Prevent DataTables from showing error alert
                    return true;
                },
                // Better error handling for JSON parsing issues
                "ajax": {
                    "dataSrc": function(json) {
                        if (json && json.data) {
                            return json.data;
                        } else if (json && json.aaData) {
                            return json.aaData;
                        } else {
                            console.log("Invalid JSON response structure:", json);
                            return [];
                        }
                    },
                    "error": function(xhr, error, thrown) {
                        console.error("AJAX error:", error, thrown);
                        if (xhr.responseText) {
                            console.log("Response:", xhr.responseText.substring(0, 1000));
                        }
                        // Return empty dataset to prevent DataTables error
                        return {
                            data: []
                        };
                    }
                }
            }
        );

        $(document).ready(function() {
            // Apply to any table with the appointments table class
            $(document).on('draw.dt', '.appointments-table', function() {
                // For each row in the table
                $(this).find('tbody tr').each(function() {
                    // Check if this is a Google synced appointment by looking for:
                    // 1. Google event ID element or 
                    // 2. Google icon indicator or
                    // 3. Data attribute indicating Google source
                    var isGoogleSynced = $(this).find('.google-event-id').length > 0 ||
                        $(this).find('.google-calendar-icon').length > 0 ||
                        $(this).data('source') === 'google_calendar';

                    if (isGoogleSynced) {
                        // Find and disable ALL dropdowns in this row
                        $(this).find('select').prop('disabled', true)
                            .addClass('google-synced-disabled')
                            .attr('title', 'Changes not available for Google Calendar events');

                        // Also disable any action buttons if needed
                        $(this).find('.dropdown-toggle[data-toggle="dropdown"]').addClass('disabled')
                            .attr('title', 'Actions limited for Google Calendar events');
                    }
                });
            });
        });

        $("body").on("click", ".approve_appointment", function() {
            $(this).attr("disabled", true);
            $(this).prev().next().addClass("approve_appointment_spacing");
            $(this).html("<i class=\"fa fa-refresh fa-spin fa-fw\"></i>");
        });

        $("#createNewAppointment").click(function() {
            createNewAppointment();
        });

        function createNewAppointment(type = null) {
            // Redirect to standalone create page
            let url = admin_url + 'appointly/appointments/create_page';

            // If type is specified, add it as a query parameter
            if (type) {
                url += '?rel_type=' + type;
            }

            window.location.href = url;
        }

        // filter click handler
        $('body').on('click', '.filter-group a', function(e) {
            e.preventDefault();
            var customView = $(this).data('cview');
            $('input[name="custom_view"]').val(customView);

            // Update active state
            $('.filter-group').removeClass('active');
            $(this).closest('.filter-group').addClass('active');

            // Reload table with filter
            appointmentsTable.ajax.reload();
        });
    });

    $(".modal").on("hidden.bs.modal", function(e) {
        $(this).removeData();
    });

    var todaysDate = new Date();

    var currentDate = todaysDate.getFullYear() + "-" + (((todaysDate.getMonth() + 1) < 10) ? "0" : "") + (todaysDate
        .getMonth() + 1 + "-" + ((todaysDate.getDate() < 10) ? "0" : "") + todaysDate.getDate());
    // Update the busy times check to use the selected duration
    function checkTimeSlotAvailability(startTime, duration, busyTimes) {
        var start = moment(startTime, 'HH:mm');
        var end = moment(startTime, 'HH:mm').add(duration || 60, 'minutes');

        for (var i = 0; i < busyTimes.length; i++) {
            var busyStart = moment(busyTimes[i].start_hour, 'HH:mm');
            var busyEnd = moment(busyTimes[i].start_hour, 'HH:mm')
                .add(busyTimes[i].duration || 60, 'minutes');

            // Check if slots overlap
            if ((start.isSameOrAfter(busyStart) && start.isBefore(busyEnd)) ||
                (end.isAfter(busyStart) && end.isSameOrBefore(busyEnd)) ||
                (start.isBefore(busyStart) && end.isAfter(busyEnd))) {
                return false;
            }
        }

        return true;
    }

    // Create a new task directly from relation, related options selected after modal is shown
    function new_task_from_relation_appointment(data) {
        var url = admin_url + 'tasks/task';

        // Initialize new task modal
        new_task(url);

        // When task modal is shown
        $('#_task').on('show.bs.modal', function(e) {
            var $modal = $(this);

            // Set task name with appointment subject
            $modal.find('input[name="name"]').val(data.subject);
            // set hourly rate to task modal
            console.log(data.service_price);
            $modal.find('input[name="hourly_rate"]').val(data.service_price);
            // set description to task modal
            var description = data.description;
            // Handle description field (might be TinyMCE or regular textarea)
            if (typeof(tinymce) != 'undefined') {
                var descEditor = tinymce.get('description');
                if (descEditor) {
                    descEditor.setContent(description);
                }
            } else {
                $modal.find('textarea[name="description"]').val(description);
            }

            // Set start date to appointment date
            $modal.find('input[name="startdate"]').val(data.date);

            // Set priority (if needed)
            $modal.find('select[name="priority"]').val(2).trigger('change');

            // Assign to appointment creator
            if (data.created_by) {
                $modal.find('select[name="assignees[]"]')
                    .val(data.created_by)
                    .trigger('change');
            }

            // Mark as not billable by default
            $modal.find('input[name="billable"]').prop('checked', false);

            // Pre-fill client/prospect information based on appointment source
            if (data.source && data.contact_id) {
                var relType = '';
                var relId = '';

                // Determine relationship type and ID based on appointment source
                if (data.source === 'internal') {
                    // Internal appointment with existing client contact
                    relType = 'customer';
                    relId = data.contact_id;
                } else if (data.source === 'lead_related') {
                    // Lead-related appointment
                    relType = 'lead';
                    relId = data.contact_id;
                } else if (data.source === 'external' && data.name) {
                    // External appointment - could be converted to lead
                    if (data.contact_id) {
                        relType = 'lead';
                        relId = data.contact_id;
                    }
                }

                // Set the relationship type if we have one
                if (relType) {
                    $modal.find('select[name="rel_type"]').val(relType).trigger('change');

                    // Wait a moment for the rel_id field to be populated, then set the ID
                    setTimeout(function() {
                        if (relId) {
                            // For customer relationships, we need to get the client ID from contact ID
                            if (relType === 'customer') {
                                // Use the existing helper function to get client ID from contact ID
                                $.ajax({
                                    url: admin_url + 'appointly/appointments/fetch_contact_data',
                                    type: 'POST',
                                    data: {
                                        contact_id: relId,
                                        is_lead: 'false'
                                    },
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.success && response.data && response.data.userid) {
                                            $modal.find('select[name="rel_id"]').val(response.data.userid).trigger('change');
                                        }
                                    },
                                    error: function() {
                                        console.log('Could not get client ID from contact ID');
                                    }
                                });
                            } else {
                                // For leads, use the contact_id directly
                                $modal.find('select[name="rel_id"]').val(relId).trigger('change');
                            }
                        }
                    }, 500);
                }
            }

            // Refresh any select2 elements
            $modal.find('select').trigger('change');
        });
    }

    // Init lead convert to lead for appointment
    function init_appointment_lead(data) {
        // Initialize the lead modal
        if (init_lead_modal_data(undefined, undefined, false)) {
            $('#lead-modal').modal('show');
        }

        // When modal is shown, populate the fields
        $('#lead-modal').on('shown.bs.modal', function(e) {
            var $modal = $(this);

            // Set modal title
            $modal.find('.add-title').text(app.lang.appointments_convert_to_lead);

            // Basic info
            $modal.find('input[name="name"]').val(data.name);
            $modal.find('input[name="email"]').val(data.email);
            $modal.find('input[name="phonenumber"]').val(data.phone);

            // Additional fields
            $modal.find('input[name="address"]').val(data.address);
            $modal.find('input[name="city"]').val(data.city);
            $modal.find('input[name="state"]').val(data.state);
            $modal.find('input[name="country"]').val(data.country);
            $modal.find('input[name="zip"]').val(data.zip);

            // Enhanced description with appointment details
            var description = "<?= _l('appointment_label') ?>" + ': ' + data.subject + '\n\n';

            // Add appointment details
            if (data.service_name) {
                description += "<?= _l('appointment_service') ?>" + ': ' + data.service_name + '\n';
            }
            if (data.date) {
                description += "<?= _l('appointment_date') ?>" + ': ' + data.date + '\n';
            }
            description += '\n' + (data.description || '');

            $modal.find('textarea[name="description"]').val(description);

            // Set source if exists (Website = 2, Phone = 1, etc.)
            var $sourceSelect = $modal.find('select[name="source"]');
            if ($sourceSelect.length) {
                $sourceSelect.val(1).trigger('change');
            }

            // Set status if exists (New = 2, Contacted = 1, etc.)
            var $statusSelect = $modal.find('select[name="status"]');
            if ($statusSelect.length) {
                $statusSelect.val(2).trigger('change');
            }

            // Set company name if available (use name as company for external appointments)
            var $companyField = $modal.find('input[name="company"]');
            if ($companyField.length && data.name) {
                $companyField.val(data.name);
            }

            // Refresh any select2 elements
            $modal.find('select').trigger('change');
        });
    }

    // Request appointment feedback
    function request_appointment_feedback(appointment_id) {
        $("body").append("<div class=\"dt-loader\"></div>");

        var url = admin_url + "appointly/appointments/requestAppointmentFeedback/" + appointment_id;
        $.post(url).done(function(response) {

            if (response.success == true) {
                alert_float("info", "<?= _l("appointment_feedback_requested_alert"); ?>");
                $("body").find(".dt-loader").remove();
            }
        }).fail(function(err) {
            console.log(err)

            $("body").find(".dt-loader").remove();
            console.log("An unknown error has been thrown" + err);
        });
    }

    // Delete appointment from database
    function deleteAppointment(id, element) {
        if (confirm("<?= _l('appointment_are_you_sure'); ?>")) {
            // Check if it's a Google synced appointment
            var isGoogleSynced = $(element).closest('tr').find('.fab.fa-google').length > 0 ||
                !$.isNumeric(id); // Google IDs are typically not numeric

            if (isGoogleSynced) {
                // For Google synced appointments, we need to delete from Google Calendar
                $.ajax({
                    url: admin_url + "appointly/appointments/deleteGoogleSyncedAppointment/" + id,
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function() {
                        $(".table-appointments").append("<div class=\"dt-loader\"></div>");
                    },
                    success: function(response) {
                        if (response && response.success) {
                            alert_float('success', response.message || "<?= _l('appointment_deleted'); ?>");
                            // Force reload the table to remove the deleted appointment
                            $(".table-appointments").DataTable().ajax.reload(null, false);
                        }
                    },
                    complete: function() {
                        $(".table-appointments").find(".dt-loader").remove();
                    }
                });
            } else {
                // Regular appointment delete
                $.ajax({
                    url: admin_url + "appointly/appointments/delete/" + id,
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function() {
                        $(".table-appointments").append("<div class=\"dt-loader\"></div>");
                    },
                    success: function(response) {
                        if (response && response.success) {
                            alert_float('success', response.message || "<?= _l('appointment_deleted'); ?>");
                            $(".table-appointments").DataTable().ajax.reload(null, false);
                        }
                    },
                    complete: function() {
                        $(".table-appointments").find(".dt-loader").remove();
                    }
                });
            }
        }
    }

    // Separate function specifically for Google synced appointments
    function deleteGoogleSyncedAppointment(element) {
        // Get the Google event ID from the data attribute
        var googleEventId = $(element).data('google-event-id');
        console.log('Google Event ID from data attribute:', googleEventId);

        // If still no ID found, show error
        if (!googleEventId || googleEventId === '') {
            alert_float('danger', 'Could not find Google Calendar event ID');
            return;
        }

        if (confirm("<?= _l('appointment_are_you_sure'); ?>\n\nThis will delete the event from Google Calendar directly.")) {
            $.ajax({
                url: admin_url + "appointly/appointments/deleteGoogleSyncedAppointment/" + googleEventId,
                type: 'POST',
                dataType: 'json',
                beforeSend: function() {
                    $(".table-appointments").append("<div class=\"dt-loader\"></div>");
                },
                success: function(response) {
                    if (response && response.success) {
                        alert_float('success', response.message || "<?= _l('appointment_deleted'); ?>");
                        // Force reload the table to remove the deleted appointment
                        $(".table-appointments").DataTable().ajax.reload(null, false);
                    }
                },
                complete: function() {
                    $(".table-appointments").find(".dt-loader").remove();
                }
            });
        }
    }

    /**
     * Check if user is logged in to outlook
     *
     * @return boolean
     */
    function isOutlookLoggedIn() {
        if (typeof myMSALObj !== "undefined" && myMSALObj.getAccount()) {
            return true;
        }
        return false;
    }

    // Mark appointment as finished
    function markAppointmentAsFinished(id) {
        $.post("appointments/finished", {
            id: id,
            beforeSend: function() {
                $(".table-appointments").append("<div class=\"dt-loader\"></div>");
            }
        }).done(function(r) {
            if (r.success == true) {
                alert_float("success", appointly_lang_finished);
                $(".table-appointments").DataTable().ajax.reload();
            }
            $(".table-appointments").find(".dt-loader").remove();
        });
    }

    // Mark appointment as approved
    function markAppointmentAsApproved(id) {
        var $table = $(".table-appointments");
        $table.append("<div class=\"dt-loader\"></div>");

        $.ajax({
            url: <?= json_encode(admin_url('appointly/appointments/approve')); ?>,
            type: "POST",
            dataType: "json",
            data: { appointment_id: id }
        }).done(function(response) {
            if (response && (response.success === true || response.result === true)) {
                alert_float("success", response.message || appointly_lang_approved);
                $table.DataTable().ajax.reload(null, false);
                return;
            }

            alert_float("danger", (response && response.message) ? response.message : "Unable to approve the appointment.");
        }).fail(function(xhr) {
            var message = "Unable to approve the appointment.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    var parsed = JSON.parse(xhr.responseText);
                    message = parsed.message || message;
                } catch (e) {
                    console.error('Appointly approval response:', xhr.responseText);
                }
            }
            alert_float("danger", message);
        }).always(function() {
            $table.find(".dt-loader").remove();
        });
    }

    // Mark appointment as cancelled
    function markAppointmentAsCancelled(id) {
        if (confirm(appointly_are_you_sure_mark_as_cancelled)) {
            $.ajax({
                url: "appointments/cancel_appointment",
                type: "POST",
                dataType: "json",
                data: {
                    id: id
                },
                beforeSend: function() {
                    $(".table-appointments").append('<div class="dt-loader"></div>');
                },
                success: function(response) {
                    if (response.success === true) {
                        alert_float("success", appointly_lang_cancelled);
                        $(".table-appointments").DataTable().ajax.reload();
                    } else {
                        var message = response.message || "Cancellation failed";
                        alert_float("warning", message);
                    }
                },
                complete: function() {
                    $(".table-appointments").find(".dt-loader").remove();
                }
            });
        }
    }

    // Mark appointment as ongoing if marked as cancelled
    function markAppointmentAsOngoing(id) {
        if (confirm(appointment_are_you_sure_mark_as_ongoing)) {
            console.log("Starting mark as ongoing request for appointment ID:", id);

            $.ajax({
                url: "appointments/mark_as_ongoing_appointment",
                type: "POST",
                dataType: "json",
                data: {
                    id: id
                },
                beforeSend: function() {
                    console.log("Sending request to mark appointment as ongoing...");
                    $(".table-appointments").append("<div class=\"dt-loader\"></div>");
                },
                success: function(response) {
                    console.log("Received response:", response);

                    if (response && response.success === true) {
                        alert_float("success", appointly_mark_as_ongoing);
                    } else {
                        var message = response && response.message ? response.message : "Status change failed";
                        alert_float("warning", message);
                        console.error("Error in markAppointmentAsOngoing:", response);
                    }
                    $(".table-appointments").DataTable().ajax.reload();
                },
                complete: function() {
                    console.log("Request completed");
                    $(".table-appointments").find(".dt-loader").remove();
                }
            });
        }
    }

    /**
     * Approve cancellation request
     * @param {number} appointment_id - The appointment ID to approve cancellation for
     */
    function approveCancellation(appointment_id) {
        if (!appointment_id) {
            alert_float('danger', 'Invalid appointment ID');
            return;
        }

        if (!confirm("<?= _l('appointment_are_you_sure_to_cancel'); ?>")) {
            return;
        }

        $.ajax({
            url: admin_url + "appointly/appointments/cancel_appointment",
            type: "POST",
            dataType: "json",
            data: {
                id: appointment_id
            },
            beforeSend: function() {
                $("body").append('<div class="dt-loader"></div>');
            },
            success: function(response) {
                if (response.success === true) {
                    alert_float("success", "<?= _l('appointment_cancellation_approved'); ?>");
                    // Reload the page to reflect the changes
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    var message = response.message || "Approval failed";
                    alert_float("warning", message);
                }
            },
            complete: function() {
                $("body").find(".dt-loader").remove();
            }
        });
    }

    // Mark appointment as no-show
    function markAppointmentAsNoShow(id) {
        if (confirm(appointly_are_you_sure_mark_as_no_show)) {
            $.ajax({
                url: "appointments/mark_as_no_show",
                type: "POST",
                dataType: "json",
                data: {
                    id: id
                },
                beforeSend: function() {
                    $(".table-appointments").append('<div class="dt-loader"></div>');
                },
                success: function(response) {
                    if (response.success === true) {
                        alert_float("success", appointly_lang_no_show);
                        $(".table-appointments").DataTable().ajax.reload();
                    } else {
                        var message = response.message || "Status change failed";
                        alert_float("warning", message);
                    }
                },
                complete: function() {
                    console.log("Request completed");
                    $(".table-appointments").find(".dt-loader").remove();
                    $(".table-appointments").DataTable().ajax.reload();
                }
            });
        }
    }
</script>
