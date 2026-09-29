<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    // DEBUG CONFIGURATION - Set to false to disable all console logging
    var APPOINTLY_DEBUG = false;

    if (location.hostname.includes('pfx.com.ai')) {
        APPOINTLY_DEBUG = true;
    }
    // Store original console methods
    var originalConsole = {
        log: console.log,
        error: console.error,
        warn: console.warn,
        info: console.info
    };

    // Override console methods based on debug flag
    if (!APPOINTLY_DEBUG) {
        console.log = function() {};
        console.error = function() {};
        console.warn = function() {};
        console.info = function() {};
    } else {
        // Enhanced console with timestamps when debug is enabled
        console.log = function() {
            var args = Array.prototype.slice.call(arguments);
            args.unshift('[APPOINTLY ' + new Date().toLocaleTimeString() + ']');
            originalConsole.log.apply(console, args);
        };

        console.error = function() {
            var args = Array.prototype.slice.call(arguments);
            args.unshift('[APPOINTLY ERROR ' + new Date().toLocaleTimeString() + ']');
            originalConsole.error.apply(console, args);
        };

        console.warn = function() {
            var args = Array.prototype.slice.call(arguments);
            args.unshift('[APPOINTLY WARN ' + new Date().toLocaleTimeString() + ']');
            originalConsole.warn.apply(console, args);
        };

        console.info = function() {
            var args = Array.prototype.slice.call(arguments);
            args.unshift('[APPOINTLY INFO ' + new Date().toLocaleTimeString() + ']');
            originalConsole.info.apply(console, args);
        };
    }

    // additional frontend validation for the external appointments form start_hour and end_hour
    $(function() {
        // Initialize variables
        var currentStep = 1;
        var selectedService = null;
        var selectedProvider = null;
        var selectedDate = null;
        var selectedTime = null;
        var selectedEndTime = null;
        var isSubmitting = false;
        var staffSchedules = {}; // Global variable to store staff schedules

        // Cache for loaded data to prevent unnecessary reloading
        var providersCache = {};
        var timeSlotsCache = {};
        var isLoadingProviders = false;
        var isLoadingTimeSlots = false;

        // Initialize appFormValidator for the appointment form
        $('#appointment-form').appFormValidator({
            rules: {
                subject: 'required',
                firstname: 'required',
                lastname: 'required',
                email: {
                    required: true,
                    email: true
                }
            },
            errorPlacement: function(error, element) {
                // Handle custom field errors
                if (element.attr('name') && element.attr('name').indexOf('custom_fields') !== -1) {
                    error.insertAfter(element.closest('.form-group'));
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                var $bookBtn = $('#book-appointment-btn');
                var originalText = $bookBtn.text();

                // Prevent multiple submissions
                if (isSubmitting) {
                    return false;
                }
                isSubmitting = true;

                // Set all required hidden fields with JavaScript variables
                $('#service_id').val(selectedService);
                $('#staff_id').val(selectedProvider);
                $('#hidden_staff_id').val(selectedProvider);
                $('#appointment_date_field').val(selectedDate);
                $('#start_hour').val(selectedTime);
                $('#end_hour').val(selectedEndTime);

                // Show loading state on button
                $bookBtn.prop('disabled', true);
                $bookBtn.html('<i class="fa fa-spinner fa-spin"></i> ' + (appointlyLang.booking || 'Booking Appointment...'));

                // Collect all form data
                var formData = new FormData(form);
                var timezone = $('#timezone').val();
                if (timezone) formData.append('timezone', timezone);
                formData.append('rel_type', 'external');

                $.ajax({
                    url: site_url + 'appointly/appointments_public/create_external_appointment',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $bookBtn.html('<i class="fa fa-check"></i> ' + (appointlyLang.success || 'Success!'));
                            setTimeout(function() {
                                window.location.href = response.redirect_url || (site_url + 'appointly/appointments_public/success_message?token=' + response.token);
                            }, 1000);
                        } else {
                            alert_float('danger', response.message || 'An error occurred while booking the appointment.');
                            $bookBtn.prop('disabled', false);
                            $bookBtn.html(originalText);
                            isSubmitting = false;
                        }
                    },
                    error: function(xhr, status, error) {
                        alert_float('danger', 'An error occurred while booking the appointment. Please try again.');
                        $bookBtn.prop('disabled', false);
                        $bookBtn.html(originalText);
                        isSubmitting = false;
                    }
                });
                return false;
            }
        });

        // Global handler to prevent form submission on input changes
        $('#appointment-form input, #appointment-form select').on('change', function(e) {
            // Only prevent default if this is not an explicit form submission
            if (!$(e.target).is('[type="submit"]')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        // Initialize page
        try {
            initializePage();
            console.log('Page initialized successfully');
        } catch (e) {
            console.error('Error initializing page:', e);
            alert('Error initializing the booking form: ' + e.message);
        }

        /**
         * Initialize all page components
         */
        function initializePage() {
            // Check required libraries
            if (typeof jQuery.fn.datetimepicker === 'undefined') {
                console.warn('datetimepicker plugin is not loaded. Date selection may not work properly.');
            }

            // Setup event handlers
            setupEventHandlers();

            // Update step display
            updateStepDisplay(currentStep);

            // Initialize timezone dropdown
            initTimezoneSelect();
        }

        /**
         * Setup all event handlers
         */
        function setupEventHandlers() {
            // Service selection
            $(document).on('click', '.service-card', function() {
                var serviceId = $(this).data('service-id');
                if (serviceId) {
                    selectService(serviceId);
                }
            });

            // Provider selection
            $(document).on('click', '.provider-card', function() {
                var providerId = $(this).data('provider-id');
                if (providerId) {
                    selectProvider(providerId);
                }
            });

            // Provider details modal
            $(document).on('click', '.view-provider-details', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var providerId = $(this).data('provider-id');
                var $providerCard = $(this).closest('.provider-card');

                // Extract provider data from the card
                var provider = {
                    staffid: providerId,
                    firstname: $providerCard.data('firstname'),
                    lastname: $providerCard.data('lastname'),
                    email: $providerCard.data('email') || '',
                    phonenumber: $providerCard.data('phone') || '',
                    profile_image: $providerCard.data('image')
                };

                // Get working hours data
                var workingHours = $providerCard.data('working-hours') || $providerCard.data('formatted-hours') || [];

                // Display the modal with provider details
                displayProviderDetails(provider, workingHours);
            });

            // Provider selection from modal
            $(document).on('click', '.select-provider', function() {
                var providerId = $(this).data('provider-id');
                if (providerId) {
                    selectProvider(providerId);
                    $('#providerDetailsModal').modal('hide');
                }
            });

            // Date selection
            $(document).on('change', '#appointment-date', function(e) {
                // Prevent default form submission behavior
                e.preventDefault();
                e.stopPropagation();

                var date = $(this).val();
                if (date) {
                    selectedDate = date;
                    $('#appointment_date_field').val(date);
                    loadTimeSlots(selectedService, selectedProvider, date);

                    // Show the time slots section
                    $('#time-slots-section').removeClass('tw-hidden');
                } else {
                    resetTimeSelection();

                    // Hide the time slots section
                    $('#time-slots-section').addClass('tw-hidden');
                }

                // Return false to prevent form submission
                return false;
            });

            // Handle date picker's xdsoft_datetimepicker change event
            $(document).on('xdsoft_change', '#appointment-date', function(e) {
                // Prevent default form submission behavior
                e.preventDefault();
                e.stopPropagation();

                var date = $(this).val();
                if (date) {
                    selectedDate = date;
                    $('#appointment_date_field').val(date);
                    loadTimeSlots(selectedService, selectedProvider, date);

                    // Show the time slots section
                    $('#time-slots-section').removeClass('tw-hidden');
                } else {
                    resetTimeSelection();

                    // Hide the time slots section
                    $('#time-slots-section').addClass('tw-hidden');
                }

                // Return false to prevent form submission
                return false;
            });

            // Time slot selection
            $('#available-times').on('change', function() {
                var time = $(this).val();
                if (time) {
                    selectedTime = time;

                    // Get end time from option data attribute
                    var $selectedOption = $(this).find('option:selected');
                    selectedEndTime = $selectedOption.data('end-time');

                    // Store values in hidden inputs
                    $('input[name="start_hour"]').val(selectedTime);
                    $('input[name="end_hour"]').val(selectedEndTime);

                    // Update summary
                    updateAppointmentSummary();
                }
            });

            // Next step button
            $('.btn-next').on('click', function() {
                if (validateCurrentStep()) {
                    currentStep++;
                    updateStepDisplay(currentStep);
                }
            });

            // Previous step button
            $('.btn-prev-step').on('click', function(e) {
                e.preventDefault();
                currentStep--;
                updateStepDisplay(currentStep);
            });

            // Form submission - let appFormValidator handle everything
            $('#appointment-form').on('submit', function(e) {
                // Let appFormValidator handle validation and submission
                // The submitHandler in appFormValidator will handle everything
                return true;
            });

            // Fix for terms checkbox visual state
            $(document).on('change', '#terms_accepted', function() {
                if ($(this).is(':checked')) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            });
        }

        /**
         * Initialize timezone select
         */
        function initTimezoneSelect() {
            try {
                // Get user's timezone if available
                var userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (userTimezone) {
                    $('#timezone').val(userTimezone);
                    $('#selected-timezone').text(userTimezone);
                } else {
                    $('#selected-timezone').text($('#timezone').val());
                }
            } catch (e) {
                console.error('Error getting user timezone:', e);
                $('#selected-timezone').text($('#timezone').val());
            }
        }

        /**
         * Update step display based on current step
         */
        function updateStepDisplay(step) {
            console.log('Updating step display to:', step);

            // Add smooth transition effects
            $('.booking-step.active').addClass('tw-opacity-0');

            setTimeout(function() {
                // Hide all steps
                $('.booking-step').addClass('hidden').removeClass('active');

                // Show current step
                $('#step-' + step).removeClass('hidden').addClass('active');

                // Trigger fade in effect
                setTimeout(function() {
                    $('.booking-step.active').removeClass('tw-opacity-0');
                }, 10);
            }, 150);

            // Update progress indicators
            $('.step-indicator').removeClass('active completed');

            // Reset all connecting lines
            $('.step-line').removeClass('active completed');

            // Update step indicators - both mark as active and complete previous steps
            for (var i = 1; i <= 4; i++) {
                var $indicator = $('#step-indicator-' + i);

                if (i < step) {
                    // Completed steps - green
                    $indicator.addClass('completed');
                    $indicator.find('.step-number').removeClass('tw-bg-neutral-300 tw-bg-primary-500').addClass('tw-bg-green-500');

                    // Mark connecting line as completed
                    if (i < 4) {
                        $('#line-' + i + '-' + (i + 1)).addClass('completed');
                    }
                } else if (i === step) {
                    // Current step - blue
                    $indicator.addClass('active');
                    $indicator.find('.step-number').removeClass('tw-bg-neutral-300 tw-bg-green-500').addClass('tw-bg-primary-500');

                    // If this is not the first step, mark the previous connecting line as completed
                    if (i > 1) {
                        $('#line-' + (i - 1) + '-' + i).addClass('completed');
                    }

                    // If this is not the last step, mark the next connecting line as active
                    if (i < 4) {
                        $('#line-' + i + '-' + (i + 1)).addClass('active');
                    }
                } else {
                    // Future steps - gray
                    $indicator.find('.step-number').removeClass('tw-bg-primary-500 tw-bg-green-500').addClass('tw-bg-neutral-300');
                }
            }

            // Update progress bar width based on current step
            var progressPercentage = step * 25;
            $('.progress-bar').css('width', progressPercentage + '%');

            // Set progress bar color based on step
            if (step > 1) {
                $('.progress-bar').removeClass('tw-bg-primary-500').addClass('tw-bg-green-500');
            } else {
                $('.progress-bar').removeClass('tw-bg-green-500').addClass('tw-bg-primary-500');
            }

            // Show/hide navigation buttons
            if (step === 1) {
                $('.btn-prev-step').addClass('hidden');
            } else {
                $('.btn-prev-step').removeClass('hidden');
            }

            // Update summary when reaching step 3 or 4
            if (step === 3 || step === 4) {
                updateAppointmentSummary();
            }

            // Log current state for debugging
            console.log('Current step:', step);
            console.log('Service ID:', selectedService);
            console.log('Provider ID:', selectedProvider);
            console.log('Date:', selectedDate);
            console.log('Time:', selectedTime);
        }

        /**
         * Validate current step
         */
        function validateCurrentStep() {
            console.log('Validating step:', currentStep);

            switch (currentStep) {
                case 1:
                    if (!selectedService) {
                        alert_float('warning', appointlyLang.service_required);
                        return false;
                    }
                    return true;

                case 2:
                    if (!selectedProvider) {
                        alert_float('warning', appointlyLang.provider_required);
                        return false;
                    }
                    return true;

                case 3:
                    if (!selectedDate) {
                        alert_float('warning', appointlyLang.date_required);
                        return false;
                    }

                    if (!selectedTime) {
                        alert_float('warning', appointlyLang.time_required);
                        return false;
                    }

                    return true;

                case 4:
                    // Step 4: Contact information - appFormValidator handles all validation
                    return true;

                default:
                    return true;
            }
        }

        /**
         * Select a service
         */
        window.selectService = function(serviceId) {
            console.log('Selecting service:', serviceId);

            // Update UI
            $('.service-card').removeClass('selected');
            $('.service-card[data-service-id="' + serviceId + '"]').addClass('selected');

            // Store the selection
            selectedService = serviceId;
            $('#service_id').val(serviceId);

            // Reset subsequent selections
            selectedProvider = null;
            selectedDate = null;
            selectedTime = null;
            selectedEndTime = null;

            $('#staff_id').val('');
            $('#hidden_staff_id').val('');
            $('#appointment-date').val('');
            resetTimeSelection();

            // Clear time slots cache when service changes
            timeSlotsCache = {};

            // Load providers for this service
            loadServiceProviders(serviceId);

            // If on step 1, move to step 2
            if (currentStep === 1) {
                currentStep = 2;
                updateStepDisplay(currentStep);
            }
        }

        /**
         * Load providers for a service
         */
        function loadServiceProviders(serviceId) {
            console.log('Loading providers for service ID:', serviceId);

            // Check if we already have cached data for this service
            if (providersCache[serviceId]) {
                console.log('Using cached providers for service:', serviceId);
                renderProviders(providersCache[serviceId].providers, providersCache[serviceId].responseData);
                return;
            }

            // Prevent multiple simultaneous requests
            if (isLoadingProviders) {
                console.log('Already loading providers, skipping request');
                return;
            }

            isLoadingProviders = true;

            // Show loading state with centered spinner - only if container is empty
            if ($('#providers-container').html().trim() === '') {
                $('#providers-container').html(
                    '<div style="position: relative; width: 100%; height: 200px; display: flex; align-items: center; justify-content: center; grid-column: 1 / -1;">' +
                    '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">' +
                    '<div style="width: 48px; height: 48px; border: 4px solid #e5e7eb; border-top: 4px solid #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 16px;"></div>' +
                    '<p style="color: #6b7280; text-align: center; margin: 0; font-size: 14px;">' + appointlyLang.loading + '</p>' +
                    '</div>' +
                    '</div>' +
                    '<style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>'
                );
            }

            // Prepare the data
            var ajaxData = {
                service_id: serviceId
            };

            // Add CSRF token if available
            if (typeof csrfData !== 'undefined') {
                ajaxData[csrfData.token_name] = csrfData.hash;
            } else if (typeof csrfTokenName !== 'undefined' && typeof csrfTokenValue !== 'undefined') {
                ajaxData[csrfTokenName] = csrfTokenValue;
            }

            // Send AJAX request
            $.ajax({
                url: site_url + 'appointly/appointments_public/get_service_staff_public',
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        var staffMembers = [];
                        var workingHoursData = {};
                        var formattedHoursData = [];

                        // Get the staff members from the response
                        if (response.data && Array.isArray(response.data)) {
                            staffMembers = response.data;
                        } else if (response.data && response.data.staff && Array.isArray(response.data.staff)) {
                            staffMembers = response.data.staff;

                            // Store working hours and formatted hours from response
                            if (response.data.working_hours) {
                                workingHoursData = response.data.working_hours;
                            }
                            if (response.data.formatted_hours) {
                                formattedHoursData = response.data.formatted_hours;
                            }

                            // Get staff schedules
                            if (response.data.staff_schedules) {
                                staffSchedules = response.data.staff_schedules;

                                // Log each staff schedule to verify the data
                                for (var staffId in staffSchedules) {
                                    if (staffSchedules.hasOwnProperty(staffId)) {

                                        if (staffSchedules[staffId].formatted_hours) {
                                            console.log('- Formatted hours array length:',
                                                Array.isArray(staffSchedules[staffId].formatted_hours) ?
                                                staffSchedules[staffId].formatted_hours.length : 'not an array');

                                            if (Array.isArray(staffSchedules[staffId].formatted_hours)) {
                                                staffSchedules[staffId].formatted_hours.forEach(function(day, index) {
                                                    console.log('  Day ' + index + ':', day);
                                                });
                                            }
                                        }
                                    }
                                }
                            }
                        } else if (response.staff && Array.isArray(response.staff)) {
                            staffMembers = response.staff;

                            // Check for working_hours and staff_schedules at the top level
                            if (response.working_hours) {
                                workingHoursData = response.working_hours;
                            }
                            if (response.formatted_hours) {
                                formattedHoursData = response.formatted_hours;
                            }
                            if (response.staff_schedules) {
                                staffSchedules = response.staff_schedules;
                            }
                        }


                        var responseData = {
                            working_hours: workingHoursData,
                            formatted_hours: formattedHoursData,
                            staff_schedules: staffSchedules || {}
                        };

                        // Cache the results for future use
                        providersCache[serviceId] = {
                            providers: staffMembers,
                            responseData: responseData
                        };

                        renderProviders(staffMembers, responseData);
                    } else {
                        console.error('Failed to load providers:', response);
                        $('#providers-container').html(
                            '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 0; width: 100%;">' +
                            '<div class="alert alert-warning" style="text-align: center;">' + (response.message || appointlyLang.error_loading_providers) + '</div>' +
                            '</div>'
                        );
                    }

                    // Reset loading flag
                    isLoadingProviders = false;
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    $('#providers-container').html(
                        '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 0; width: 100%;">' +
                        '<div class="alert alert-danger" style="text-align: center;">' + appointlyLang.error_loading_providers + '</div>' +
                        '</div>'
                    );

                    // Reset loading flag
                    isLoadingProviders = false;
                }
            });
        }

        /**
         * Check if provider has any working hours
         */
        function providerHasWorkingHours(workingHours) {
            if (!workingHours) return false;

            // If it's an array (formatted hours format)
            if (Array.isArray(workingHours)) {
                return workingHours.length > 0 && workingHours.some(function(day) {
                    return day && day.day && day.start && day.end;
                });
            }

            // If it's an object with day keys
            if (typeof workingHours === 'object') {
                // Check if any day is enabled
                for (var day in workingHours) {
                    if (workingHours.hasOwnProperty(day)) {
                        var dayData = workingHours[day];

                        if (!dayData) continue;

                        // Two ways to determine if a day is enabled:
                        // 1. If 'enabled' property is true
                        // 2. If a day has both start_time and end_time
                        var isEnabled = (dayData.enabled === true || dayData.enabled === '1' || dayData.enabled === 1);
                        var hasHours = (dayData.start_time && dayData.end_time);

                        // Special case: if using company schedule, we need to check if that day is enabled in the company schedule
                        if (dayData.use_company_schedule) {
                            console.log('Day ' + day + ' uses company schedule');
                            // Since we don't have direct access to company schedule here,
                            // we assume if use_company_schedule is true, then this day could be enabled
                            return true;
                        }

                        if (isEnabled && hasHours) {
                            console.log('Day ' + day + ' is enabled with hours');
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        /**
         * Render provider cards
         */
        function renderProviders(providers, responseData) {
            console.log('Rendering providers:', providers);
            console.log('Response data for providers:', responseData);

            // Reset loading flag
            isLoadingProviders = false;

            if (!providers || providers.length === 0) {
                $('#providers-container').html(
                    '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 0; width: 100%;">' +
                    '<div class="alert alert-warning" style="text-align: center;">' + appointlyLang.no_providers + '</div>' +
                    '</div>'
                );
                return;
            }

            var html = '';
            var availableProvidersCount = 0;
            staffSchedules = responseData.staff_schedules || {};

            console.log('Staff schedules in renderProviders:', staffSchedules);

            // Loop through providers and create cards
            providers.forEach(function(provider) {
                if (!provider || !provider.staffid) {
                    console.error('Invalid provider data:', provider);
                    return; // Skip this provider
                }

                var profileImage = provider.profile_image || site_url + 'assets/images/user-placeholder.jpg';
                var providerName = provider.firstname + ' ' + provider.lastname;

                // Get the provider-specific working hours if available
                var providerWorkingHours = {};
                var formattedHours = [];
                var hasWorkingHours = false;

                // Try to get provider-specific schedule from staff_schedules
                if (staffSchedules && staffSchedules[provider.staffid]) {
                    console.log('Found staff schedule for provider ' + provider.staffid);
                    var providerSchedule = staffSchedules[provider.staffid];

                    if (providerSchedule.working_hours) {
                        providerWorkingHours = providerSchedule.working_hours;
                        console.log('Provider ' + provider.staffid + ' working hours:', providerWorkingHours);
                    }

                    if (providerSchedule.formatted_hours) {
                        formattedHours = providerSchedule.formatted_hours;
                        console.log('Provider ' + provider.staffid + ' formatted hours (' + formattedHours.length + ' entries):', formattedHours);
                    }
                }
                // If not found in staff_schedules, try the global response data as fallback
                else if (responseData) {
                    if (responseData.working_hours) {
                        providerWorkingHours = responseData.working_hours;
                        console.log('Using global working hours for provider ' + provider.staffid);
                    }
                    if (responseData.formatted_hours) {
                        formattedHours = responseData.formatted_hours;
                        console.log('Using global formatted hours for provider ' + provider.staffid);
                    }
                }

                // Verify data integrity of formatted hours
                if (Array.isArray(formattedHours)) {
                    formattedHours.forEach(function(day, index) {
                        if (!day || !day.day || !day.start || !day.end) {
                            console.warn('Formatted hours entry ' + index + ' is incomplete:', day);
                        }
                    });
                } else {
                    console.warn('Formatted hours is not an array:', formattedHours);
                }

                // Check if provider has any working hours
                hasWorkingHours = providerHasWorkingHours(providerWorkingHours) ||
                    providerHasWorkingHours(formattedHours);

                console.log('Provider ' + provider.staffid + ' has working hours: ' + hasWorkingHours);

                // Save both working hours and formatted hours as data attributes
                var workingHoursAttr = '';
                var formattedHoursAttr = '';
                var disabledClass = !hasWorkingHours ? 'disabled-provider' : '';
                var cursorClass = !hasWorkingHours ? 'tw-cursor-not-allowed' : 'tw-cursor-pointer';
                var providerTooltip = !hasWorkingHours ? 'data-toggle="tooltip" title="' + appointlyLang.no_working_hours + '"' : '';

                if (Object.keys(providerWorkingHours).length > 0) {
                    workingHoursAttr = 'data-working-hours=\'' + JSON.stringify(providerWorkingHours) + '\'';
                }

                if (Array.isArray(formattedHours) && formattedHours.length > 0) {
                    formattedHoursAttr = 'data-formatted-hours=\'' + JSON.stringify(formattedHours) + '\'';
                }

                if (hasWorkingHours) {
                    availableProvidersCount++;
                }

                html += '<div class="provider-card tw-bg-white tw-border tw-border-neutral-200 tw-rounded-lg tw-p-6 tw-transition-all hover:tw-border-primary-500 ' + disabledClass + ' ' + cursorClass + '" ' +
                    'data-provider-id="' + provider.staffid + '" ' +
                    'data-firstname="' + provider.firstname + '" ' +
                    'data-lastname="' + provider.lastname + '" ' +
                    'data-email="' + (provider.email || '') + '" ' +
                    'data-phone="' + (provider.phonenumber || '') + '" ' +
                    'data-image="' + profileImage + '" ' +
                    'data-has-hours="' + (hasWorkingHours ? 'true' : 'false') + '" ' +
                    workingHoursAttr + ' ' +
                    formattedHoursAttr + ' ' +
                    providerTooltip + '>' +
                    '<div class="tw-flex tw-justify-between tw-items-start">' +
                    '<div class="tw-flex tw-items-center">' +
                    '<div class="tw-w-16 tw-h-16 tw-rounded-full tw-mr-4 tw-overflow-hidden">' +
                    '<img src="' + profileImage + '" alt="' + providerName + '" class="tw-w-full tw-h-full tw-object-cover' + (!hasWorkingHours ? ' tw-opacity-50' : '') + '">' +
                    '</div>' +
                    '<div>' +
                    '<h5 class="tw-font-semibold tw-text-lg tw-mb-1' + (!hasWorkingHours ? ' tw-text-neutral-400' : '') + '">' + providerName + '</h5>' +
                    (provider.email && appointlyShowStaffEmail == '1' ? '<p class="tw-text-sm tw-text-neutral-600 tw-mb-1' + (!hasWorkingHours ? ' tw-text-neutral-400' : '') + '">' + provider.email + '</p>' : '') +
                    (provider.phonenumber ? '<p class="tw-text-sm tw-t ext-neutral-600 tw-mb-0' + (!hasWorkingHours ? ' tw-text-neutral-400' : '') + '">' + provider.phonenumber + '</p>' : '') +
                    (!hasWorkingHours ? '<p class="tw-text-sm tw-text-red-500 tw-mt-1">' + appointlyLang.no_working_hours + '</p>' : '') +
                    '</div>' +
                    '</div>' +
                    '<button type="button" class="view-provider-details btn btn-sm btn-primary' + (!hasWorkingHours ? ' btn-outline' : '') + '" data-provider-id="' + provider.staffid + '">' +
                    appointlyLang.view_details +
                    '</button>' +
                    '</div>' +
                    '</div>';
            });

            // If no providers have working hours, show a message
            if (availableProvidersCount === 0) {
                $('#providers-container').html(
                    '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 0; width: 100%;">' +
                    '<div class="alert alert-warning" style="text-align: center;">' + appointlyLang.no_providers_with_hours + '</div>' +
                    '</div>'
                );
                return;
            }

            $('#providers-container').html(html);

            // Initialize tooltips if Bootstrap's tooltip is available
            if (typeof $.fn.tooltip !== 'undefined') {
                $('[data-toggle="tooltip"]').tooltip();
            }

            // Re-attach click handlers
            attachProviderClickHandlers();
        }

        /**
         * Attach click handlers to provider cards
         */
        function attachProviderClickHandlers() {
            console.log('Attaching click handlers to provider cards');

            // Detach any existing handlers to prevent duplicates
            $('.provider-card').off('click');
            $('.view-provider-details').off('click');

            // Provider card click
            $('.provider-card').on('click', function(e) {
                // Don't trigger if clicking on the view details button
                if ($(e.target).closest('.view-provider-details').length === 0) {
                    var providerId = $(this).data('provider-id');
                    var hasHours = $(this).data('has-hours');

                    // Only select providers with working hours
                    if (providerId && hasHours === true) {
                        selectProvider(providerId);
                    } else if (!hasHours) {
                        // Show message for providers without working hours
                        alert_float('warning', appointlyLang.no_working_hours);
                    }
                }
            });

            // View details button click
            $('.view-provider-details').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var providerId = $(this).data('provider-id');
                var $providerCard = $(this).closest('.provider-card');

                // Extract provider data from the card
                var provider = {
                    staffid: providerId,
                    firstname: $providerCard.data('firstname'),
                    lastname: $providerCard.data('lastname'),
                    email: $providerCard.data('email') || '',
                    phonenumber: $providerCard.data('phone') || '',
                    profile_image: $providerCard.data('image')
                };

                // Get working hours data - try both data attributes
                var workingHours = $providerCard.data('working-hours') || $providerCard.data('formatted-hours') || [];

                // Display the modal with provider details
                displayProviderDetails(provider, workingHours);
            });
        }

        /**
         * Display provider details in modal
         */
        function displayProviderDetails(provider, workingHours) {
            if (!provider) {
                console.error('Provider data is missing');
                return;
            }

            try {
                // Set modal data
                $('.provider-name').text(provider.firstname + ' ' + provider.lastname);
                $('.provider-avatar').attr('src', provider.profile_image || site_url + 'assets/images/user-placeholder.jpg');

                // Conditionally show/hide email based on setting
                if (appointlyShowStaffEmail == '1') {
                    $('.provider-email').text(provider.email || '').parent().show();
                } else {
                    $('.provider-email').parent().hide();
                }

                $('.provider-phone').text(provider.phonenumber || '');
                $('.select-provider').data('provider-id', provider.staffid);

                // Get formatted hours if available
                var formattedHours = $('*[data-provider-id="' + provider.staffid + '"]').data('formatted-hours');
                if (formattedHours && Array.isArray(formattedHours) && formattedHours.length > 0) {
                    console.log('Using formatted hours from data attribute:', formattedHours);

                    // Additional logging to verify data integrity
                    formattedHours.forEach(function(day, index) {
                        console.log('Formatted hours day ' + index + ':', day);
                    });

                    workingHours = formattedHours; // Use formatted hours instead
                } else {
                    console.log('No formatted hours available, using working hours directly');
                }

                // Render working hours
                var workingHoursHtml = generateWorkingHoursHtml(workingHours);
                $('.schedule-list').html(workingHoursHtml);

                // Show the modal
                $('#providerDetailsModal').modal('show');
            } catch (error) {
                console.error('Error displaying provider details:', error);
            }
        }

        /**
         * Generate working hours HTML
         */
        function generateWorkingHoursHtml(workingHours) {
            // Guard against undefined/null working hours
            if (!workingHours ||
                (Array.isArray(workingHours) && workingHours.length === 0) ||
                (typeof workingHours === 'object' && !Array.isArray(workingHours) && Object.keys(workingHours).length === 0)) {
                console.log('No working hours data available for HTML generation');
                return '<div class="tw-text-center tw-py-2">' + appointlyLang.no_working_hours + '</div>';
            }

            console.log('Working hours data for HTML generation:', workingHours);
            console.log('Working hours type:', Array.isArray(workingHours) ? 'Array' : typeof workingHours);

            if (Array.isArray(workingHours)) {
                console.log('Working hours array length:', workingHours.length);
                workingHours.forEach(function(day, index) {
                    console.log('Working hours day ' + index + ':', day);
                });
            }

            try {
                // Create mapping for day numbers to names
                var daysMap = {
                    1: appointlyLang.monday,
                    2: appointlyLang.tuesday,
                    3: appointlyLang.wednesday,
                    4: appointlyLang.thursday,
                    5: appointlyLang.friday,
                    6: appointlyLang.saturday,
                    7: appointlyLang.sunday
                };

                var html = '';

                // Check if workingHours is an object with day keys
                if (typeof workingHours === 'object' && !Array.isArray(workingHours)) {
                    console.log('Processing working hours as object with day keys');
                    // Handle object format with day keys
                    for (var dayNum in workingHours) {
                        if (workingHours.hasOwnProperty(dayNum)) {
                            var dayData = workingHours[dayNum];
                            var dayName = daysMap[dayNum] || 'Day ' + dayNum;
                            // Safely check if the day is enabled
                            var isEnabled = (dayData.enabled !== false && dayData.enabled !== '0');

                            console.log('Day ' + dayNum + ' (' + dayName + ') enabled:', isEnabled);
                            console.log('Day ' + dayNum + ' data:', dayData);

                            var timeRange = isEnabled && dayData.start_time && dayData.end_time ?
                                dayData.start_time + ' - ' + dayData.end_time :
                                '<span class="tw-text-red-500">' + appointlyLang.closed + '</span>';

                            html += '<div class="tw-flex tw-justify-between tw-py-2 tw-border-b tw-border-neutral-200 last:tw-border-b-0">' +
                                '<span>' + dayName + '</span>' +
                                '<span>' + timeRange + '</span>' +
                                '</div>';
                        }
                    }
                } else if (Array.isArray(workingHours)) {
                    console.log('Processing working hours as array');
                    // Handle array format (formatted_hours)
                    workingHours.forEach(function(dayData, index) {
                        if (!dayData) {
                            console.warn('Day data at index ' + index + ' is empty');
                            return;
                        }

                        var dayName = dayData.day || 'Unknown';
                        console.log('Processing day:', dayName, 'Start:', dayData.start, 'End:', dayData.end);

                        var timeRange = dayData.start && dayData.end ?
                            dayData.start + ' - ' + dayData.end :
                            '<span class="tw-text-red-500">' + appointlyLang.closed + '</span>';

                        html += '<div class="tw-flex tw-justify-between tw-py-2 tw-border-b tw-border-neutral-200 last:tw-border-b-0">' +
                            '<span>' + dayName + '</span>' +
                            '<span>' + timeRange + '</span>' +
                            '</div>';
                    });
                }

                if (!html) {
                    console.warn('No HTML generated for working hours');
                    return '<div class="tw-text-center tw-py-2">' + appointlyLang.no_working_hours + '</div>';
                }

                console.log('Generated HTML for working hours');
                return html;
            } catch (error) {
                console.error('Error generating working hours HTML:', error);
                return '<div class="tw-text-center tw-py-2">' + appointlyLang.no_working_hours + '</div>';
            }
        }

        /**
         * Check if a variable is set and not null
         */
        function isset(variable) {
            return typeof variable !== 'undefined' && variable !== null;
        }

        /**
         * Select a provider
         */
        function selectProvider(providerId) {
            console.log('Selecting provider with ID:', providerId);

            if (!providerId) {
                console.error('No provider ID provided');
                return;
            }

            // Update UI
            $('.provider-card').removeClass('selected');
            $('.provider-card[data-provider-id="' + providerId + '"]').addClass('selected');

            // Store the selection
            selectedProvider = providerId;
            $('#staff_id').val(providerId);
            $('#hidden_staff_id').val(providerId);

            // Reset date and time
            selectedDate = null;
            selectedTime = null;
            selectedEndTime = null;

            $('#appointment-date').val('');
            resetTimeSelection();

            // Clear time slots cache when provider changes
            timeSlotsCache = {};

            // console.log('Staff schedules when selecting provider:', staffSchedules);
            // console.log('Selected provider schedule:', staffSchedules[providerId]);

            // Initialize date picker for this provider
            initializeDatePicker(providerId);

            // If on step 2, move to step 3
            if (currentStep === 2) {
                currentStep = 3;
                updateStepDisplay(currentStep);
            }
        }

        /**
         * Initialize date picker for a provider
         */
        function initializeDatePicker(providerId) {
            console.log('Initializing date picker for provider:', providerId);

            if (!selectedService || !providerId) {
                console.error('Missing service ID or provider ID');
                return;
            }

            // Make sure jQuery and the required plugins are loaded
            if (typeof jQuery === 'undefined' || typeof jQuery.fn.datetimepicker === 'undefined') {
                console.error('jQuery or datetimepicker plugin not loaded. Cannot initialize date picker.');
                alert_float('danger', 'Error: Required JavaScript libraries are missing. Please contact support.');
                return;
            }

            // Reset and disable date picker while loading
            if ($('#appointment-date').data('xdsoft_datetimepicker')) {
                $('#appointment-date').datetimepicker('destroy');
            }
            $('#appointment-date').prop('disabled', true);
            $('#date_loading').removeClass('hide');

            // Get the provider's schedule
            var providerSchedule = staffSchedules[providerId] || {};
            var providerWorkingHours = providerSchedule.working_hours || {};

            // Create an array of days when the provider is available (0=Sunday, 6=Saturday)
            var availableDays = [];

            // Map weekday numbers to day names (1=Monday, 7=Sunday in our working_hours data)
            var dayMapping = {
                1: 'Monday',
                2: 'Tuesday',
                3: 'Wednesday',
                4: 'Thursday',
                5: 'Friday',
                6: 'Saturday',
                7: 'Sunday'
            };

            // Create mapping from JS day numbers (0=Sunday) to our day numbers (1=Monday)
            var jsToDayNumberMapping = {
                0: 7, // Sunday
                1: 1, // Monday
                2: 2, // Tuesday
                3: 3, // Wednesday
                4: 4, // Thursday
                5: 5, // Friday
                6: 6 // Saturday
            };

            // Check each day if the provider is available
            for (var dayNumber in providerWorkingHours) {
                if (providerWorkingHours.hasOwnProperty(dayNumber)) {
                    var dayData = providerWorkingHours[dayNumber];

                    // If day is enabled and has working hours, add to available days
                    if (dayData.enabled) {
                        // Convert our day number (1-7, Monday-Sunday) to JS day number (0-6, Sunday-Saturday)
                        var jsDayNumber;
                        if (dayNumber == 7) { // Sunday
                            jsDayNumber = 0;
                        } else {
                            jsDayNumber = parseInt(dayNumber);
                        }

                        availableDays.push(jsDayNumber);
                        console.log('Added available day:', dayMapping[dayNumber], 'JS day number:', jsDayNumber);
                    }
                }
            }

            console.log('Provider available days:', availableDays);

            // Get blocked days
            $.ajax({
                url: site_url + 'appointly/appointments_public/get_blocked_days',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Blocked days response:', response);
                    var blockedDays = [];
                    if (response && response.success && response.blocked_days) {
                        blockedDays = response.blocked_days.map(function(date) {
                            // Ensure all dates are in the format YYYY-MM-DD
                            return date.trim();
                        });
                    }

                    console.log('Formatted blocked days:', blockedDays);

                    // Initialize date picker with blocked days
                    var disablePastDates = <?php echo get_option('appointments_show_past_dates') == '1' ? 'true' : 'false'; ?>;
                    try {
                        // Create a function to check if a date is in the blocked days
                        var isDateBlocked = function(dateStr) {
                            return blockedDays.indexOf(dateStr) !== -1;
                        };

                        // Create basic options for datetimepicker
                        var datePickerOptions = {
                            format: 'Y-m-d',
                            timepicker: false,
                            datepicker: true,
                            scrollInput: false,
                            lazyInit: false,
                            minDate: disablePastDates ? 0 : false,
                            dayOfWeekStart: app.options.calendar_first_day || 0,
                            ignoreReadonly: true,
                            validateOnBlur: false,
                            onSelectDate: function(ct, $input) {
                                // Prevent default form submission
                                setTimeout(function() {
                                    var date = $input.val();
                                    if (date) {
                                        selectedDate = date;
                                        $('#appointment_date_field').val(date);
                                        loadTimeSlots(selectedService, selectedProvider, date);
                                        $('#time-slots-section').removeClass('tw-hidden');
                                    }
                                }, 50);
                                return false;
                            },
                            beforeShowDay: function(date) {
                                // Format date as YYYY-MM-DD for comparison
                                var dateStr = date.getFullYear() + '-' +
                                    ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                                    ('0' + date.getDate()).slice(-2);

                                // Check if date is blocked
                                if (isDateBlocked(dateStr)) {
                                    console.log('Disabling date:', dateStr);
                                    return [false, 'blocked-date'];
                                }

                                // Get the day of week (0-6, Sunday-Saturday)
                                var day = date.getDay();

                                // Check if this day of week is available for the provider
                                var isDayAvailable = availableDays.indexOf(day) !== -1;

                                if (!isDayAvailable) {
                                    console.log('Provider not available on', dateStr, '(day', day, ')');
                                    return [false, 'provider-unavailable'];
                                }

                                return [true, ''];
                            },
                            onGenerate: function(ct) {
                                console.log('Datepicker generated');

                                // Add additional processing after calendar is generated
                                setTimeout(function() {
                                    $('.xdsoft_date').each(function() {
                                        var $this = $(this);
                                        var dateData = $this.data();

                                        if (dateData) {
                                            // Format as YYYY-MM-DD
                                            var month = parseInt(dateData.month) + 1; // months are 0-based
                                            var dateStr = dateData.year + '-' +
                                                ('0' + month).slice(-2) + '-' +
                                                ('0' + dateData.date).slice(-2);

                                            // Check if this date is blocked
                                            if (isDateBlocked(dateStr)) {
                                                $this.addClass('xdsoft_disabled blocked-date');
                                                // Set the title attribute directly (not data attribute)
                                                $this.attr('title', appointlyLang.appointment_blocked_days || 'Company Holiday/Blocked Date');
                                                $this.css('cursor', 'not-allowed');
                                            }

                                            // Check if this is a day the provider doesn't work
                                            var jsDate = new Date(dateData.year, dateData.month, dateData.date);
                                            var day = jsDate.getDay(); // 0-6, Sunday-Saturday

                                            // Check if the date is in the past
                                            var isPastDate = jsDate < new Date().setHours(0, 0, 0, 0);

                                            if (isPastDate && !isDateBlocked(dateStr)) {
                                                // Past dates should be disabled with a tooltip
                                                $this.addClass('xdsoft_disabled');
                                                $this.attr('title', 'Past date');
                                            } else if (isDateBlocked(dateStr)) {
                                                $this.addClass('xdsoft_disabled blocked-date');
                                                $this.attr('title', appointlyLang.appointment_blocked_days || 'Company Holiday/Blocked Date');
                                                $this.css('cursor', 'not-allowed');
                                            } else if (availableDays.indexOf(day) === -1) {
                                                $this.addClass('xdsoft_disabled provider-unavailable');
                                                // Set title attribute directly
                                                $this.attr('title', appointlyLang.appointment_provider_unavailable || 'Provider does not work on this day');
                                                $this.css('cursor', 'not-allowed');
                                            } else {
                                                $this.attr('title', appointlyLang.appointment_available_days || 'Available for booking');
                                            }
                                        }
                                    });
                                }, 100);
                            }
                        };

                        // Initialize datetimepicker
                        $('#appointment-date').datetimepicker(datePickerOptions).prop('disabled', false);
                        console.log('Date picker initialized successfully with blocked days');
                        $('#date_loading').addClass('hide');

                        // Simple tooltip fix - ensure all date cells have proper title attributes
                        setTimeout(function() {
                            $('.xdsoft_date').each(function() {
                                var $this = $(this);
                                // If the date doesn't have a title but has a class that should have a tooltip
                                if (!$this.attr('title')) {
                                    if ($this.hasClass('blocked-date')) {
                                        $this.attr('title', appointlyLang.appointment_blocked_days || 'Company Holiday/Blocked Date');
                                    } else if ($this.hasClass('provider-unavailable')) {
                                        $this.attr('title', appointlyLang.appointment_provider_unavailable || 'Provider does not work on this day');
                                    } else if ($this.hasClass('xdsoft_disabled')) {
                                        $this.attr('title', 'Unavailable date');
                                    } else {
                                        $this.attr('title', appointlyLang.appointment_available_days || 'Available for booking');
                                    }
                                }
                            });
                        }, 200);
                    } catch (e) {
                        console.error('Error initializing datetimepicker:', e);
                        alert_float('danger', 'Error initializing date picker: ' + e.message);
                        $('#date_loading').addClass('hide');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching blocked days:', error);
                    $('#date_loading').addClass('hide');

                    try {
                        // Initialize date picker anyway with default settings
                        var disablePastDates = <?php echo get_option('appointments_show_past_dates') == '1' ? 'true' : 'false'; ?>;
                        var datePickerOptions = {
                            format: 'Y-m-d',
                            timepicker: false,
                            datepicker: true,
                            scrollInput: false,
                            lazyInit: false,
                            minDate: disablePastDates ? 0 : false,
                            dayOfWeekStart: app.options.calendar_first_day || 0,
                            beforeShowDay: function(date) {
                                // Get the day of week (0-6, Sunday-Saturday)
                                var day = date.getDay();

                                // Check if this day of week is available for the provider
                                var isDayAvailable = availableDays.indexOf(day) !== -1;

                                return [isDayAvailable, !isDayAvailable ? 'provider-unavailable' : ''];
                            },
                            onGenerate: function(ct) {
                                setTimeout(function() {
                                    $('.xdsoft_date').each(function() {
                                        var $this = $(this);
                                        var dateData = $this.data();

                                        if (dateData) {
                                            var jsDate = new Date(dateData.year, dateData.month, dateData.date);
                                            var day = jsDate.getDay(); // 0-6, Sunday-Saturday

                                            if (availableDays.indexOf(day) === -1) {
                                                $this.addClass('xdsoft_disabled provider-unavailable');
                                            }
                                        }
                                    });
                                }, 100);
                            }
                        };

                        $('#appointment-date').datetimepicker(datePickerOptions).prop('disabled', false);
                        console.log('Date picker initialized with default settings');
                    } catch (e) {
                        console.error('Error initializing datetimepicker with default settings:', e);
                        alert_float('danger', 'Error initializing date picker: ' + e.message);
                    }
                }
            });
        }

        /**
         * Load time slots for a specific date
         */
        function loadTimeSlots(serviceId, providerId, date) {
            console.log('Loading time slots for service', serviceId, 'provider', providerId, 'date', date);

            // Create cache key
            var cacheKey = serviceId + '_' + providerId + '_' + date;

            // Check if we already have cached data
            if (timeSlotsCache[cacheKey]) {
                console.log('Using cached time slots for:', cacheKey);
                renderTimeSlots(timeSlotsCache[cacheKey]);
                return;
            }

            // Prevent multiple simultaneous requests
            if (isLoadingTimeSlots) {
                console.log('Already loading time slots, skipping request');
                return;
            }

            isLoadingTimeSlots = true;

            // Show the time slots section
            $('#time-slots-section').removeClass('tw-hidden');

            // Show loading indicator only if container is empty
            if ($('#time-slots-container').html().trim() === '') {
                $('#slot-loading').removeClass('hidden');
                // Clear any existing time slots
                $('#time-slots-container').empty();
            }

            var timezone = $('#timezone').val() || Intl.DateTimeFormat().resolvedOptions().timeZone;

            // Prepare data for the request
            var ajaxData = {
                service_id: serviceId,
                provider_id: providerId, // Using provider_id as the key
                staff_id: providerId, // Also include staff_id for backward compatibility
                date: date,
                timezone: timezone
            };

            // Add CSRF token if available
            if (typeof csrfData !== 'undefined') {
                ajaxData[csrfData.token_name] = csrfData.hash;
            } else if (typeof csrfTokenName !== 'undefined' && typeof csrfTokenValue !== 'undefined') {
                ajaxData[csrfTokenName] = csrfTokenValue;
            }

            // Send AJAX request to get available time slots
            $.ajax({
                url: site_url + 'appointly/appointments_public/get_available_time_slots',
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                success: function(response) {
                    console.log('Time slots response:', response);

                    // Cache the response
                    timeSlotsCache[cacheKey] = response;

                    // Render the time slots
                    renderTimeSlots(response);

                    // Reset loading flag
                    isLoadingTimeSlots = false;
                },
                error: function(xhr, status, error) {
                    console.error('Error loading time slots:', error);

                    // Hide loading indicator
                    $('#slot-loading').addClass('hidden');

                    // Show error message
                    $('#time-slots-container').html(
                        '<div class="tw-col-span-full tw-text-center tw-py-6 tw-text-red-600">' +
                        appointlyLang.error_loading_slots +
                        '</div>'
                    );

                    $('.btn-next[data-step="3"]').prop('disabled', true);
                    alert_float('danger', appointlyLang.error_loading_slots);

                    // Reset loading flag
                    isLoadingTimeSlots = false;
                }
            });
        }

        /**
         * Render time slots from response data
         */
        function renderTimeSlots(response) {
            console.log('Rendering time slots:', response);

            // Hide loading indicator
            $('#slot-loading').addClass('hidden');

            // Get the time slots container
            var $timeSlotContainer = $('#time-slots-container');
            $timeSlotContainer.empty();

            if (response && response.success && response.time_slots && response.time_slots.length > 0) {
                // Track if we have available slots
                var hasAvailableSlots = false;

                // Add time slots as buttons
                response.time_slots.forEach(function(slot) {
                    if (slot.available !== false) {
                        hasAvailableSlots = true;

                        // Create button element for the time slot
                        var $timeSlotBtn = $('<button>', {
                            type: 'button',
                            class: 'time-slot-btn',
                            'data-start-time': slot.value,
                            'data-end-time': slot.end_time,
                            'data-text': slot.text,
                            html: '<span>' + slot.text + '</span>'
                        });

                        // Add event listener to select this time slot
                        $timeSlotBtn.on('click', function(e) {
                            // Prevent default form submission behavior
                            e.preventDefault();
                            e.stopPropagation();

                            console.log('Time slot clicked:', $(this).data('text'));

                            // Remove selected class from all buttons
                            $('.time-slot-btn').removeClass('selected');

                            // Add selected class to this button
                            $(this).addClass('selected');

                            // Store the selected time values
                            selectedTime = $(this).data('start-time');
                            selectedEndTime = $(this).data('end-time');

                            // Update hidden inputs
                            $('#start_hour').val(selectedTime);
                            $('#end_hour').val(selectedEndTime);

                            // Enable the next button
                            $('.btn-next[data-step="3"]').prop('disabled', false);

                            // Update appointment summary without regenerating datepicker
                            updateAppointmentSummary();

                            // Smooth scroll to next button with focus
                            setTimeout(function() {
                                var $nextButton = $('.btn-next[data-step="3"]');
                                if ($nextButton.length) {
                                    $nextButton.focus();
                                    $('html, body').animate({
                                        scrollTop: $nextButton.offset().top - 100
                                    }, 500, 'swing');
                                }
                            }, 100);

                            // Return false to prevent bubbling and form submission
                            return false;
                        });

                        $timeSlotContainer.append($timeSlotBtn);
                    }
                });

                // Show no slots message if no available slots
                if (!hasAvailableSlots) {
                    $timeSlotContainer.html(
                        '<div class="alert alert-warning tw-col-span-full tw-text-center tw-py-6 tw-text-neutral-500">' +
                        appointlyLang.no_slots_available +
                        '</div>'
                    );
                    $('.btn-next[data-step="3"]').prop('disabled', true);
                }
            } else {
                // No slots available
                var message = (response && response.message) ? response.message : appointlyLang.no_slots_available;

                $timeSlotContainer.html(
                    '<div class="alert alert-warning tw-col-span-full tw-text-center tw-py-6 tw-text-neutral-500">' +
                    message +
                    '</div>'
                );
                $('.btn-next[data-step="3"]').prop('disabled', true);
            }
        }

        /**
         * Reset time selection
         */
        function resetTimeSelection() {
            // Clear time slots container
            $('#time-slots-container').empty();

            // Hide the time slots section
            $('#time-slots-section').addClass('tw-hidden');

            // Clear hidden inputs
            $('#start_hour').val('');
            $('#end_hour').val('');

            // Disable next button
            $('.btn-next[data-step="3"]').prop('disabled', true);
        }

        /**
         * Update appointment summary
         */
        function updateAppointmentSummary() {
            console.log('Updating appointment summary');

            // Service info
            var serviceName = '';
            var serviceDuration = '';
            var servicePrice = '';

            $('.service-card').each(function() {
                if ($(this).data('service-id') == selectedService) {
                    serviceName = $(this).find('h5').text();
                    serviceDuration = $(this).data('duration') || '';
                    servicePrice = $(this).data('price') || '';
                }
            });

            // Provider info
            var providerName = '';

            $('.provider-card').each(function() {
                if ($(this).data('provider-id') == selectedProvider) {
                    providerName = $(this).find('h5').text();
                }
            });

            // Format date
            var formattedDate = selectedDate ? moment(selectedDate, 'YYYY-MM-DD').format('LL') : '';

            // Format time
            var formattedTime = '';
            if (selectedTime) {
                formattedTime = moment(selectedTime, 'HH:mm').format('LT');

                if (selectedEndTime) {
                    formattedTime += ' - ' + moment(selectedEndTime, 'HH:mm').format('LT');
                }
            }

            // Update step 3 summary fields
            $('#summary-service-name').text(serviceName || '-');
            $('#summary-provider-name').text(providerName || '-');

            // Update the final summary in step 4
            $('#final-service-name').text(serviceName || '-');
            $('#final-service-price').text(servicePrice || '-');
            $('#final-provider-name').text(providerName || '-');

            $('#final-date-time').text((formattedDate ? formattedDate + ', ' : '') + (formattedTime || '-'));

            // Prevent event propagation that might cause datepicker to regenerate
            return false;
        }

        /**
         * Reset form to initial state
         */
        function resetForm() {
            console.log('Resetting form');

            // Reset variables
            selectedService = null;
            selectedProvider = null;
            selectedDate = null;
            selectedTime = null;
            selectedEndTime = null;

            // Reset service selection
            $('.service-card').removeClass('selected');

            // Reset provider selection
            $('.provider-card').removeClass('selected');
            $('#providers-container').empty();

            // Reset date and time selection
            $('#appointment-date').val('').prop('disabled', true);
            resetTimeSelection();

            // Reset hidden inputs
            $('input[name="service_id"]').val('');
            $('input[name="staff_id"]').val('');
            $('input[name="start_hour"]').val('');
            $('input[name="end_hour"]').val('');

            // Reset contact form fields
            $('#appointment-form').find('input[type="text"], input[type="email"], textarea').val('');

            // Reset current step
            currentStep = 1;
            updateStepDisplay(currentStep);
        }
    });
</script>
