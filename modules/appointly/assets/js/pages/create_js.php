<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(function() {
        // make sidebar active
        $('li.menu-item-appointly').addClass('active');
        $('li.menu-item-appointly > ul').addClass('in');
        $('li.sub-menu-item-appointly-user-dashboard').addClass('active');
        // Flag to prevent duplicate submissions
        var isSubmitting = false;
        var blockedDays = <?php
                            $blocked_days = get_appointly_blocked_days();
                            echo json_encode($blocked_days);
                            ?>;

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

        // Initialize the page as soon as it loads
        initializePage();

        /**
         * Initialize all page components
         */
        function initializePage() {

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
            // Initialize date picker
            initializeDateTimePickers();

            // Initialize AJAX search for leads/contacts
            initRelationSearch();

            // Set up event handlers
            setupEventHandlers();

            // Handle appointment type on initial load
            var relType = $('#rel_type').val();
            appointlyHandleTypeChange(relType, true);

            // Setup form validation
            initializeFormValidation();

            // Ensure provider is disabled until service is selected
            if (!$('#service_id').val()) {
                $('#provider_id').prop('disabled', true).selectpicker('refresh');
            }

            // If relType is set from PHP (when client_id is provided)
            if (relType) {
                appointlyHandleTypeChange(relType, true);

                // If this was initialized with a client_id, also handle showing client fields
                var clientId = $('select[name="contact_id"]').data('client-id');
                var contactId = $('select[name="contact_id"]').val();

                if (clientId && relType === 'internal') {
                    $('#select_contacts').removeClass('hidden');

                    // Force refresh the contact selectpicker
                    setTimeout(function() {
                        $('select[name="contact_id"]').selectpicker('refresh');

                        // If contact is selected, fetch the contact data
                        if (contactId) {
                            appointlyFetchContactData(contactId, false);
                        }
                    }, 300);
                } else if (relType === 'lead_related') {
                    // For lead-related appointments, handle showing lead fields
                    $('#rel_id_wrapper').removeClass('hide');

                    // Get rel_id from URL if it exists
                    var leadId = '<?= isset($rel_id) ? $rel_id : '' ?>';

                    // Initialize lead AJAX search with proper parameters
                    init_ajax_search('lead', $('#rel_id.ajax-search'), {
                        rel_id: leadId,
                        type: 'leads'
                    }, admin_url + 'leads/search_contacts');

                    // If lead ID is available, fetch lead data after a short delay
                    if (leadId) {
                        setTimeout(function() {
                            appointlyFetchContactData(leadId, true);
                        }, 500);
                    }
                }
            }
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
                    console.log('Provider schedule loaded:', response);

                    if (response && response.success && response.data && response.data.schedule) {
                        // Store provider schedule globally
                        window.providerSchedule = response.data.schedule;

                        // Reinitialize the date picker with provider schedule
                        updateDatePickerWithProviderSchedule(blockedDays, showPastDates, relType);
                    } else {
                        console.log('No provider schedule found, using default calendar');
                        // Initialize normal calendar
                        initializeDateTimePickers();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading provider schedule:', error);
                    // Initialize normal calendar as fallback
                    initializeDateTimePickers();
                }
            });
        }

        /**
         * Update date picker with provider schedule
         */
        function updateDatePickerWithProviderSchedule(blockedDays, showPastDates, relType) {
            console.log('Updating date picker with provider schedule');

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
         * Initialize date picker with proper settings
         */
        function initializeDateTimePickers() {
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

            // Initialize select pickers
            init_selectpicker();
        }

        // Helper function to pad numbers with leading zeros
        function padZero(num) {
            return (num < 10 ? '0' : '') + num;
        }

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
                appointlyGetTimeSlots(0, providerId, formattedDate, '', $('#timezone').val(), 0);
            } else {
                // For regular appointments, we need provider and service
                var providerId = $('#provider_id').val();
                var serviceId = $('#service_id').val();

                if (providerId && serviceId) {
                    $('#slot_loading').removeClass('hide');
                    appointlyGetTimeSlots(serviceId, providerId, formattedDate, '', $('#timezone').val(), 0);
                } else {
                    // Show message indicating service and provider are needed
                    resetTimeSelectionRequireServiceProvider();
                }
            }
        });

        /**
         * Setup all event handlers for interactive elements
         */
        function setupEventHandlers() {
            // Appointment type change
            $('#rel_type').on('change', function() {
                // Call helper function directly
                appointlyHandleTypeChange($(this).val(), false);
            });

            // Service selection handling
            $('#service_id').on('change', function() {
                var serviceId = $(this).val();
                var selectedOption = $(this).find('option:selected');
                var serviceDuration = selectedOption.data('duration');

                // Update duration
                if (serviceDuration) {
                    $('#appointment_duration').val(serviceDuration);
                }

                // Only reset time slots, preserve the selected date
                resetTimeSelection();

                // Get providers for this service
                if (serviceId) {
                    appointlyLoadProviders(serviceId);
                } else {
                    // Clear provider dropdown but keep it enabled
                    $('#provider_id').html('<option value=""><?= _l('appointment_select_provider'); ?></option>');
                    $('#provider_id').selectpicker('refresh');
                }
            });

            // Provider change handling
            $('#provider_id').on('change', function() {
                var providerId = $(this).val();
                var serviceId = $('#service_id').val();

                // Only reset time slots, preserve the selected date
                resetTimeSelection();

                // If provider is selected, load their schedule and update calendar
                if (providerId) {
                    loadProviderScheduleAndUpdateCalendar(providerId);

                    // If date and service are already selected, automatically load time slots
                    var selectedDate = $('#appointment_date').val();
                    if (selectedDate && serviceId) {
                        $('#slot_loading').removeClass('hide');
                        var formattedDate = appointlyFormatDate(selectedDate);
                        appointlyGetTimeSlots(serviceId, providerId, formattedDate, '', $('#timezone').val(), 0);
                    }
                }
            });

            // Duration display sync
            $('#duration_display').on('change', function() {
                $('#appointment_duration').val($(this).val());
            });

            // Notification checkbox handlers
            $('#by_sms, #by_email').on('change', function() {
                if ($('#by_sms').prop('checked') || $('#by_email').prop('checked')) {
                    $('.appointment-reminder').removeClass('hide');
                } else {
                    $('.appointment-reminder').addClass('hide');
                }
            });

            // Contact selection handlers
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

            // Handle time slot selection
            $('#available_times').on('change', function() {
                handleTimeSlotChange($(this));
            });
        }

        /**
         * Initialize AJAX search for leads/contacts
         */
        function initRelationSearch() {
            // Initialize AJAX search for contacts
            init_ajax_search('contact', '#contact_id.ajax-search');

            // Initialize AJAX search for leads
            if (typeof init_ajax_search === 'function') {
                // Initialize lead search if lead_related is selected
                if ($('#rel_type').val() === 'lead_related') {
                    console.log("Initializing lead search on page load");
                    // Ensure rel_id_wrapper is visible
                    $('#rel_id_wrapper').removeClass('hide');

                    // Force initialize AJAX search for leads with the correct parameters
                    init_ajax_search('lead', $('#rel_id'), {
                        rel_id: $('#rel_id').val(),
                        type: 'leads'
                    });
                }
            }
        }

        /**
         * Initialize form validation using Perfex CRM's appFormValidator
         */
        function initializeFormValidation() {
            $('#appointment-form').appFormValidator({
                rules: {
                    subject: 'required',
                    rel_type: 'required',
                    date: 'required',
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
                    contact_id: {
                        required: function() {
                            return $('#rel_type').val() === 'internal';
                        }
                    },
                    rel_id: {
                        required: function() {
                            return $('#rel_type').val() === 'lead_related';
                        }
                    },
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
                    }
                },
                submitHandler: function(form) {
                    if (isSubmitting) return false;

                    var $form = $(form);
                    var relType = $('#rel_type').val();
                    var $submitBtn = $('#appointment_submit');

                    // Check attendees for internal_staff appointments (same as update side)
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

                            return false;
                        }
                    }

                    // Mark as submitting and update UI
                    isSubmitting = true;
                    $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?= _l('submit'); ?>');
                    $form.find('button, input[type="submit"], a.btn').prop('disabled', true);

                    // For staff-only appointments, ensure duration is set
                    if (relType === 'internal_staff') {
                        var duration = $('#duration_display').val() || 60;
                        $('#appointment_duration').val(duration);
                    }

                    // Get form data and exclude outlook checkbox
                    var formData = $form.serializeArray();
                    var filteredData = formData.filter(function(item) {
                        return item.name !== 'outlook' && item.name !== 'google';
                    });

                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        data: filteredData,
                        dataType: 'json',
                        success: function(response) {
                            if (response && (response.success || response.result)) {
                                // Check if calendar integrations are requested
                                var outlookChecked = $('#outlook').is(':checked');
                                var googleChecked = $('#google').is(':checked');

                                if (outlookChecked && googleChecked && response.appointment_id) {
                                    // Handle both integrations - start with Outlook, then Google
                                    handleBothCalendarIntegrations(response.appointment_id);
                                } else if (outlookChecked && response.appointment_id) {
                                    // Handle Outlook integration only
                                    handleOutlookIntegration(response.appointment_id);
                                } else if (googleChecked && response.appointment_id) {
                                    // Handle Google Calendar integration only
                                    handleGoogleIntegration(response.appointment_id);
                                } else {
                                    // Show success message and redirect
                                    alert_float('success', "<?= _l('appointment_created'); ?>");
                                    window.location.href = admin_url + 'appointly/appointments';
                                }
                            } else {

                                handleFormError(response && response.message ? response.message : "<?= _l('appointment_could_not_be_created'); ?>");
                            }
                        }
                    });

                    return false;
                }
            });
        }

        /**
         * Helper function to handle form errors
         */
        function handleFormError(message) {
            alert_float('danger', message);
            isSubmitting = false;
            $('#appointment_submit').prop('disabled', false).html("<?= _l('submit') ?>");
            $('.close_btn').prop('disabled', false);
        }

        /**
         * Handle Outlook integration after appointment creation
         */
        function handleOutlookIntegration(appointmentId) {

            if (!isOutlookLoggedIn()) {

                signInToOutlook();

                alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_outlook_auth_error'); ?>");
                window.location.href = admin_url + 'appointly/appointments';
                return;
            }

            // User is authenticated, create Outlook event
            createOutlookEventForAppointment(appointmentId);
        }

        /**
         * Create Outlook event for the appointment
         */
        function createOutlookEventForAppointment(appointmentId, callback) {

            myMSALObj.acquireTokenSilent(outlookConf.requestObj)
                .then(function(tokenResponse) {
                    var accessToken = tokenResponse.accessToken;

                    // Use the same logic as the update page
                    var selectedAttendees = $('select[name="attendees[]"]').val();


                    if (selectedAttendees && selectedAttendees.length > 0) {

                        // Get attendee data via AJAX (staff members)
                        $.post(site_url + 'appointly/appointments/getAttendeeData', {
                            ids: selectedAttendees,
                            [csrfTokenName]: csrfTokenValue
                        }).done(function(attendees) {
                            // Check if attendees is already an array or needs parsing
                            if (typeof attendees === 'string') {
                                try {
                                    attendees = JSON.parse(attendees);
                                } catch (e) {
                                    attendees = [];
                                }
                            }

                            // Add related contact based on appointment type
                            var rel_type = $('#rel_type').val();

                            if (rel_type === 'external') {
                                var external_name = $('#name').val();
                                var external_email = $('#email').val();

                                if (external_name && external_email) {
                                    attendees.push({
                                        emailAddress: {
                                            address: external_email,
                                            name: external_name
                                        },
                                        type: "required"
                                    });
                                }
                                createOutlookEventWithAttendees(attendees, accessToken, appointmentId, callback);
                            } else if (rel_type === 'internal') {
                                // For internal appointments, check if there's a selected contact
                                var contact_select = $('#contact_id');
                                var contact_id = contact_select.val();

                                if (contact_id) {
                                    var selectedOption = contact_select.find('option:selected');
                                    var contactText = selectedOption.text();

                                    // Fetch contact email via AJAX
                                    $.post(site_url + 'appointly/appointments/getContactEmail', {
                                        contact_id: contact_id,
                                        [csrfTokenName]: csrfTokenValue
                                    }).done(function(contactEmailData) {
                                        try {
                                            if (typeof contactEmailData === 'string') {
                                                contactEmailData = JSON.parse(contactEmailData);
                                            }

                                            if (contactEmailData && contactEmailData.email) {
                                                attendees.push({
                                                    emailAddress: {
                                                        address: contactEmailData.email,
                                                        name: contactEmailData.name || contactText.split(' - ')[0]
                                                    },
                                                    type: "required"
                                                });
                                            } else {}
                                        } catch (e) {
                                            console.error('Error parsing contact email data:', e);
                                        }

                                        // Continue with event creation after trying to add contact
                                        createOutlookEventWithAttendees(attendees, accessToken, appointmentId, callback);
                                    }).fail(function(xhr, status, error) {
                                        console.error('Failed to get contact email:', status, error);
                                        console.error('XHR response:', xhr.responseText);

                                        // Continue without contact email
                                        createOutlookEventWithAttendees(attendees, accessToken, appointmentId, callback);
                                    });
                                } else {
                                    createOutlookEventWithAttendees(attendees, accessToken, appointmentId, callback);
                                }
                            } else if (rel_type === 'lead_related') {
                                var contact_select = $('#rel_id');
                                var contact_id = contact_select.val();

                                if (contact_id) {
                                    var selectedOption = contact_select.find('option:selected');
                                    var contactText = selectedOption.text();
                                }

                                createOutlookEventWithAttendees(attendees, accessToken, appointmentId, callback);
                            } else {
                                createOutlookEventWithAttendees(attendees, accessToken, appointmentId, callback);
                            }
                        }).fail(function(xhr, status, error) {
                            console.error('Failed to get attendee data:', status, error);
                            console.error('XHR response:', xhr.responseText);
                            createOutlookEventWithAttendees([], accessToken, appointmentId, callback);
                        });
                    } else {

                        // Start with empty array
                        var attendees = [];

                        // 1. Add the provider (service provider) to attendees
                        var provider_id = $('#provider_id').val();

                        if (provider_id) {
                            // Get provider details via AJAX
                            $.post(site_url + 'appointly/appointments/getAttendeeData', {
                                ids: [provider_id],
                                [csrfTokenName]: csrfTokenValue
                            }).done(function(providerData) {
                                try {
                                    if (typeof providerData === 'string') {
                                        providerData = JSON.parse(providerData);
                                    }
                                    if (Array.isArray(providerData) && providerData.length > 0) {
                                        attendees = attendees.concat(providerData);
                                    } else {}
                                } catch (e) {
                                    console.error('Error parsing provider data:', e);
                                }

                                // 2. Add related contact based on appointment type
                                addRelatedContactAndCreateEvent(attendees);
                            }).fail(function(xhr, status, error) {
                                console.error('Failed to get provider data:', status, error);
                                console.error('XHR response:', xhr.responseText);
                                // Still try to add related contact
                                addRelatedContactAndCreateEvent(attendees);
                            });
                        } else {
                            // No provider, just add related contact
                            addRelatedContactAndCreateEvent(attendees);
                        }

                        function addRelatedContactAndCreateEvent(attendees) {
                            var rel_type = $('#rel_type').val();


                            if (rel_type === 'external') {
                                var external_name = $('#name').val();
                                var external_email = $('#email').val();

                                if (external_name && external_email) {
                                    attendees.push({
                                        emailAddress: {
                                            address: external_email,
                                            name: external_name
                                        },
                                        type: "required"
                                    });
                                } else {}
                            } else if (rel_type === 'lead_related') {
                                var contact_select = $('#rel_id');
                                var contact_id = contact_select.val();

                                if (contact_id) {
                                    var selectedOption = contact_select.find('option:selected');
                                    var contactText = selectedOption.text();
                                }
                            } else if (rel_type === 'internal') {


                                // For internal appointments, check if there's a selected contact
                                var contact_select = $('#contact_id');
                                var contact_id = contact_select.val();

                                if (contact_id) {
                                    var selectedOption = contact_select.find('option:selected');
                                    var contactText = selectedOption.text();

                                    // Fetch contact email via AJAX
                                    $.post(site_url + 'appointly/appointments/getContactEmail', {
                                        contact_id: contact_id,
                                        [csrfTokenName]: csrfTokenValue
                                    }).done(function(contactEmailData) {
                                        try {
                                            if (typeof contactEmailData === 'string') {
                                                contactEmailData = JSON.parse(contactEmailData);
                                            }

                                            if (contactEmailData && contactEmailData.email) {
                                                attendees.push({
                                                    emailAddress: {
                                                        address: contactEmailData.email,
                                                        name: contactEmailData.name || contactText.split(' - ')[0]
                                                    },
                                                    type: "required"
                                                });
                                            } else {}
                                        } catch (e) {
                                            console.error('Error parsing contact email data:', e);
                                        }

                                        // Continue with event creation after trying to add contact
                                        createOutlookEventWithAttendees(attendees, accessToken, appointmentId, callback);
                                    }).fail(function(xhr, status, error) {
                                        console.error('Failed to get contact email:', status, error);
                                        console.error('XHR response:', xhr.responseText);

                                        // Continue without contact email
                                        createOutlookEventWithAttendees(attendees, accessToken, appointmentId, callback);
                                    });

                                    return; // Exit here, createOutlookEvent will be called from AJAX callback
                                } else {}
                            } else {}

                            createOutlookEventWithAttendees(attendees, accessToken, appointmentId, callback);
                        }
                    }

                    function createOutlookEventWithAttendees(attendees, accessToken, appointmentId, callback) {
                        // Get form data like the update page does
                        var date = $('#appointment_date').val();
                        var time = $('#available_times').val();
                        var subject = $('#subject').val();
                        var description = $('#description').val() || '';
                        var address = $('#address').val() || '';
                        var duration = $('#appointment_duration').val() || 60;

                        // Combine date and time properly
                        var dateTimeStr = date + ' ' + time;
                        var formattedDate = moment(dateTimeStr, [
                            'DD/MM/YYYY HH:mm',
                            'DD.MM.YYYY HH:mm',
                            'MM-DD-YYYY HH:mm',
                            'MM.DD.YYYY HH:mm',
                            'MM/DD/YYYY HH:mm',
                            'YYYY-MM-DD HH:mm'
                        ]).format();

                        // Calculate end time using duration
                        var endDateTime = moment(formattedDate).add(parseInt(duration), 'minutes').format();

                        // Create event object
                        var event = {
                            subject: subject,
                            body: {
                                contentType: "HTML",
                                content: description
                            },
                            start: {
                                dateTime: formattedDate,
                                timeZone: app.options.timezone || 'UTC'
                            },
                            end: {
                                dateTime: endDateTime,
                                timeZone: app.options.timezone || 'UTC'
                            },
                            location: {
                                displayName: address
                            },
                            attendees: attendees,
                            isReminderOn: true,
                            reminderMinutesBeforeStart: <?= get_option('appointly_google_meet_reminder_minutes') ?: 30 ?>
                        };

                        // Create event in Outlook
                        $.ajax({
                            url: 'https://graph.microsoft.com/v1.0/me/events',
                            type: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + accessToken,
                            },
                            data: JSON.stringify(event)
                        }).done(function(response) {
                            if (response.id) {
                                // Save Outlook event ID to database
                                $.post(site_url + 'appointly/appointments/save_outlook_event_id', {
                                    appointment_id: appointmentId,
                                    outlook_event_id: response.id,
                                    outlook_calendar_link: response.webLink || '',
                                    [csrfTokenName]: csrfTokenValue
                                }).done(function(saveResponse) {
                                    if (callback) {
                                        callback(true);
                                    } else {
                                        alert_float('success', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_added_to_outlook'); ?>");
                                        window.location.href = admin_url + 'appointly/appointments';
                                    }
                                }).fail(function(xhr, status, error) {

                                    if (callback) {
                                        callback(false);
                                    } else {
                                        alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_outlook_event_save_failed'); ?>");
                                        window.location.href = admin_url + 'appointly/appointments';
                                    }
                                });
                            }
                        }).fail(function(xhr) {
                            console.error('Outlook API Error:', xhr);
                            if (xhr.status === 401) {
                                // Token expired, try to refresh
                                myMSALObj.acquireTokenPopup(outlookConf.requestObj)
                                    .then(function(newTokenResponse) {
                                        // Retry with new token
                                        createOutlookEventForAppointment(appointmentId, callback);
                                    })
                                    .catch(function(error) {
                                        console.error('Token refresh failed:', error);
                                        if (callback) {
                                            callback(false);
                                        } else {
                                            alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_outlook_auth_error'); ?>");
                                            window.location.href = admin_url + 'appointly/appointments';
                                        }
                                    });
                            } else {
                                if (callback) {
                                    callback(false);
                                } else {
                                    alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_outlook_error'); ?>");
                                    window.location.href = admin_url + 'appointly/appointments';
                                }
                            }
                        });
                    }
                })
                .catch(function(error) {
                    console.error('Token acquisition failed:', error);
                    if (requiresInteraction(error.errorCode)) {
                        myMSALObj.acquireTokenPopup(outlookConf.requestObj)
                            .then(function(tokenResponse) {
                                createOutlookEventForAppointment(appointmentId, callback);
                            })
                            .catch(function(error) {
                                console.error('Token acquisition failed:', error);
                                if (callback) {
                                    callback(false);
                                } else {
                                    alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_outlook_auth_error'); ?>");
                                    window.location.href = admin_url + 'appointly/appointments';
                                }
                            });
                    } else {
                        if (callback) {
                            callback(false);
                        } else {
                            alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_outlook_error'); ?>");
                            window.location.href = admin_url + 'appointly/appointments';
                        }
                    }
                });
        }

        /**
         * Handle Google Calendar integration after appointment creation
         */
        function handleGoogleIntegration(appointmentId) {
            // For Google Calendar integration, we need to redirect to the add to Google Calendar endpoint
            // This is similar to how it's done in the update page
            var form = $('<form>', {
                'method': 'POST',
                'action': admin_url + 'appointly/appointments/addEventToGoogleCalendar'
            });

            // Add appointment ID
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'appointment_id',
                'value': appointmentId
            }));

            // Add CSRF token
            form.append($('<input>', {
                'type': 'hidden',
                'name': csrfTokenName,
                'value': csrfTokenValue
            }));

            // Submit the form
            $('body').append(form);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response && response.result) {
                        alert_float('success', "<?= _l('appointment_created'); ?> " + "<?= _l('appointments_added_to_google_calendar'); ?>");
                    } else {
                        alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_error_adding_to_calendar'); ?>");
                    }
                    window.location.href = admin_url + 'appointly/appointments';
                },
                error: function() {
                    alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_error_adding_to_calendar'); ?>");
                    window.location.href = admin_url + 'appointly/appointments';
                }
            });

            form.remove();
        }

        /**
         * Reset time selection dropdown
         */
        function resetTimeSelection() {
            // Use the shared function from helpers.js
            window.resetTimeSelection();
        }

        // Make sure lead fields are initialized properly on page load
        $(document).ready(function() {
            // Run this after a slight delay to ensure all DOM is loaded
            setTimeout(function() {
                var currentType = $('#rel_type').val();
                console.log("Document ready, current type:", currentType);

                // Force AJAX search initialization for leads if current type is lead_related
                if (currentType === 'lead_related') {
                    if (typeof init_ajax_search === 'function') {
                        console.log("Reinitializing lead search on page load");
                        // Ensure rel_id_wrapper is visible
                        $('#rel_id_wrapper').removeClass('hide');

                        // Force initialize AJAX search for leads with the correct parameters
                        init_ajax_search('lead', $('#rel_id'), {
                            rel_id: $('#rel_id').val(),
                            type: 'leads'
                        });

                        // If lead already selected, force fetch data
                        var leadId = $('#rel_id').val();
                        if (leadId) {
                            console.log("Lead already selected, fetching data:", leadId);
                            setTimeout(function() {
                                appointlyFetchContactData(leadId, true);
                            }, 300);
                        }
                    }
                }

                $('.staffonly-hide').show();
                $('#service_field, #provider_field').removeClass('hidden');

                // Make sure external fields are enabled if external type
                if (currentType === 'external') {
                    $('#div_name, #div_email, #div_phone').removeClass('hidden');
                    $('#div_name input, #div_email input, #div_phone input').prop('disabled', false);
                }

                // Ensure rel_id event handler is properly bound for leads
                $('#rel_id').off('change').on('change', function() {
                    var leadId = $(this).val();
                    console.log("Lead selected:", leadId);

                    if (!leadId) {
                        appointlyResetContactFields();
                        return;
                    }

                    appointlyFetchContactData(leadId, true);
                });

                // Force refresh all selectpickers
                $('select').selectpicker('refresh');
            }, 500);
        });

        /**
         * Handle both Google and Outlook Calendar integrations
         */
        function handleBothCalendarIntegrations(appointmentId) {
            // Start with Outlook integration
            if (!isOutlookLoggedIn()) {
                // User needs to authenticate with Outlook first
                signInToOutlook();
                alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_outlook_auth_error'); ?>");
                window.location.href = admin_url + 'appointly/appointments';
                return;
            }

            // Create Outlook event first
            createOutlookEventForAppointment(appointmentId, function(outlookSuccess) {
                // After Outlook is done, handle Google Calendar
                handleGoogleIntegrationAfterOutlook(appointmentId, outlookSuccess);
            });
        }

        /**
         * Handle Google Calendar integration after Outlook is completed
         */
        function handleGoogleIntegrationAfterOutlook(appointmentId, outlookSuccess) {
            var form = $('<form>', {
                'method': 'POST',
                'action': admin_url + 'appointly/appointments/addEventToGoogleCalendar'
            });

            // Add appointment ID
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'appointment_id',
                'value': appointmentId
            }));

            // Add CSRF token
            form.append($('<input>', {
                'type': 'hidden',
                'name': csrfTokenName,
                'value': csrfTokenValue
            }));

            // Submit the form
            $('body').append(form);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    var googleSuccess = response && response.result;

                    // Show combined status message
                    if (outlookSuccess && googleSuccess) {
                        alert_float('success', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_added_to_outlook'); ?> " + "<?= _l('appointments_added_to_google_calendar'); ?>");
                    } else if (outlookSuccess && !googleSuccess) {
                        alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_added_to_outlook'); ?> " + "<?= _l('appointment_error_adding_to_calendar'); ?>");
                    } else if (!outlookSuccess && googleSuccess) {
                        alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_outlook_error'); ?> " + "<?= _l('appointments_added_to_google_calendar'); ?>");
                    } else {
                        alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_outlook_error'); ?> " + "<?= _l('appointment_error_adding_to_calendar'); ?>");
                    }

                    window.location.href = admin_url + 'appointly/appointments';
                },
                error: function() {
                    // Google failed, but show Outlook status
                    if (outlookSuccess) {
                        alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_added_to_outlook'); ?> " + "<?= _l('appointment_error_adding_to_calendar'); ?>");
                    } else {
                        alert_float('warning', "<?= _l('appointment_created'); ?> " + "<?= _l('appointment_outlook_error'); ?> " + "<?= _l('appointment_error_adding_to_calendar'); ?>");
                    }
                    window.location.href = admin_url + 'appointly/appointments';
                }
            });

            form.remove();
        }
    });
</script>
