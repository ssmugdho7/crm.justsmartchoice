<script>
    $(function() {
        if (!isOutlookLoggedIn()) {
            $('body').find('#showOutlookCheckbox').remove();
        } else {
            acquireTokenPopupAndCallMSGraph();
        }

        var div_name = $('#div_name');
        var div_email = $('#div_email');
        var div_phone = $('#div_phone');

        // Flag to prevent duplicate submissions
        var isSubmitting = false;

        init_editor('textarea[name="notes"]', {
            menubar: false,
        });

        initAppointmentScheduledDates();

        $('.modal').on('hidden.bs.modal', function(e) {
            $('.xdsoft_datetimepicker').remove();
            $(this).removeData();

            // Reset submission flag when modal is closed
            isSubmitting = false;
        });

        // Prevent form submission only when validation fails
        $('#appointment-form').on('submit', function(e) {
            // Check if form is valid using jQuery Validation plugin
            if (!$(this).valid()) {
                e.preventDefault();
                return false;
            }

            // Prevent duplicate submissions
            if (isSubmitting) {
                console.log('Form already submitting, preventing duplicate');
                e.preventDefault();
                return false;
            }

            // For internal_staff, we need to handle form submission manually
            if ($('#rel_type').val() === 'internal_staff') {
                e.preventDefault();

                // Set flag to prevent duplicate submissions
                isSubmitting = true;

                // Disable buttons and show loading 
                $('button[type="submit"], button.close_btn').prop('disabled', true);
                $('button[type="submit"]').html('<i class="fa fa-refresh fa-spin fa-fw"></i>');
                $('.modal-title').html("<?= _l('appointment_please_wait'); ?>");

                // Get form data 
                var formData = new FormData(this);

                // Remove service_id and provider_id as they're not required for internal_staff
                if (formData.has('service_id')) formData.delete('service_id');
                if (formData.has('provider_id')) formData.delete('provider_id');

                // Default duration for internal_staff appointments if not specified
                var duration = 60; // Default to 60 minutes if not specified

                // Add duration to the form data if not already present or empty
                if (!formData.has('duration') || formData.get('duration') === '') {
                    formData.set('duration', duration);
                } else {
                    duration = parseInt(formData.get('duration'));
                }

                // Parse the date to get start_hour
                var dateStr = formData.get('date');
                console.log('Date string from form:', dateStr);

                var dateParts, timePart, startHour;

                // Handle different date formats (Y-m-d H:i or d-m-Y H:i)
                if (dateStr.includes('-')) {
                    // Split the date string
                    var dateTimeParts = dateStr.split(' ');
                    if (dateTimeParts.length >= 2) {
                        timePart = dateTimeParts[1]; // Get the time part
                        startHour = timePart;
                    } else {
                        // Default time if not specified
                        startHour = '09:00';
                    }
                } else {
                    // If date format is unexpected, use default
                    startHour = '09:00';
                }

                console.log('Extracted start hour:', startHour);

                // Calculate end hour based on duration
                var startMoment = moment(startHour, 'HH:mm');
                var endMoment = moment(startHour, 'HH:mm').add(duration, 'minutes');
                var endHour = endMoment.format('HH:mm');

                console.log('Calculated end hour:', endHour);

                // Add start_hour and end_hour to form data
                formData.set('start_hour', startHour);
                formData.set('end_hour', endHour);

                // Log the form data for debugging
                console.log('Form data before submission:');
                for (var pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }

                // Manual AJAX submission
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Internal staff submission response:', response);
                        if (response && response.result) {
                            var isOutlookChecked = document.getElementById('outlook-checkbox') || null;
                            if (isOutlookLoggedIn() && isOutlookChecked && isOutlookChecked.checked) {
                                outlookAddOrUpdateEvent(Array.from(formData.entries()));
                            }
                            alert_float('success', "<?= _l('appointment_created') ?>");
                            $('.modal').modal('hide');
                            $(document).find('.table-appointments').DataTable().ajax.reload();
                        } else {
                            console.error('Form submission failed:', response);
                            alert_float('danger', response.message || "<?= _l('appointment_could_not_be_created'); ?>");
                            $('button[type="submit"], button.close_btn').prop('disabled', false);
                            $('button[type="submit"]').html("<?= _l('submit') ?>");
                            $('.modal-title').html("<?= _l('appointment_new_appointment') ?>");
                        }
                        // Reset submission flag
                        isSubmitting = false;
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error for internal staff submission:', error);
                        if (xhr.responseText) {
                            try {
                                console.log('Error response:', xhr.responseText.substring(0, 1000));
                                var response = JSON.parse(xhr.responseText);
                                alert_float('danger', response.message || "<?= _l('appointment_could_not_be_created'); ?>");
                            } catch (e) {
                                alert_float('danger', "<?= _l('appointment_could_not_be_created'); ?> - " + error);
                            }
                        } else {
                            alert_float('danger', "<?= _l('appointment_could_not_be_created'); ?> - " + error);
                        }
                        $('button[type="submit"], button.close_btn').prop('disabled', false);
                        $('button[type="submit"]').html("<?= _l('submit'); ?>");
                        $('.modal-title').html("<?= _l('appointment_new_appointment'); ?>");
                        // Reset submission flag
                        isSubmitting = false;
                    }
                });

                return false;
            }

            // For other types, let the normal validation flow handle it
            // If we get here, validation has passed, let the submitHandler from appFormValidator handle it
        });

        // Complete fix for Staff Only validation issues
        function toggleStaffOnlyFields() {
            var relType = $('#rel_type').val();

            if (relType === 'internal_staff') {
                // 1. Hide service fields
                $('.staffonly-hide').hide();

                // Make sure duration field is visible and has default value
                var durationField = $('#appointment_duration');
                if (!durationField.val() || durationField.val() === '') {
                    durationField.val(60);
                }
                $('#duration_wrapper').removeClass('hidden');

                // 2. Remove required attributes
                $('#service_id, #provider_id').removeAttr('required');

                // 3. Remove validation rules if the validator exists
                if ($.validator && $('#appointment-form').data('validator')) {
                    try {
                        $('#service_id').rules('remove', 'required');
                        $('#provider_id').rules('remove', 'required');

                        // Force validation to run again with new rules
                        $('#appointment-form').validate().checkForm();
                    } catch (e) {
                        console.log('Validation rule removal error:', e);
                    }
                }

                // 4. Remove error messages
                $('#service_id-error, #provider_id-error').remove();

                // 5. Make sure any previously failed validation marks are cleared
                $('#service_id, #provider_id')
                    .closest('.form-group')
                    .removeClass('has-error');
            } else {
                // Show fields for other types
                $('.staffonly-hide').show();

                // Add back required attribute for non-staff-only
                $('#service_id, #provider_id').attr('required', 'required');

                // Add back validation rules
                if ($.validator && $('#appointment-form').data('validator')) {
                    try {
                        $('#service_id, #provider_id').each(function() {
                            $(this).rules('add', {
                                required: true
                            });
                        });
                    } catch (e) {
                        console.log('Validation rule addition error:', e);
                    }
                }
            }
        }

        // Run on page load
        toggleStaffOnlyFields();

        // Run when rel_type changes
        $('#rel_type').on('change', function() {
            console.log('Appointment type changed to:', $(this).val());

            var optionSelected = $("option:selected", this).attr('id');
            var contact_id = $("#contact_id");
            var select_contacts = $('#select_contacts');
            var div_nep = $('#div_name, #div_email, #div_phone');
            var meeting_select_inputs = $('#div_name input, #div_email input, #div_phone input');
            var rel_id_wrapper = $('body').find('#appointment-form #rel_id_wrapper');
            var lead_select = $('body').find('#appointment-form #rel_id');

            if (optionSelected == 'external') {
                meeting_select_inputs.val('').attr('disabled', false).attr('required', true);
                $('#div_phone input').attr('required', false);
                div_nep.removeClass('hidden');
                select_contacts.addClass('hidden');
                contact_id.selectpicker("refresh").attr('required', false);
                rel_id_wrapper.addClass('hide');
                lead_select.attr('required', false).val('default').selectpicker("refresh");
            } else if (optionSelected == 'internal') {
                contact_id.val('default').selectpicker("refresh").attr('required', true);
                div_nep.addClass('hidden').attr('required', false);
                select_contacts.removeClass('hidden');
                rel_id_wrapper.addClass('hide');
                lead_select.attr('required', false).val('default').selectpicker("refresh");
            } else if (optionSelected == 'internal_staff') {
                // Internal staff specific UI updates
                meeting_select_inputs.attr('required', false);
                contact_id.val('default').selectpicker("refresh").attr('required', false);
                div_nep.addClass('hidden').attr('required', false);
                select_contacts.addClass('hidden');
                rel_id_wrapper.addClass('hide');
                lead_select.attr('required', false);

                // Set default duration for internal_staff appointments if not already set
                var durationField = $('#appointment_duration');
                if (!durationField.val() || durationField.val() === '') {
                    durationField.val(60);
                }

                // Make sure the duration field is visible for internal_staff
                $('#duration_wrapper').removeClass('hidden');
            } else {
                // Likely lead_related
                meeting_select_inputs.attr('required', false);
                contact_id.val('default').selectpicker("refresh").attr('required', false);
                div_nep.addClass('hidden').attr('required', false);
                select_contacts.addClass('hidden');
                rel_id_wrapper.removeClass('hide');
                lead_select.attr('required', true);
            }

            // First apply UI changes, then update validation
            toggleStaffOnlyFields();

            // Re-initialize validation with new rules when appointment type changes
            if (validator) {
                console.log('Reinitializing validator for new appointment type');
                validator.resetForm();
            }
        });

        $('body').on('click', '#outlook-checkbox', function() {
            $(this).attr('checked', $(this).is(':checked') ? true : false);
        });

        // Updated validation rules
        function getValidationRules() {
            var relType = $('#rel_type').val();
            console.log('Getting validation rules for type:', relType);

            var rules = {
                subject: "required",
                date: "required",
                rel_type: "required",
                'attendees[]': {
                    required: true,
                    minlength: 1
                }
            };

            // Only require service_id and provider_id if NOT staff only
            if (relType !== 'internal_staff') {
                rules.service_id = "required";
                rules.provider_id = "required";
            }

            console.log('Validation rules:', rules);
            return rules;
        }

        var validator = appValidateForm($("#appointment-form"), getValidationRules(), function(form) {
            // This is only called when form is valid

            // Prevent duplicate submissions
            if (isSubmitting) {
                console.log('Form already submitting via validator, preventing duplicate');
                return false;
            }

            // Set flag to prevent duplicate submissions
            isSubmitting = true;

            $('button[type="submit"], button.close_btn').prop('disabled', true);
            $('button[type="submit"]').html('<i class="fa fa-refresh fa-spin fa-fw"></i>');
            $('.modal-title').html("<?= _l('appointment_please_wait'); ?>");

            var formSerializedData = $(form).serializeArray();
            var isOutlookChecked = document.getElementById('outlook-checkbox') || null;
            var relType = $('#rel_type').val();
            var duration = $('#appointment_duration').val();

            // Handle the case when duration is empty for internal_staff
            if (relType === 'internal_staff' && (!duration || duration === '')) {
                duration = 60; // Default to 60 minutes if not specified for internal_staff

                // Add duration to form data
                formSerializedData.push({
                    name: 'duration',
                    value: duration
                });
            }

            // Parse date to extract start hour
            var dateInput = $('input[name="date"]').val();
            var startHour = '09:00'; // Default

            // Extract time from date input
            if (dateInput && dateInput.includes(' ')) {
                var dateTimeParts = dateInput.split(' ');
                if (dateTimeParts.length >= 2) {
                    startHour = dateTimeParts[1]; // Get the time part
                }
            }

            console.log('Date input:', dateInput);
            console.log('Extracted start hour:', startHour);

            // Make sure we have start_hour in the data
            var hasStartHour = false;
            for (var i = 0; i < formSerializedData.length; i++) {
                if (formSerializedData[i].name === 'start_hour') {
                    hasStartHour = true;
                    formSerializedData[i].value = startHour;
                    break;
                }
            }

            if (!hasStartHour) {
                formSerializedData.push({
                    name: 'start_hour',
                    value: startHour
                });
            }

            var startDateTime = moment(startHour, 'HH:mm');
            var endDateTime = moment(startHour, 'HH:mm').add(duration || 60, 'minutes');
            var endHour = endDateTime.format('HH:mm');

            console.log('Calculated end hour:', endHour);

            // Add end_hour to form data
            formSerializedData.push({
                name: 'end_hour',
                value: endHour
            });

            // If staff only, remove service_id and provider_id from form data
            if (relType === 'internal_staff') {
                formSerializedData = formSerializedData.filter(function(item) {
                    return item.name !== 'service_id' && item.name !== 'provider_id';
                });
            }

            $.ajax({
                url: form.action,
                type: 'POST',
                data: formSerializedData,
                success: function(response) {
                    console.log('Form submission response:', response);
                    if (response.result) {
                        if (isOutlookLoggedIn() && isOutlookChecked && isOutlookChecked.checked) {
                            outlookAddOrUpdateEvent(formSerializedData);
                        }
                        alert_float('success', "<?= _l('appointment_created'); ?>");
                        $('.modal').modal('hide');
                        $(document).find('.table-appointments').DataTable().ajax.reload();
                    } else {
                        console.error('Form submission failed:', response);
                        alert_float('danger', response.message || "<?= _l('appointment_could_not_be_created'); ?>");
                        $('button[type="submit"], button.close_btn').prop('disabled', false);
                        $('button[type="submit"]').html("<?= _l('submit'); ?>");
                        $('.modal-title').html("<?= _l('appointment_new_appointment'); ?>");
                    }
                    // Reset submission flag
                    isSubmitting = false;
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    console.log('Form data:', formSerializedData);
                    if (xhr.responseText) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            alert_float('danger', response.message || "<?= _l('appointment_could_not_be_created'); ?>");
                        } catch (e) {
                            alert_float('danger', "<?= _l('appointment_could_not_be_created'); ?> - " + error);
                        }
                    } else {
                        alert_float('danger', "<?= _l('appointment_could_not_be_created'); ?> - " + error);
                    }
                    $('button[type="submit"], button.close_btn').prop('disabled', false);
                    $('button[type="submit"]').html("<?= _l('submit'); ?>");
                    $('.modal-title').html("<?= _l('appointment_new_appointment'); ?>");
                    // Reset submission flag
                    isSubmitting = false;
                },
            });

            return false;
        });

        $('body').on('change', '#contact_id, #rel_id', function() {
            var contact_id = $("option:selected", this).val();

            if (contact_id == "" && div_name.children('input').is(":visible")) {
                div_name.children('input').val('');
                div_email.children('input').val('');
                div_phone.children('input').val('');
            }

            var url = "<?= admin_url('appointly/appointments/fetch_contact_data'); ?>";

            $.post(url, {
                contact_id: contact_id,
                lead: ($(this).attr('id') == 'rel_id') ? true : false
            }).done(function(response) {
                if (response !== null) {
                    $('#div_name, #div_email, #div_phone').removeClass('hidden');

                    var full_name = (typeof(response.firstname) != 'undefined') ? response.firstname + ' ' + response.lastname : response.name;
                    var email = response.email;
                    var phone = response.phonenumber;

                    div_name.children('input').val(full_name).attr('disabled', true);
                    div_email.children('input').val(email).attr('disabled', (response.email == '') ? false : true);
                    div_email.children('input[name="email"]').val(email).attr('required', (response.email == '') ? true : false);
                    div_phone.children('input').val(phone).attr('disabled', true);
                }
            });
        });

        init_selectpicker();

        // Handle service selection
        $('#service_id').on('change', function() {
            var serviceId = $(this).val();
            var providerSelect = $('#provider_id');

            // Clear the provider select and add the default option.
            providerSelect.empty().append('<option value=""><?= _l("appointment_select_provider"); ?></option>');

            if (serviceId) {
                $.post(admin_url + 'appointly/services/get_service_details', {
                        service_id: serviceId
                    })
                    .done(function(response) {
                        // Ensure response is an object.
                        var data = (typeof response === 'string') ? JSON.parse(response) : response;
                        if (data.success) {
                            // Populate provider select.
                            data.data.providers.forEach(function(provider) {
                                providerSelect.append(
                                    '<option value="' + provider.staffid + '">' + provider.firstname + ' ' + provider.lastname + '</option>'
                                );
                            });
                            // Enable and refresh the selectpicker.
                            providerSelect.prop('disabled', false).selectpicker('refresh');

                            // Store duration in hidden input
                            if (data.data.duration) {
                                $('#appointment_duration').val(data.data.duration);
                            }
                        } else {
                            alert_float('warning', data.message || app.lang.error_occurred);
                        }
                    })
                    .fail(function() {
                        alert_float('danger', app.lang.error_occurred);
                    });
            } else {
                // If no service is selected, disable the provider select.
                providerSelect.prop('disabled', true).selectpicker('refresh');
            }
        });


        // Enable provider select when page loads if service is selected
        if ($('#service_id').val()) {
            $('#provider_id').prop('disabled', false).selectpicker('refresh');
        }

        // Leads functionality
        var _rel_id = $('#rel_id'),
            _rel_type = $('#rel_lead_type');

        // Items ajax search
        var serverData = {};
        init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
        serverData.rel_id = _rel_id.val();
        init_ajax_search(_rel_type.val(), _rel_id, serverData);

        // Patch: Allow all days/times for staff-only appointments
        function enableAllDaysForStaffOnly() {
            var relType = $('#rel_type').val();
            if (relType === 'internal_staff') {
                // Remove disabled days from datepicker
                $('.xdsoft_datepicker .xdsoft_disabled').removeClass('xdsoft_disabled');
            }
        }

        // Show info message for staff-only
        function showStaffOnlyInfo() {
            var relType = $('#rel_type').val();
            var infoId = 'staff-only-info';
            $('#' + infoId).remove();
            if (relType === 'internal_staff') {
                var info = $('<div id="' + infoId + '" class="alert alert-info" style="margin-top:10px;">This appointment will only include staff. All days and times are available.</div>');
                $('#rel_type').closest('.form-group').after(info);
            }
        }

        $('#rel_type').on('change', function() {
            enableAllDaysForStaffOnly();
            showStaffOnlyInfo();
        });
        // Initial call
        enableAllDaysForStaffOnly();
        showStaffOnlyInfo();

        // If you use a custom datepicker, hook into its onShow/onChange events as well
        $(document).on('mousedown', '.xdsoft_calendar, .xdsoft_timepicker', function() {
            enableAllDaysForStaffOnly();
        });

        // Patch: Ensure default time is set when a date is picked
        $(document).on('change', '.appointment-date', function() {
            var val = $(this).val();
            // If only date is present (no time), append default time
            if (val && val.match(/^\d{4}-\d{2}-\d{2}$/)) {
                $(this).val(val + ' 09:00');
            }
        });
    });
</script>
