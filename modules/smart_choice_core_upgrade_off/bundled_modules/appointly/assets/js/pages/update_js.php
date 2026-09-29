<script>
    $(function() {
        // Global variables to track state
        var isSubmitting = false;
        var timeSlotRequestInProgress = false;
        // make sidebar active
        $('li.menu-item-appointly').addClass('active');
        $('li.menu-item-appointly > ul').addClass('in');
        $('li.sub-menu-item-appointly-user-dashboard').addClass('active');

        // Load company schedule for date picker validation
        window.appointlyCompanySchedule = <?php
                                            $CI = &get_instance();
                                            $CI->db->select('weekday, is_enabled');
                                            $CI->db->from(db_prefix() . 'appointly_company_schedule');
                                            $company_schedule = $CI->db->get()->result_array();

                                            $schedule_by_day = [];
                                            foreach ($company_schedule as $day) {
                                                $schedule_by_day[$day['weekday']] = [
                                                    'is_enabled' => (bool)$day['is_enabled']
                                                ];
                                            }
                                            echo json_encode($schedule_by_day);
                                            ?>;

        // Initialize UI components
        if (typeof init_selectpicker === 'function') {
            init_selectpicker();
        }

        // Initialize editors
        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.remove("#description");
            tinyMCE.remove("#notes");
        }

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

        // Cache DOM elements for better performance
        var $relType = $('#rel_type');
        var $serviceId = $('#service_id');
        var $providerId = $('#provider_id');
        var $appointmentDate = $('#appointment_date');
        var $availableTimes = $('#available_times');
        var $appointmentDuration = $('#appointment_duration');

        // Initialize appointment data based on current inputs
        initializeAppointmentData();

        // Initialize with proper options
        // Get blocked days from PHP
        var blockedDays = <?php
                            $blocked_days = get_appointly_blocked_days();
                            echo json_encode($blocked_days);
                            ?>;

        // Get the setting for showing past dates
        var showPastDates = <?php echo get_option('appointments_show_past_dates') == '1' ? 'true' : 'false'; ?>;

        // Get appointment type
        var relType = $('#rel_type').val();

        // Configure datepicker with helper function
        var appointmentDatePickerOptions = appointlyConfigureDatePicker(
            blockedDays,
            showPastDates,
            relType
        );

        // Apply datepicker to the appointment date field
        $('#appointment_date').datetimepicker(appointmentDatePickerOptions);

        // Load initial provider schedule if provider is already selected
        var initialProviderId = $('#provider_id').val();
        if (initialProviderId && relType !== 'internal_staff') {
            loadProviderScheduleAndUpdateCalendar(initialProviderId);
        }

        // Helper function to pad numbers with leading zeros
        function padZero(num) {
            return (num < 10 ? '0' : '') + num;
        }

        /**
         * Load provider schedule and update calendar
         */
        function loadProviderScheduleAndUpdateCalendar(providerId) {


            // Get current blocked days and settings
            var blockedDays = <?php
                                $blocked_days = get_appointly_blocked_days();
                                echo json_encode($blocked_days);
                                ?>;
            var showPastDates = <?php echo get_option('appointments_show_past_dates') == '1' ? 'true' : 'false'; ?>;
            var relType = $('#rel_type').val();

            // Load provider schedule
            $.ajax({
                url: admin_url + 'appointly/services/get_staff_schedule',
                type: 'POST',
                data: {
                    staff_id: providerId
                },
                dataType: 'json',
                success: function(response) {


                    if (response && response.success && response.data && response.data.schedule) {
                        // Store provider schedule globally
                        window.providerSchedule = response.data.schedule;

                        // Reinitialize the date picker with provider schedule
                        updateDatePickerWithProviderSchedule(blockedDays, showPastDates, relType);
                    } else {

                        // Initialize normal calendar
                        initializeDateTimePickers();
                    }
                },
                error: function(xhr, status, error) {

                    // Initialize normal calendar as fallback
                    initializeDateTimePickers();
                }
            });
        }

        /**
         * Update date picker with provider schedule
         */
        function updateDatePickerWithProviderSchedule(blockedDays, showPastDates, relType) {


            // Destroy existing datepicker
            if ($('#appointment_date').data('xdsoft_datetimepicker')) {
                $('#appointment_date').datetimepicker('destroy');
            }

            // Configure datepicker with provider schedule
            var appointmentDatePickerOptions = appointlyConfigureDatePicker(
                blockedDays,
                showPastDates,
                relType,
                window.providerSchedule // Pass provider schedule
            );

            // Apply datepicker to the appointment date field
            $('#appointment_date').datetimepicker(appointmentDatePickerOptions);
        }

        /**
         * Initialize date picker normally
         */
        function initializeDateTimePickers() {
            var blockedDays = <?php
                                $blocked_days = get_appointly_blocked_days();
                                echo json_encode($blocked_days);
                                ?>;
            var showPastDates = <?php echo get_option('appointments_show_past_dates') == '1' ? 'true' : 'false'; ?>;
            var relType = $('#rel_type').val();

            // Destroy existing datepicker
            if ($('#appointment_date').data('xdsoft_datetimepicker')) {
                $('#appointment_date').datetimepicker('destroy');
            }

            // Configure datepicker without provider schedule
            var appointmentDatePickerOptions = appointlyConfigureDatePicker(
                blockedDays,
                showPastDates,
                relType
            );

            // Apply datepicker to the appointment date field
            $('#appointment_date').datetimepicker(appointmentDatePickerOptions);
        }

        // Date picker icon click handler
        $('.calendar-icon, #appointment_date').off('click').on('click', function(e) {
            e.stopPropagation(); // Prevent event bubbling

            // If already using datetimepicker, just focus
            if ($('#appointment_date').data('xdsoft_datetimepicker')) {
                $('#appointment_date').datetimepicker('show');
                return;
            }

            // If using Perfex's standard datepicker, reconfigure with our options
            var showPastDates = <?php echo get_option('appointments_show_past_dates') == '1' ? 'true' : 'false'; ?>;
            var relType = $('#rel_type').val();

            // Initialize with Perfex's function if available
            if (typeof appDatepicker === 'function') {
                var dateOptions = {
                    element_date: $appointmentDate,
                    open_immediately: true,
                    disabledDates: blockedDays
                };

                // Add minDate if we're not showing past dates
                if (!showPastDates) {
                    dateOptions.minDate = new Date();
                }

                appDatepicker(dateOptions);
            } else if (typeof init_datepicker === 'function') {
                init_datepicker();
                $appointmentDate.datepicker('show');
            }
        });

        // Date change handler - loads available time slots
        $('#appointment_date').off('change').on('change', function() {
            var date = $(this).val();
            var relType = $('#rel_type').val();
            var isStaffOnly = (relType === 'internal_staff');
            var formattedDate = appointlyFormatDate(date);

            if (!date) {
                $('#slot_loading').addClass('hide');
                return;
            }

            if (isStaffOnly) {
                // For staff-only meetings, we don't need service/provider
                $('#slot_loading').removeClass('hide');
                var providerId = $('input[name="created_by"]').val() || <?= get_staff_user_id(); ?>;
                appointlyGetTimeSlots(0, providerId, formattedDate, $('input[name="start_hour"]').val(), $('#timezone').val(), $('input[name="appointment_id"]').val());
            } else {
                // For regular appointments, we need provider and service
                var providerId = $('#provider_id').val();
                var serviceId = $('#service_id').val();

                if (providerId && serviceId) {
                    $('#slot_loading').removeClass('hide');
                    appointlyGetTimeSlots(serviceId, providerId, formattedDate, $('input[name="start_hour"]').val(), $('#timezone').val(), $('input[name="appointment_id"]').val());
                } else {
                    resetTimeSelection();
                }
            }
        });

        // Provider change event
        $('#provider_id').on('change', function() {
            var providerId = $(this).val();

            if (providerId) {
                // Clear existing time slots
                resetTimeSelection();

                // Load provider schedule and update calendar
                loadProviderScheduleAndUpdateCalendar(providerId);

                // Load time slots if date and service are selected
                var currentDate = $('#appointment_date').val();
                var serviceId = $('#service_id').val();

                if (currentDate && serviceId) {
                    $('#slot_loading').removeClass('hide');
                    appointlyGetTimeSlots(serviceId, providerId, appointlyFormatDate(currentDate), $('input[name="start_hour"]').val(), $('#timezone').val(), $('input[name="appointment_id"]').val());
                }
            } else {
                resetDateAndTimeSelections();
            }
        });

        // Service change event
        $('#service_id').on('change', function() {
            var serviceId = $(this).val();

            if (!serviceId) {
                resetProviderSelection();
                return;
            }

            // Get duration from selected service
            var duration = $(this).find('option:selected').data('duration');
            if (duration) {
                $('#appointment_duration').val(duration);
            }

            // Load available providers for this service
            appointlyLoadProviders(serviceId);
        });

        // Notification checkboxes handling
        function toggleReminderFields() {
            var anyChecked = $('#by_sms').prop('checked') || $('#by_email').prop('checked');
            $('.appointment-reminder').toggleClass('hide', !anyChecked);
        }

        $('#by_sms, #by_email').on('change', toggleReminderFields);

        // Store previous values for different types to restore when switching back
        var storedValues = {
            internal: {
                contact_id: null
            },
            lead_related: {
                rel_id: null
            },
            external: {
                name: '',
                email: '',
                phone: ''
            },
            service_id: null,
            provider_id: null
        };

        // Appointment type (rel_type) handling
        $relType.off('change').on('change', function() {
            var oldType = window.currentAppointmentType;
            var newType = $(this).val();


            // Store current values before switching
            if (oldType === 'internal') {
                storedValues.internal.contact_id = $('#contact_id').val();
            } else if (oldType === 'lead_related') {
                storedValues.lead_related.rel_id = $('#rel_id').val();
            } else if (oldType === 'external') {
                storedValues.external.name = $('#name').val();
                storedValues.external.email = $('#email').val();
                storedValues.external.phone = $('#phone').val();
            }

            // Always store service and provider values
            if (oldType !== 'internal_staff') {
                storedValues.service_id = $('#service_id').val();
                storedValues.provider_id = $('#provider_id').val();
            }

            // Clear any previous errors or warnings
            $('.time-slot-unavailable').addClass('hide');
            $('.time-slot-unavailable-info').addClass('hide');

            // Reset time slots when changing types
            resetTimeSelection();

            // Use centralized function for all dynamic field logic
            handleAppointmentTypeChange(newType, false);

            // Restore previously stored values if switching back to a type
            if (newType !== 'internal_staff') {
                // Restore service and provider
                if (storedValues.service_id) {
                    $('#service_id').val(storedValues.service_id).prop('disabled', false).selectpicker('refresh');
                }
                if (storedValues.provider_id) {
                    $('#provider_id').val(storedValues.provider_id).prop('disabled', false).selectpicker('refresh');
                }

                // Restore type-specific values
                if (newType === 'internal' && storedValues.internal.contact_id) {
                    $('#contact_id').val(storedValues.internal.contact_id).selectpicker('refresh');
                    appointlyFetchContactData(storedValues.internal.contact_id, false);
                } else if (newType === 'lead_related' && storedValues.lead_related.rel_id) {
                    setTimeout(function() {
                        $('#rel_id').val(storedValues.lead_related.rel_id).selectpicker('refresh');
                        appointlyFetchContactData(storedValues.lead_related.rel_id, true);
                    }, 300);
                } else if (newType === 'external') {
                    $('#name').val(storedValues.external.name);
                    $('#email').val(storedValues.external.email);
                    $('#phone').val(storedValues.external.phone);
                    $('#div_name input, #div_email input, #div_phone input').prop('disabled', false);
                }
            }

            // Update the stored type
            window.currentAppointmentType = newType;
        });

        // Centralized function for all dynamic field logic
        function handleAppointmentTypeChange(relType, isInit) {
            appointlyHandleTypeChange(relType, isInit);
        }

        // On page load, set up initial state using the centralized function
        var initialType = $relType.val();
        window.currentAppointmentType = initialType;

        // Handle time slot selection change
        $('#available_times').on('change', function() {
            handleTimeSlotChange($(this));
        });

        // Contact and lead selection handlers
        $('#contact_id').on('change', function() {
            var contactId = $(this).val();
            if (!contactId) {
                appointlyResetContactFields();
                return;
            }

            appointlyFetchContactData(contactId, false);
        });

        // Lead selection handler
        $('#rel_id').on('change', function() {
            var leadId = $(this).val();
            if (!leadId) {
                appointlyResetContactFields();
                return;
            }

            appointlyFetchContactData(leadId, true);
        });

        // Initialize form validation 
        $('#appointment-update-form').appFormValidator({
            rules: {
                subject: 'required',
                service_id: {
                    required: function() {
                        return $('#rel_type').val() !== 'internal_staff';
                    }
                },
                provider_id: {
                    required: function() {
                        return $('#rel_type').val() !== 'internal_staff';
                    }
                },
                date: 'required',
                name: {
                    required: function() {
                        return $('#rel_type').val() === 'external';
                    }
                },
                email: {
                    required: function() {
                        return $('#rel_type').val() === 'external';
                    },
                    email: true
                },
                rel_id: {
                    required: function() {
                        return $('#rel_type').val() === 'lead_related';
                    }
                },
                contact_id: {
                    required: function() {
                        return $('#rel_type').val() === 'internal';
                    }
                }
            },
            submitHandler: function(form) {
                var $form = $(form);
                var $submitBtn = $form.find('button[type="submit"]');

                // Prevent double submission
                if ($submitBtn.prop('disabled')) {
                    return false;
                }

                // Disable ALL buttons in the form and panel footer
                var $panel = $form.closest('.panel-body');
                $form.find('button').prop('disabled', true);
                $panel.find('.btn').prop('disabled', true);

                // Check attendees for internal_staff appointments
                var relType = $('#rel_type').val();
                if (relType === 'internal_staff') {
                    var selectedAttendees = $('select[name="attendees[]"]').val();
                    if (!selectedAttendees || selectedAttendees.length === 0) {
                        alert_float('danger', "<?= _l('appointment_attendees_required'); ?>");

                        // Expand attendees section
                        var $attendeesSection = $('.appointment-attendees');
                        var $attendeesHeader = $attendeesSection.prev('.appointment-collapsible-header');
                        $attendeesSection.show();
                        $attendeesHeader.find('.appointment-toggle-plus').text('-');
                        $('select[name="attendees[]"]').focus();

                        // Re-enable all buttons
                        $form.find('button').prop('disabled', false);
                        $panel.find('.btn').prop('disabled', false);
                        return false;
                    }
                }

                // Set submitting flag
                window.isSubmitting = true;

                // Add loading spinner to submit button
                $submitBtn.html('<i class="fa fa-spinner fa-spin"></i>');

                // For internal_staff meetings, ensure there's a duration and transfer value from display field
                var relType = $('#rel_type').val();
                if (relType === 'internal_staff') {
                    // Remove irrelevant fields before submit
                    $('#contact_id').val('');
                    $('#rel_id').val('');
                    $('#rel_lead_type').val('');

                    // Set duration if not already set
                    if (!$('#appointment_duration').val()) {
                        $('#appointment_duration').val(60);
                    }
                } else if (relType === 'internal') {
                    $('#rel_id').val('');
                    // Keep rel_lead_type for debugging
                    $('#name, #email, #phone').val('');
                } else if (relType === 'lead_related') {
                    $('#contact_id').val('');
                    // Make sure rel_lead_type is set to 'leads'
                    $('#rel_lead_type').val('leads');

                    // Validate that rel_id is set
                    if (!$('#rel_id').val()) {
                        alert_float('danger', "<?= _l('appointment_lead_required'); ?>");
                        $submitBtn.prop('disabled', false).html("<?= _l('appointment_save_changes_btn_label'); ?>");
                        $panel.find('.btn').prop('disabled', false);
                        return false;
                    }

                    $('#name, #email, #phone').val('');
                } else if (relType === 'external') {
                    $('#contact_id').val('');
                    $('#rel_id').val('');
                    $('#rel_lead_type').val('');
                }

                // Sync TinyMCE content back to textareas before submission
                if (typeof tinyMCE !== 'undefined') {
                    tinyMCE.triggerSave();

                    // Explicitly sync each editor
                    var descEditor = tinyMCE.get('description');
                    if (descEditor) {
                        descEditor.save();
                    }

                    var notesEditor = tinyMCE.get('notes');
                    if (notesEditor) {
                        notesEditor.save();
                    }
                }

                // Ensure all form fields are enabled for submission (including hidden sections)
                $form.find('input, select, textarea').each(function() {
                    // Temporarily enable all fields for serialization
                    if ($(this).prop('disabled')) {
                        $(this).attr('data-was-disabled', 'true').prop('disabled', false);
                    }
                });

                // Debug: Log what's being sent
                var formData = $form.serialize();

                // Submit the form
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert_float('success', response.message);
                            window.location.reload();
                        } else {
                            alert_float('danger', response.message);
                            // Re-enable all buttons
                            $form.find('button').prop('disabled', false);
                            $panel.find('.btn').prop('disabled', false);
                            $submitBtn.html("<?= _l('appointment_save_changes_btn_label'); ?>");
                        }
                    }
                });
            }
        });

        /**
         * HELPER FUNCTIONS
         */

        // Function to initialize appointment data based on current values
        function initializeAppointmentData() {
            // Set current values
            var appointmentType = $relType.val();

            // Handle appointment type display
            handleAppointmentTypeChange(appointmentType, true);

            // Initialize toggles based on current state
            toggleReminderFields();
            // For internal_staff appointments, show duration field
            if (appointmentType === 'internal_staff') {
                $('#duration_wrapper').removeClass('hidden');
            } else {
                // Hide duration for all other appointment types
                $('#duration_wrapper').addClass('hidden');
            }

            // Initialize AJAX search for relationships
            init_ajax_search('contact', '#contact_id.ajax-search');
            init_ajax_search('lead', '#rel_id.ajax-search');

            // Check if we have existing contact/lead data to fetch
            if (appointmentType === 'internal') {
                var contactId = $('#contact_id').val();
                if (contactId) {
                    setTimeout(function() {
                        appointlyFetchContactData(contactId, false);
                    }, 500);
                }
            } else if (appointmentType === 'lead_related') {
                var leadId = $('#rel_id').val();
                if (leadId) {
                    setTimeout(function() {
                        appointlyFetchContactData(leadId, true);
                    }, 500);
                }
            } else if (appointmentType === 'external') {
                // For external type, just make sure the fields are visible and editable
                if ($('#name').val() || $('#email').val() || $('#phone').val()) {
                    $('#div_name, #div_email, #div_phone').removeClass('hidden');
                    $('#div_name input, #div_email input, #div_phone input').prop('disabled', false);
                }
            }

            // Load time slots if we have all necessary data
            var currentDate = $('#appointment_date').val();

            if (currentDate) {
                // For service-based appointments, we need provider and service
                var providerId = $('#provider_id').val();
                var serviceId = $('#service_id').val();

                if (providerId && serviceId) {
                    $('#slot_loading').removeClass('hide');
                    setTimeout(function() {
                        loadAvailableTimeSlots(currentDate, providerId, serviceId);
                    }, 300);
                }
            }
        }

        // Reset time selection function
        function resetTimeSelection() {
            // Use the shared function from helpers.js
            window.resetTimeSelection();
        }

        // Reset date and time selections
        function resetDateAndTimeSelections() {
            $('#appointment_date').val('');
            resetTimeSelection();
        }

        // Reset provider selection
        function resetProviderSelection() {
            var providersSelect = $('#provider_id');
            providersSelect.html('<option value=""><?= _l("appointment_select_provider"); ?></option>');
            providersSelect.prop('disabled', true);

            providersSelect.selectpicker('refresh');

            // Only reset time slots, preserve the selected date
            resetTimeSelection();
        }

        // Function to load available time slots
        function loadAvailableTimeSlots(date, providerId, serviceId) {
            // Check if this is an internal_staff appointment
            var isStaffOnly = $('#rel_type').val() === 'internal_staff';

            if (!date) {
                $('#slot_loading').addClass('hide');
                return;
            }

            // For staff-only appointments, provider and service are optional
            if (!isStaffOnly && (!providerId || !serviceId)) {
                $('#slot_loading').addClass('hide');
                return;
            }

            // Show loading indicator
            $('#slot_loading').removeClass('hide');

            // Prevent multiple concurrent requests
            if (window.timeSlotRequestInProgress) {
                return;
            }

            window.timeSlotRequestInProgress = true;

            // Clear any existing time slot error messages
            $('.time-slot-unavailable').addClass('hide');
            $('.time-slot-unavailable-info').addClass('hide');

            var appointmentId = $('input[name="appointment_id"]').val() || '0';
            var appointmentDate = $('#appointment_date').val();

            // Get the current appointment time from hidden field - important for preserving selection
            var currentStartHour = $('input[name="start_hour"]').val();
            var currentEndHour = $('input[name="end_hour"]').val();
            var currentDate = $('#appointment_date').data('current-date');

            // Flag to track if we're viewing the original appointment date
            var isViewingOriginalDate = (appointmentDate === currentDate);


            // Prepare data for AJAX request
            var requestData = {
                date: date,
                appointment_id: appointmentId,
                <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
            };

            // For staff meetings, we need special handling
            if (isStaffOnly) {
                // Use current user as provider if not specified
                requestData.provider_id = providerId || $('input[name="created_by"]').val() || <?= get_staff_user_id(); ?>;

                // Don't send service_id for staff-only meetings
                // Leave it undefined so the server will handle it
            } else {
                // For regular appointments, use the selected provider and service
                // Ensure we have a valid service ID (not 0 or empty)
                requestData.provider_id = providerId;

                // Make sure we have a valid service_id
                if (serviceId && serviceId !== '0') {
                    requestData.service_id = serviceId;
                } else {
                    // Try to get from select field
                    var selectedServiceId = $('#service_id').val();
                    if (selectedServiceId && selectedServiceId !== '0') {
                        requestData.service_id = selectedServiceId;
                    } else {
                        // If no valid service ID is available, show a warning and stop
                        $('#slot_loading').addClass('hide');
                        window.timeSlotRequestInProgress = false;
                        alert_float('warning', "<?= _l('appointment_service_required'); ?>");
                        return;
                    }
                }
            }

            $.ajax({
                url: admin_url + 'appointly/appointments/get_available_time_slots',
                type: 'POST',
                data: requestData,
                dataType: 'json',
                success: function(response) {
                    window.timeSlotRequestInProgress = false;

                    // Hide loading indicator
                    $('#slot_loading').addClass('hide');

                    var timesSelect = $('#available_times');

                    // Get the current selected time from the dropdown or the hidden field
                    var currentSelectedTime = timesSelect.val() || currentStartHour;

                    // Clear options
                    timesSelect.empty();
                    timesSelect.append('<option value=""><?= _l("dropdown_non_selected_tex"); ?></option>');

                    if (response.success && response.time_slots && response.time_slots.length > 0) {
                        // If we're editing an existing appointment on the original date, always add current time first
                        if (currentStartHour && isViewingOriginalDate) {
                            var formattedTime = appointlyFormatTimeDisplay(currentStartHour, currentEndHour);
                            var currentOption = new Option(formattedTime + ' (current)', currentStartHour, true, true);
                            $(currentOption).addClass('current-slot');
                            timesSelect.append(currentOption);
                        }

                        // Add all time slots (available and unavailable)
                        $.each(response.time_slots, function(index, slot) {
                            // Skip if this is the current appointment time (already added above)
                            if (currentStartHour && slot.value === currentStartHour && isViewingOriginalDate) {
                                return;
                            }

                            if (slot.available !== false) {
                                // Available slot
                                var option = new Option(slot.text, slot.value);
                                if (slot.end_time) {
                                    $(option).attr('data-end-time', slot.end_time);
                                }
                                timesSelect.append(option);
                            } else {
                                // Unavailable slot - show in red with strikethrough
                                var reason = slot.unavailable_reason ? ' - ' + slot.unavailable_reason : '';
                                var $option = $('<option>', {
                                    value: '', // Empty value so it can't be selected
                                    disabled: true,
                                    class: 'unavailable-time-slot',
                                    text: slot.text + reason,
                                    css: {
                                        'color': '#ff6666',
                                        'background-color': '#ffeeee',
                                        'text-decoration': 'line-through',
                                        'font-style': 'italic'
                                    }
                                });
                                timesSelect.append($option);
                            }
                        });

                        timesSelect.prop('disabled', false);

                        // Initialize selectpicker with proper styling
                        if (typeof $.fn.selectpicker === 'function') {
                            try {
                                timesSelect.selectpicker('destroy');
                                timesSelect.selectpicker({
                                    liveSearch: isStaffOnly, // Enable search for staff meetings (many options)
                                    showSubtext: false,
                                    hideDisabled: false // Important - show disabled options so users can see unavailable slots
                                });
                            } catch (e) {
                                // Silently ignore error
                            }
                        }
                    } else {
                        // No time slots available - show current time if editing existing appointment
                        if (currentStartHour && isViewingOriginalDate) {
                            var formattedTime = appointlyFormatTimeDisplay(currentStartHour, currentEndHour);
                            var option = new Option(formattedTime + ' (current)', currentStartHour, true, true);
                            $(option).addClass('current-slot');
                            timesSelect.append(option);
                            timesSelect.prop('disabled', false);
                        } else {
                            timesSelect.prop('disabled', true);
                            alert_float('warning', "<?= _l('appointment_no_slots_available'); ?>");
                        }

                        timesSelect.selectpicker('refresh');
                    }
                },
                error: function(xhr, status, error) {
                    window.timeSlotRequestInProgress = false;
                    $('#slot_loading').addClass('hide');

                    var timesSelect = $('#available_times');
                    timesSelect.empty();
                    timesSelect.append('<option value=""><?= _l("dropdown_non_selected_tex"); ?></option>');

                    // Show current time if editing existing appointment, otherwise disable
                    if (currentStartHour && isViewingOriginalDate) {
                        var formattedTime = appointlyFormatTimeDisplay(currentStartHour, currentEndHour);
                        var option = new Option(formattedTime + ' (current)', currentStartHour, true, true);
                        $(option).addClass('current-slot');
                        timesSelect.append(option);
                        timesSelect.prop('disabled', false);
                    } else {
                        timesSelect.prop('disabled', true);
                        alert_float('danger', "<?= _l('appointment_error_loading_slots'); ?>");
                    }

                    timesSelect.selectpicker('refresh');
                }
            });
        }
    });

    // Google and Outlook calendar integration functions
    function addEventToGoogleCalendar(button) {
        // First, validate the form for internal_staff appointments
        var relType = $('#rel_type').val();
        if (relType === 'internal_staff') {
            var attendeesSelect = $('select[name="attendees[]"]');
            var selectedAttendees = attendeesSelect.val();

            if (!selectedAttendees || selectedAttendees.length === 0) {
                // Show validation error and expand attendees section
                alert_float('danger', "<?= _l('appointment_attendees_required'); ?>");

                // Automatically expand attendees section
                var $attendeesSection = $('.appointment-attendees');
                var $attendeesHeader = $attendeesSection.prev('.appointment-collapsible-header');

                $attendeesSection.show();
                $attendeesHeader.find('.appointment-toggle-plus').text('-');

                // Focus on attendees select
                attendeesSelect.focus();

                return;
            }
        }

        var form = $('#appointment-update-form').serialize();
        var url = "<?= admin_url('appointly/appointments/addEventToGoogleCalendar'); ?>";
        var modalBody = $('#appointment-update-form .modal-body');

        $.ajax({
            url: url,
            type: "POST",
            data: form,
            beforeSend: function() {
                $(button).attr('disabled', true);
                $('.modal .btn').attr('disabled', true);
                modalBody.addClass('filterBlur');
                $(button).html('<i class="fa fa-refresh fa-spin fa-fw"></i> <?= _l("appointment_calendar_adding_to_google") ?>');
            },
            success: function(r) {
                alert_float('success', r.message);
                window.location.reload();
                $(button).remove();
            }
        });
    }

    function addEventToOutlookCalendar(button, appointmentId) {
        // First, validate the form for internal_staff appointments
        var relType = $('#rel_type').val();
        if (relType === 'internal_staff') {
            var attendeesSelect = $('select[name="attendees[]"]');
            var selectedAttendees = attendeesSelect.val();

            if (!selectedAttendees || selectedAttendees.length === 0) {
                // Show validation error and expand attendees section
                alert_float('danger', "<?= _l('appointment_attendees_required'); ?>");

                // Automatically expand attendees section
                var $attendeesSection = $('.appointment-attendees');
                var $attendeesHeader = $attendeesSection.prev('.appointment-collapsible-header');

                $attendeesSection.show();
                $attendeesHeader.find('.appointment-toggle-plus').text('-');

                // Focus on attendees select
                attendeesSelect.focus();

                return;
            }
        }

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

        addToOutlookNewEventFromUpdate(appointmentId);
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
