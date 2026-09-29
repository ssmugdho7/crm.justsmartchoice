<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(function() {
        const disableWeekends = <?php echo get_option('appointments_disable_weekends') ?> == "1" ? [0, 6] : [];
        console.log('disableWeekends', disableWeekends);
        // Check if Outlook functions exist before calling them
        if (typeof isOutlookLoggedIn === 'function' && isOutlookLoggedIn()) {
            if (typeof acquireTokenPopupAndCallMSGraph === 'function') {
                acquireTokenPopupAndCallMSGraph();
            }
        }
        tinymce.remove("#description");
        tinymce.remove("#notes");

        // we need to reinitialize on modal open
        if (typeof init_editor === 'function') {

            init_editor('textarea[name="description"]', {
                menubar: false,
                height: 150
            });

            init_editor('textarea[name="notes"]', {
                menubar: false,
                height: 100
            });
        }

        // Initialize selectpicker
        if (typeof init_selectpicker === 'function') {
            init_selectpicker();
        }

        // Initialize datetimepicker
        if (typeof initAppointmentScheduledDates === 'function') {
            initAppointmentScheduledDates();
        }

        // Service and provider handling
        var source = $('input[name="source"]').val();

        // Only handle service/provider logic for non-staff internal appointments
        if (source !== 'internal_staff') {
            // Service change event
            $('#service_id').on('change', function() {
                var serviceId = $(this).val();
                var serviceOption = $(this).find('option:selected');
                var duration = serviceOption.attr('data-content') ?
                    $(serviceOption.attr('data-content')).find('small').text().match(/\d+/)[0] : '';

                // Set hidden duration field if needed
                if (duration) {
                    if (!$('input[name="duration"]').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'duration',
                            value: duration
                        }).appendTo('#appointment-form');
                    } else {
                        $('input[name="duration"]').val(duration);
                    }
                }

                // Load available providers for this service
                if (serviceId) {
                    // You could add AJAX call here to get providers available for this service
                    // For now, just enable the provider dropdown
                    $('#provider_id').prop('disabled', false).selectpicker('refresh');
                } else {
                    $('#provider_id').prop('disabled', true).selectpicker('refresh');
                }
            });

            // Trigger service change to initialize hidden fields
            $('#service_id').trigger('change');
        }

        // Modal cleanup
        $('.modal').on('hidden.bs.modal', function(e) {
            let accessToken = document.getElementById('ms-access-token');
            if (accessToken != null) accessToken.value = '';
            $('.xdsoft_datetimepicker').remove();
        });

        // Initialize AJAX search for leads or customers based on source
        if (source === 'lead_related') {
            // For leads, initialize the lead search
            init_ajax_search('leads', '#rel_id', {});

            // Set initial lead data if exists
            if (typeof leadData !== 'undefined') {
                $('#lead_email').val(leadData.email);
                $('#lead_phone').val(leadData.phone);
            }

            // Handle lead selection change
            $('#rel_id').on('change', function() {
                var leadId = $(this).val();
                if (leadId) {
                    $.post(admin_url + 'appointly/appointments/fetch_contact_data', {
                        contact_id: leadId,
                        lead: true,
                        csrf_token_name: csrfData.hash
                    }).done(function(response) {
                        var data = typeof response === 'string' ? JSON.parse(response) : response;
                        $('#lead_email').val(data.email || '');
                        $('#lead_phone').val(data.phonenumber || '');
                    });
                } else {
                    $('#lead_email').val('');
                    $('#lead_phone').val('');
                }
            });
        } else if (source === 'internal') {
            // For contacts, initialize the contact search
            init_ajax_search('contacts', '#rel_id', {});

            $('#rel_id').on('change', function() {
                var contactId = $(this).val();
                if (contactId) {
                    $.post(admin_url + 'appointly/appointments/fetch_contact_data', {
                        contact_id: contactId,
                        lead: false,
                        csrf_token_name: csrfData.hash
                    }).done(function(response) {
                        var data = typeof response === 'string' ? JSON.parse(response) : response;
                        $('#contact_email').val(data.email || '');
                        $('#contact_phone').val(data.phonenumber || '');
                    });
                } else {
                    $('#contact_email').val('');
                    $('#contact_phone').val('');
                }
            });
        }
        // Apply form validation for all appointment types
        if (typeof appValidateForm === 'function') {
            $('#appointment-form').data('validator', null); // Clear existing validator

            var validationRules = {
                subject: 'required',
                date: 'required',
                'attendees[]': {
                    required: true,
                    minlength: 1
                }
            };

            //  contact_id validation for leads and internal sources
            if (source === 'lead_related' || source === 'internal') {
                validationRules.contact_id = 'required';
            }

            //  external contact validation
            if (source === 'external') {
                validationRules.name = 'required';
                validationRules.email = {
                    required: true,
                    email: true
                };
                validationRules.phone = 'required';
            }

            appValidateForm($('#appointment-form'), validationRules, appointmentFormSubmitHandler);
        }


        setTimeout(() => {
            let $input = $('input[name="date"]');
            if (!$input.length) return;

            let picker = $input.datetimepicker('getValue');
            if (picker) {
                $input.datetimepicker('destroy');
            }
            $input.datetimepicker({
                format: app.options.date_format + ' H:i',
                disabledWeekDays: disableWeekends.length > 0 ? disableWeekends : [],
                step: 30
            });
        }, 500);


    });

    function appointmentFormSubmitHandler(form) {
        var $form = $(form);
        var formData = $form.serialize();
        var $submitBtn = $form.find('button[type="submit"]');

        // Disable the submit button and all other action buttons
        $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $form.find('.modal-body').addClass('filterBlur');
        $form.find('button, input[type="submit"], .close_btn, #addToOutlookBtn, #addToGoogleBtn').prop('disabled', true);

        // For internal_staff meetings, ensure there's a duration
        if (formData.includes('source=internal_staff') || formData.includes('rel_type=internal_staff')) {
            // Add default duration (60 minutes) if not provided
            if (!formData.includes('duration=')) {
                formData += '&duration=60';
            } else if (formData.includes('duration=&')) {
                formData = formData.replace('duration=&', 'duration=60&');
            }
        }

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                try {
                    response = typeof response === 'string' ? JSON.parse(response) : response;

                    if (response.result || response.success) {
                        alert_float('success', '<?= _l('appointment_updated'); ?>');
                        $('.table-appointments').DataTable().ajax.reload(null, false);
                        $('#appointmentModal').modal('hide');
                    } else {
                        handleFormError(response.message || "<?= _l('appointment_error_occurred'); ?>");
                    }
                } catch (err) {
                    console.error('Response JSON parsing failed:', err);
                    handleFormError("<?= _l('appointment_update_failed'); ?>");
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                handleFormError('<?= _l('something_went_wrong'); ?>');
            }
        });

        return false;
    }

    // Helper function to handle form errors
    function handleFormError(message) {
        alert_float('danger', message);
        $('button[type="submit"], button.close_btn, #addToOutlookBtn').prop('disabled', false);
        $('button[type="submit"]').html("<?= _l('submit') ?>");
        $('#appointment-form .modal-body').removeClass('filterBlur');
        $('.modal-title').html("<?= _l('appointment_edit_appointment') ?>");
    }

    function addEventToOutlookCalendar(button, appointmentId) {
        if (typeof isOutlookLoggedIn !== 'function' || !isOutlookLoggedIn()) {
            if (typeof signInToOutlook === 'function') {
                signInToOutlook();
            } else {
                alert_float('danger', "<?= _l('appointment_outlook_auth_error'); ?>");
            }
            return;
        }

        var $btn = $(button);
        $btn.prop('disabled', true)
            .html('<i class="fa fa-refresh fa-spin fa-fw"></i> <?= _l("appointment_calendar_adding_to_outlook") ?>');

        if (typeof addToOutlookNewEventFromUpdate === 'function') {
            addToOutlookNewEventFromUpdate(appointmentId);
        } else {
            alert_float('danger', "<?= _l('appointment_outlook_error'); ?>");
            $btn.prop('disabled', false)
                .html('<?= _l("appointment_add_to_outlook"); ?> <i class="fa fa-envelope"></i>');
        }
    }

    function addEventToGoogleCalendar(button) {
        var form = $('#appointment-form').serialize();
        var url = "<?= admin_url('appointly/appointments/addEventToGoogleCalendar'); ?>";
        var modalBody = $('#appointment-form .modal-body');

        $.ajax({
            url: url,
            type: "POST",
            data: form,
            beforeSend: function() {
                $(button).attr('disabled', true);
                $('.modal .btn').attr('disabled', true);
                modalBody.addClass('filterBlur');
                $(button).html("<?= _l('appointment_please_wait'); ?>" + '<i class="fa fa-refresh fa-spin fa-fw"></i>');
            },
            success: function(r) {
                try {
                    if (typeof r === 'string') {
                        r = JSON.parse(r);
                    }

                    if (r.result == 'success') {
                        alert_float('success', r.message);
                        $('.modal').modal('hide');
                        if (typeof DataTable === 'function' && $('.table-appointments').length) {
                            $('.table-appointments').DataTable().ajax.reload();
                        } else {
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        }
                    } else if (r.result == 'error') {
                        alert_float('danger', r.message);
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert_float('danger', "<?= _l('appointment_google_calendar_error'); ?>");
                }
                modalBody.removeClass('filterBlur');
                $(button).attr('disabled', false);
                $('.modal .btn').attr('disabled', false);
                $(button).html('<?= _l("appointment_add_to_calendar"); ?> <i class="fa-brands fa-google"></i>');
            },
            error: function(e) {
                console.error('Error adding event to Google Calendar:', e);
                alert_float('danger', "<?= _l('appointment_google_calendar_error'); ?>");
                modalBody.removeClass('filterBlur');
                $(button).attr('disabled', false);
                $('.modal .btn').attr('disabled', false);
                $(button).html('<?= _l("appointment_add_to_calendar"); ?> <i class="fa-brands fa-google"></i>');
            }
        });
    }
    // Define isOutlookLoggedIn if not already defined
    if (typeof isOutlookLoggedIn !== 'function') {
        function isOutlookLoggedIn() {
            if (typeof myMSALObj !== "undefined" && typeof myMSALObj.getAccount === 'function' && myMSALObj.getAccount()) {
                return true;
            }
            return false;
        }
    }
</script>
