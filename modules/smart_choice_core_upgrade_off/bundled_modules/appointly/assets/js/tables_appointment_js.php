<script>
    // Language variables
    var appointly_lang_finished = "<?= _l('appointment_marked_as_finished'); ?>";
    var appointly_lang_cancelled = "<?= _l('appointment_is_cancelled'); ?>";
    var appointly_mark_as_ongoing = "<?= _l('appointment_marked_as_ongoing'); ?>";
    var appointment_are_you_sure_mark_as_ongoing = "<?= _l('appointment_are_you_sure_to_mark_as_ongoing') ?>";
    var appointly_are_you_sure_mark_as_cancelled = "<?= _l('appointment_are_you_sure_to_cancel') ?>";
    var appointly_are_you_early_reminders = "<?= _l('appointly_are_you_early_reminders') ?>";
    var appointly_reminders_sent = "<?= _l('appointly_reminders_sent') ?>";
    var appointly_please_wait = "<?= _l('appointment_please_wait'); ?>";

    //  body class
    $(function() {
        let el = $('body').find('#google_not_set');
        setTimeout(() => {
            if ($(el).is(":visible")) $(el).fadeOut();
        }, 5000)

        $("body").addClass("single_view_board");
    });

    // Set active menu state
    $('li.menu-item-appointly').addClass('active');
    $('li.menu-item-appointly > ul').addClass('in');
    $('li.sub-menu-item-appointly-user-dashboard').addClass('active');

    // Gather staff attendee emails for Google Meet
    var attendees = window.google_meet_attendees || [];

    $(".modal").on("hidden.bs.modal", function(e) {
        tinymce.remove("textarea[name=\"google_meet_notify_message\"]");
        $(this).removeData();
    });

    // Mark appointment as finished
    function markAppointmentAsFinished() {
        var url = window.location.search;
        var id = url.split("=")[1];

        $.ajax({
            url: "change_appointment_status",
            type: "POST",
            dataType: "json",
            data: {
                id: id,
                status: 'completed'
            },
            beforeSend: function() {
                disableButtonsAfterPost($("#markAsFinished"));
            },
            success: function(response) {
                if (response && response.success === true) {
                    alert_float("success", appointly_lang_finished);

                    // If on the appointments table, refresh it
                    if ($('#appointments_table').length) {
                        $('#appointments_table').DataTable().ajax.reload();
                    } else {
                        // Otherwise reload the page
                        reloadlocation(800);
                    }
                } else {
                    var message = response && response.message ? response.message : "Status change failed";
                    alert_float("warning", message);
                    console.error("Error marking as finished:", response);
                }
            },
        });
    }

    // Cancel appointment
    function cancelAppointment() {
        var url = window.location.search;
        var id = url.split("=")[1];

        if (confirm(appointly_are_you_sure_mark_as_cancelled)) {
            $.ajax({
                url: "change_appointment_status",
                type: "POST",
                dataType: "json",
                data: {
                    id: id,
                    status: 'cancelled'
                },
                beforeSend: function() {
                    disableButtonsAfterPost($("#cancelAppointment"));
                },
                success: function(response) {
                    if (response && response.success === true) {
                        alert_float("success", appointly_lang_cancelled);

                        // If on the appointments table, refresh it
                        if ($('#appointments_table').length) {
                            $('#appointments_table').DataTable().ajax.reload();
                        } else {
                            // Otherwise reload the page
                            reloadlocation(800);
                        }
                    } else {
                        var message = response && response.message ? response.message : "Status change failed";
                        alert_float("warning", message);
                        console.error("Error cancelling appointment:", response);
                    }
                },
            });
        }
    }

    // Mark appointment as ongoing if marked as cancelled
    function markAppointmentAsOngoing() {
        var url = window.location.search;
        var id = url.split("=")[1];

        if (confirm(appointment_are_you_sure_mark_as_ongoing)) {
            $.ajax({
                url: "change_appointment_status",
                type: "POST",
                dataType: "json",
                data: {
                    id: id,
                    status: 'in-progress'
                },
                beforeSend: function() {
                    disableButtonsAfterPost($("#markAppointmentAsOngoing"));
                },
                success: function(response) {
                    if (response && response.success === true) {
                        alert_float("success", appointly_mark_as_ongoing);

                        // If on the appointments table, refresh it
                        if ($('#appointments_table').length) {
                            $('#appointments_table').DataTable().ajax.reload();
                        } else {
                            // Otherwise reload the page
                            reloadlocation(800);
                        }
                    } else {
                        var message = response && response.message ? response.message : "Status change failed";
                        alert_float("warning", message);
                        console.error("Error in markAppointmentAsOngoing:", response);
                    }
                },
            });
        }
    }

    // Mark appointment as no-show
    function markAppointmentAsNoShow() {
        var url = window.location.search;
        var id = url.split("=")[1];

        $.ajax({
            url: "change_appointment_status",
            type: "POST",
            dataType: "json",
            data: {
                id: id,
                status: 'no-show'
            },
            beforeSend: function() {
                disableButtonsAfterPost($("#markAsNoShow"));
            },
            success: function(response) {
                if (response && response.success === true) {
                    alert_float("success", response.message || "Appointment marked as no-show");

                    // If on the appointments table, refresh it
                    if ($('#appointments_table').length) {
                        $('#appointments_table').DataTable().ajax.reload();
                    } else {
                        // Otherwise reload the page
                        reloadlocation(800);
                    }
                } else {
                    var message = response && response.message ? response.message : "Status change failed";
                    alert_float("warning", message);
                    console.error("Error marking as no-show:", response);
                }
            },
        });
    }

    //  Trigger appointment reminders
    function sendAppointmentReminders() {
        var url = window.location.search;
        var id = url.split("=")[1];

        if (confirm(appointly_are_you_early_reminders)) {
            $.post("send_appointment_early_reminders", {
                id: id,
                beforeSend: function() {
                    disableButtonsAfterPost($("#sendAppointmentReminders"));
                },
            }).done(function(r) {
                r = JSON.parse(r);
                if (r.success == true) {
                    alert_float("success", appointly_reminders_sent);
                    reloadlocation(2000);
                }
            });
        }
    }

    function sendAppointmentRemindersEmail() {

        var message = $("#google_meet_notify_message").val();

        if ($.trim(message) == "") {
            alert_float("warning", "<?= _l('appointment_google_meet_message_required'); ?>");
            return false;
        }

        $("#submit_google_meet_email_btn").html("<i class=\"fa fa-refresh fa-spin fa-fw\"></i> <?= _l('sending'); ?>").attr("disabled", true);

        // Use the global attendees variable set in the view
        var attendees = window.google_meet_attendees || [];
        var notifyAttendees = $("#notify_attendees_checkbox").is(':checked');

        // Debug: Log the attendees to verify they're being used correctly
        console.log('Attendees available:', attendees);
        console.log('Notify attendees checkbox checked:', notifyAttendees);
        console.log('Final attendees to send:', notifyAttendees ? attendees : []);

        var emailData = {
            message: message,
            client_external_url: "<?= isset($appointment['public_url']) ? $appointment['public_url'] : ''; ?>",
            google_meet_link: "<?= isset($appointment['google_meet_link']) ? $appointment['google_meet_link'] : ''; ?>",
            to: "<?= isset($appointment['email']) ? $appointment['email'] : ''; ?>", // client email
            attendees: notifyAttendees ? attendees : [] // only send to attendees if checkbox is checked
        };

        $.post("send_appointment_custom_email", emailData).done(function(r) {
            // Handle both string/boolean and JSON responses
            try {
                if (typeof r === 'string') {
                    r = JSON.parse(r);
                }
            } catch (e) {
                // If parsing fails, treat as legacy boolean response
                r = {
                    success: r === true || r === 'true'
                };
            }

            if (r.success) {
                // Show detailed success message if available
                var message = r.message || "<?= _l('appointment_meeting_request_sent'); ?>";
                if (r.total_sent && r.total_sent > 1) {
                    message += " (" + r.total_sent + " recipients)";
                }
                alert_float("success", message);
                $("#customEmailModal").modal('hide');
                reloadlocation(1500);
            } else {
                var errorMessage = r.message || "<?= _l('appointment_email_send_failed'); ?>";
                alert_float("warning", errorMessage);
                $("#submit_google_meet_email_btn").html("<?= _l('send'); ?>").attr("disabled", false);
            }
        }).fail(function() {
            alert_float("danger", "<?= _l('appointment_email_send_failed'); ?>");
            $("#submit_google_meet_email_btn").html("<?= _l('send'); ?>").attr("disabled", false);
        });
    }

    // Disable buttons
    function disableButtonsAfterPost(button) {
        $("#markAsFinished").attr("disabled", true);
        $("#confirmDelete").attr("disabled", true);
        $("#cancelAppointment").attr("disabled", true);
        $("#markAppointmentAsOngoing").attr("disabled", true);
        $(".btn-primary-google").attr("disabled", true);
        button.html("" + appointly_please_wait + "<i class=\"fa fa-refresh fa-spin fa-fw\"></i>");
    }

    // Disable delete button used in view as function
    function disableButtonsAfterDelete() {
        $("button").attr("disabled", true);
        $("a").addClass("disabled");
        return false;
    }

    // Simple reload
    function reloadlocation(timer) {
        setTimeout(function() {
            location.reload();
        }, timer);
    }

    // Initialize editor
    init_editor('textarea[name="notes"]', {
        menubar: true,
    });

    // Save notes function
    function save_appointment_notes() {
        var $btn = $('#privNotesBtn');
        var notes = tinymce.get('notes').getContent();
        var appointment_id = '<?php echo $appointment['id']; ?>';
        var csrf_token_name = $('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').attr('name');
        var csrf_token_value = $('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').val();

        $btn.prop('disabled', true).html($btn.data('loading-text'));

        var data = {
            appointment_id: appointment_id,
            notes: notes
        };

        // Add CSRF token
        data[csrf_token_name] = csrf_token_value;

        $.post(admin_url + 'appointly/appointments/save_notes_from_appointment_view', data)
            .done(function(response) {
                response = JSON.parse(response);
                if (response.success) {
                    alert_float('success', "<?= _l('appointment_notes_updated'); ?>");
                }
            })
            .fail(function() {
                alert_float('danger', "<?= _l('appointment_notes_update_failed'); ?>");
            })
            .always(function() {
                $btn.prop('disabled', false).html("<?= _l('save'); ?>");
            });
    }

    // Initialize tabs
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr("href");
        if (target === '#tab_notes') {
            // Reinitialize editor if needed
            if (typeof(tinymce) != 'undefined') {
                init_editor('#notes');
            }
        }
    });

    // Initialize editor for notes
    if (typeof(tinymce) != 'undefined') {
        init_editor('#notes', {
            menubar: false,
            height: 150
        });
    }

    // Description editing functions
    function editDescription() {
        $('#description_display').hide();
        $('#description_placeholder').hide();
        $('#description_edit_btn').hide();
        $('#description_editor').show();

        // Initialize TinyMCE editor
        init_editor('#appointment_description', {
            menubar: false,
            height: 200,
            setup: function(editor) {
                editor.on('init', function() {
                    editor.focus();
                });
            }
        });
    }

    function saveDescription() {
        var content = tinymce.get('appointment_description').getContent();
        var appointmentId = $('#appointment_description').data('appointment-id');

        $.post(admin_url + 'appointly/appointments/save_appointment_description', {
                appointment_id: appointmentId,
                description: content
            })
            .done(function(response) {
                try {
                    response = JSON.parse(response);
                    if (response.success) {
                        alert_float('success', response.message);
                        // Update display
                        if (content.trim() === '') {
                            $('#description_display').hide();
                            $('#description_placeholder').show();
                            $('#description_edit_btn').hide();
                        } else {
                            $('#description_display').html(content).show();
                            $('#description_placeholder').hide();
                            $('#description_edit_btn').show();
                        }
                        cancelDescription();
                    } else {
                        alert_float('danger', response.message);
                    }
                } catch (e) {
                    alert_float('danger', 'Error parsing response');
                }
            })
            .fail(function() {
                alert_float('danger', 'Failed to save description');
            });
    }

    function cancelDescription() {
        tinymce.remove('#appointment_description');
        $('#description_editor').hide();

        // Show appropriate elements
        var hasContent = $('#description_display').text().trim() !== '';
        if (hasContent) {
            $('#description_display').show();
            $('#description_edit_btn').show();
        } else {
            $('#description_placeholder').show();
        }
    }

    // Notes editing functions
    function editNotes() {
        $('#notes_display').hide();
        $('#notes_placeholder').hide();
        $('#notes_edit_btn').hide();
        $('#notes_editor').show();

        // Initialize TinyMCE editor
        init_editor('#appointment_notes', {
            menubar: false,
            height: 200,
            setup: function(editor) {
                editor.on('init', function() {
                    editor.focus();
                });
            }
        });
    }

    function saveNotes() {
        var content = tinymce.get('appointment_notes').getContent();
        var appointmentId = $('#appointment_notes').data('appointment-id');

        $.post(admin_url + 'appointly/appointments/save_notes_from_appointment_view', {
                appointment_id: appointmentId,
                notes: content
            })
            .done(function(response) {
                try {
                    response = JSON.parse(response);
                    if (response.success) {
                        alert_float('success', response.message);
                        // Update display
                        if (content.trim() === '') {
                            $('#notes_display').hide();
                            $('#notes_placeholder').show();
                            $('#notes_edit_btn').hide();
                        } else {
                            $('#notes_display').html(content).show();
                            $('#notes_placeholder').hide();
                            $('#notes_edit_btn').show();
                        }
                        cancelNotes();
                    } else {
                        alert_float('danger', response.message);
                    }
                } catch (e) {
                    alert_float('danger', 'Error parsing response');
                }
            })
            .fail(function() {
                alert_float('danger', 'Failed to save notes');
            });
    }

    function cancelNotes() {
        tinymce.remove('#appointment_notes');
        $('#notes_editor').hide();

        // Show appropriate elements
        var hasContent = $('#notes_display').text().trim() !== '';
        if (hasContent) {
            $('#notes_display').show();
            $('#notes_edit_btn').show();
        } else {
            $('#notes_placeholder').show();
        }
    }

    // Cleanup TinyMCE when modal closes
    $("#customEmailModal").on("hidden.bs.modal", function() {
        tinymce.remove("textarea[name='google_meet_notify_message']");
        $(this).removeData();
    });

    // Hide "Google API Key not set" alert after 5s
    let el = $('#google_not_set');
    if (el.length) {
        setTimeout(function() {
            el.fadeOut();
        }, 5000);
    }

    // Enhanced Google Meet Functions

    // Copy Google Meet link to clipboard
    function copyGoogleMeetLink(meetLink) {
        // Use modern clipboard API
        navigator.clipboard.writeText(meetLink).then(function(r) {
            // Show success message in modal if available
            const successMsg = document.getElementById('copySuccessMessage');
            if (successMsg) {
                successMsg.classList.remove('hidden');
                successMsg.style.display = 'block';

                setTimeout(() => {
                    successMsg.classList.add('hidden');
                    successMsg.style.display = 'none';
                }, 3000);
                alert_float('success', "<?= _l('appointment_google_meet_link_copied'); ?>");
            }
        })
    }
    /**
     * Approve cancellation request
     * @param {number} appointment_id - The appointment ID to approve cancellation for
     */
    function approveCancellation(appointment_id) {
        if (!appointment_id) {
            alert_float('danger', "<?= addslashes(_l('invalid_appointment_id')); ?>");
            return;
        }

        if (!confirm("<?= _l('appointment_are_you_sure_to_cancel') ?>")) {
            return;
        }

        $.ajax({
            url: admin_url + 'appointly/appointments/cancel_appointment',
            type: 'POST',
            data: {
                id: appointment_id
            },
            success: function(response) {
                var result = typeof response === 'string' ? JSON.parse(response) : response;
                if (result.success) {
                    alert_float('success', (typeof _l !== 'undefined' ? _l('appointment_cancellation_approved') : 'Cancellation approved'));
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    alert_float('danger', "<?= addslashes(_l('appointment_cancellation_approval_failed')); ?>: " + (result.message || "<?= addslashes(_l('unknown_error')); ?>"));
                }

            },
            error: function(xhr, status, error) {
                alert_float('danger', "<?= addslashes(_l('request_failed')); ?>: " + error);
            }
        });
    }

    // Request feedback from client
    function requestFeedback(appointmentId) {
        if (confirm("<?= _l('appointments_are_you_sure_request_feedback'); ?>")) {
            $.post(admin_url + 'appointly/appointments/requestAppointmentFeedback', {
                appointment_id: appointmentId
            }).done(function(response) {
                try {
                    // Make sure response is properly parsed as JSON
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    if (response.success) {
                        alert_float('success', "<?= addslashes(_l('appointment_feedback_requested_alert')); ?>");
                    } else {
                        alert_float('warning', response.message || "<?= addslashes(_l('something_went_wrong')); ?>");
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert_float('danger', "<?= addslashes(_l('something_went_wrong')); ?>");
                }
            }).fail(function() {
                alert_float('danger', "<?= addslashes(_l('something_went_wrong')); ?>");
            });
        }
    }

    // Convert appointment to invoice
    function convertAppointmentToInvoice(appointmentId, viewAfterConvert = false) {
        if (confirm("<?= addslashes(_l('appointment_are_you_sure_convert_to_invoice')); ?>")) {
            $.post(admin_url + 'appointly/appointments/convert_to_invoice', {
                appointment_id: appointmentId
            }).done(function(response) {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    response = response;
                }

                if (response.success) {
                    alert_float('success', response.message);
                    if (viewAfterConvert && response.invoice_id) {
                        // Redirect to view the invoice
                        window.location.href = admin_url + 'invoices/invoice/' + response.invoice_id;
                    } else {
                        reloadlocation(1000);
                    }
                } else {
                    alert_float('danger', response.message || "<?= addslashes(_l('appointment_convert_to_invoice_failed')); ?>");
                }
            }).fail(function(xhr, textStatus, errorThrown) {
                alert_float('danger', "<?= addslashes(_l('appointment_convert_to_invoice_failed')); ?>");
                console.error(xhr.responseText || errorThrown);
            });
        }
    }

    // Delete Google Calendar integration from appointment
    function deleteGoogleIntegration(appointmentId, googleEventId) {
        if (!appointmentId || !googleEventId) {
            alert_float('danger', "<?= addslashes(_l('appointment_missing_required_fields')); ?>");
            return;
        }

        if (confirm("<?= addslashes(_l('appointment_confirm_remove_google_integration')); ?>")) {
            $.ajax({
                url: admin_url + 'appointly/appointments/deleteGoogleEvent',
                type: 'POST',
                data: {
                    appointment_id: appointmentId,
                    google_event_id: googleEventId,
                    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                },
                beforeSend: function() {
                    // Disable the button to prevent multiple clicks
                    $('button[onclick*="deleteGoogleIntegration(' + appointmentId + ')"]').prop('disabled', true);
                },
                success: function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }

                        if (response.success) {
                            alert_float('success', response.message);
                            // Reload the page to reflect changes
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            alert_float('danger', response.message || "<?= addslashes(_l('appointment_google_removal_failed')); ?>");
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert_float('danger', "<?= addslashes(_l('something_went_wrong')); ?>");
                    }
                },
                error: function(xhr, status, error) {
                    alert_float('danger', "<?= addslashes(_l('request_failed')); ?>: " + error);
                },
                complete: function() {
                    // Re-enable the button
                    $('button[onclick*="deleteGoogleIntegration(' + appointmentId + ')"]').prop('disabled', false);
                }
            });
        }
    }

    // Delete Outlook Calendar integration from appointment
    function deleteOutlookIntegration(appointmentId, outlookEventId) {
        if (!appointmentId || !outlookEventId) {
            alert_float('danger', "<?= addslashes(_l('appointment_missing_required_fields')); ?>");
            return;
        }

        // Check if user is authenticated with Outlook
        var isAuthenticated = false;
        var authMessage = "";

        if (typeof isOutlookLoggedIn === 'function') {
            isAuthenticated = isOutlookLoggedIn();
            if (!isAuthenticated) {
                authMessage = "<?= addslashes(_l('appointment_outlook_not_authenticated_warning')); ?>";
            }
        } else {
            authMessage = "<?= addslashes(_l('appointment_outlook_not_available_warning')); ?>";
        }

        // Show confirmation with authentication warning if needed
        var confirmMessage = "<?= addslashes(_l('appointment_confirm_remove_outlook_integration')); ?>";
        if (authMessage) {
            confirmMessage = authMessage + "\n\n" + confirmMessage;
        }

        if (confirm(confirmMessage)) {
            // First try to delete from Outlook Calendar via API if authenticated
            if (isAuthenticated && typeof deleteOutlookEvent === 'function') {
                try {
                    // Call the function to delete from Outlook Calendar (without showing alert)
                    deleteOutlookEvent(outlookEventId, false);
                } catch (e) {
                    console.warn('Failed to delete from Outlook Calendar:', e);
                }
            }

            // Always remove from local appointment
            $.ajax({
                url: admin_url + 'appointly/appointments/deleteOutlookEvent',
                type: 'POST',
                data: {
                    appointment_id: appointmentId,
                    outlook_event_id: outlookEventId,
                    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                },
                beforeSend: function() {
                    // Disable the button to prevent multiple clicks
                    $('button[onclick*="deleteOutlookIntegration(' + appointmentId + ')"]').prop('disabled', true);
                },
                success: function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }

                        if (response.success) {
                            // Show appropriate success message based on authentication status
                            var successMessage = response.message;
                            if (!isAuthenticated) {
                                successMessage = "<?= addslashes(_l('appointment_outlook_integration_removed_local_only')); ?>";
                            }
                            alert_float('success', successMessage);

                            // Reload the page to reflect changes
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            alert_float('danger', response.message || "<?= addslashes(_l('appointment_outlook_removal_failed')); ?>");
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert_float('danger', "<?= addslashes(_l('something_went_wrong')); ?>");
                    }
                },
                error: function(xhr, status, error) {
                    alert_float('danger', "<?= addslashes(_l('request_failed')); ?>: " + error);
                },
                complete: function() {
                    // Re-enable the button
                    $('button[onclick*="deleteOutlookIntegration(' + appointmentId + ')"]').prop('disabled', false);
                }
            });
        }
    }
</script>

<style>
    .mce-widget.mce-btn.mce-menubtn.mce-flow-layout-item.mce-last.mce-btn-has-text,
    .mce-widget.mce-btn.mce-splitbtn.mce-colorbutton.mce-last,
    .mce-widget.mce-btn.mce-splitbtn.mce-colorbutton.mce-first {
        display: none;
    }
</style>
