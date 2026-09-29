/**
 * Appointly Module - Shared helper functions
 */

/**
 * Get available time slots for a date/provider/service combination
 * @param {string} serviceId - The service ID
 * @param {string} providerId - The provider ID
 * @param {string} date - The date to check
 * @param {string} currentStartHour - Current appointment start hour (for existing appointments)
 * @param {string} timezone - Timezone to use
 * @param {string} appointmentId - Current appointment ID when editing
 */
function appointlyGetTimeSlots(serviceId, providerId, date, currentStartHour, timezone, appointmentId) {
    // Show loading and disable time select
    $('#slot_loading').removeClass('hide');
    $('#available_times').prop('disabled', true);

    // Hide any previous warnings
    $('.time-slot-unavailable-info').addClass('hide');

    // Determine if we're viewing the original appointment date (client-side check)
    var appointmentDate = $('input[name="appointment_date"]').data('current-date') || '';
    var currentDate = $('#appointment_date').val() || '';
    var clientSideIsOriginalDate = (appointmentDate && currentDate && appointmentDate === currentDate);

    var requestData = {
        service_id: serviceId,
        provider_id: providerId,
        date: date,
        timezone: timezone,
        appointment_id: appointmentId || 0,
        rel_type: $('#rel_type').val() || ''
    };

    // Add CSRF token
    requestData[csrfTokenName] = csrfTokenValue;

    $.ajax({
        url: admin_url + 'appointly/appointments/get_available_time_slots',
        type: 'POST',
        data: requestData,
        dataType: 'json',
        success: function(response) {
            $('#slot_loading').addClass('hide');

            var $timeSelect = $('#available_times');
            $timeSelect.html('<option value="">' + appointlyLang.dropdown_non_selected_tex + '</option>');

            // Track if we have available slots
            var hasAvailableSlots = false;
            var hasUnavailableSlots = false;    
            
            // Check if we're viewing the original date, either from server or our client-side check
            var isViewingOriginalDate = response.is_original_date || clientSideIsOriginalDate;

            if (response && response.success && response.time_slots && response.time_slots.length > 0) {
                // Process all time slots
                $.each(response.time_slots, function(i, slot) {
                    var isAvailable = slot.available !== false;
                    var isCurrentSlot = (currentStartHour && slot.value === currentStartHour);

                    // For editing, we always make the current slot available when on original date
                    if (isCurrentSlot && appointmentId && isViewingOriginalDate) {
                        isAvailable = true;
                    }

                    if (isAvailable) {            
                        hasAvailableSlots = true;
                        
                        // Create the option element
                        var $option = $('<option>', {
                            value: slot.value,
                            text: slot.text,
                            selected: isCurrentSlot && isViewingOriginalDate
                        });
                        
                        // Store end time if available
                        if (slot.end_time) {
                            $option.attr('data-end-time', slot.end_time);
                        }
                        
                        if (isCurrentSlot && isViewingOriginalDate) {
                            $option.addClass('current-slot');
                            if (typeof slot.text === 'string' && !slot.text.includes('(current)')) {
                                $option.text(slot.text + ' (current)');
                            }
                        }
                        
                        $timeSelect.append($option);
                    } else {
                        // This is an unavailable slot
                        hasUnavailableSlots = true;
                        var reason = slot.unavailable_reason ? ' - ' + slot.unavailable_reason : '';
                        
                        var $option = $('<option>', {
                            value: '', // Empty value so it can't be selected
                            disabled: true,
                            class: 'unavailable-time-slot',
                            text: slot.text + reason
                        });
                        
                        $timeSelect.append($option);
                    }
                });

                // Enable select if we have available slots
                if (hasAvailableSlots) {
                    $timeSelect.prop('disabled', false);
                } else {
                    $timeSelect.prop('disabled', true);
                }

                // Initialize selectpicker
                $timeSelect.selectpicker('refresh');

                // Always show the unavailable slots warning if there are any unavailable slots
                if (hasUnavailableSlots) {
                    // If the warning element doesn't exist, create it
                    if ($('.time-slot-unavailable-info').length === 0) {
                        // Create a separate warning message outside the dropdown
                        $timeSelect.closest('.form-group').after('<div class="time-slot-unavailable-info alert alert-danger mtop10"><i class="fa fa-info-circle"></i> ' + appointlyLang.appointment_unavailable_slots_shown + '</div>');
                    } else {
                        // Update existing warning with the correct message and make it visible
                        $('.time-slot-unavailable-info')
                            .removeClass('hide')
                            .removeClass('alert-warning')
                            .addClass('alert-danger')
                            .html('<i class="fa fa-info-circle"></i> ' + appointlyLang.appointment_unavailable_slots_shown);
                    }
                    
                    // Style unavailable slots
                    setTimeout(styleTimeSlotOptions, 200);
                } else {
                    $('.time-slot-unavailable-info').addClass('hide');
                }

                // If we have no available slots but have unavailable ones, show message
                if (!hasAvailableSlots && hasUnavailableSlots) {
                    $timeSelect.html('<option value="">' + appointlyLang.appointment_no_slots_available + '</option>');
                    $timeSelect.prop('disabled', true).selectpicker('refresh');
                    
                    // Show a warning message outside the dropdown
                    if ($('.time-slot-unavailable-info').length === 0) {
                        $timeSelect.closest('.form-group').after('<div class="time-slot-unavailable-info alert alert-warning mtop10"><i class="fa fa-info-circle"></i> ' + appointlyLang.appointment_no_slots_available + '</div>');
                    } else {
                        $('.time-slot-unavailable-info')
                            .removeClass('hide')
                            .removeClass('alert-danger')
                            .addClass('alert-warning')
                            .html('<i class="fa fa-info-circle"></i> ' + appointlyLang.appointment_no_slots_available);
                    }
                }
            } else {
                // No slots available
                $timeSelect.html('<option value="">' + appointlyLang.appointment_no_slots_available + '</option>');
                $timeSelect.prop('disabled', true).selectpicker('refresh');
                
                // Show no slots available message outside the dropdown
                if ($('.time-slot-unavailable-info').length === 0) {
                    $timeSelect.closest('.form-group').after('<div class="time-slot-unavailable-info alert alert-warning mtop10"><i class="fa fa-info-circle"></i> ' + appointlyLang.appointment_no_slots_available + '</div>');
                } else {
                    $('.time-slot-unavailable-info')
                        .removeClass('hide')
                        .removeClass('alert-danger')
                        .addClass('alert-warning')
                        .html('<i class="fa fa-info-circle"></i> ' + appointlyLang.appointment_no_slots_available);
                }

                if (response && response.message) {
                    alert_float('warning', response.message);
                }
            }

            // After initialization, style the selectpicker items
            setTimeout(styleTimeSlotOptions, 200);
        },
        error: function(xhr, status, error) {
            $('#slot_loading').addClass('hide');

            var $timeSelect = $('#available_times');
            $timeSelect.html('<option value="">' + appointlyLang.appointment_error_loading_slots + '</option>');
            $timeSelect.prop('disabled', true).selectpicker('refresh');
            alert_float('danger', appointlyLang.appointment_error_loading_slots);
        }
    });
}

/**
 * Style time slot options in the selectpicker dropdown
 */
function styleTimeSlotOptions() {
    // Apply styling to unavailable time slots in the dropdown
    $('.unavailable-time-slot').parents('li').css({
        'color': '#ff6666',
        'background-color': '#ffeeee',
        'text-decoration': 'line-through',
        'font-style': 'italic'
    });
    
    // Apply styling to current slot in the dropdown
    $('.current-slot').parents('li').css({
        'font-weight': 'bold',
        'background-color': '#e8f0fe'
    });
    
    // Show warning message if we have unavailable slots
    if ($('.unavailable-time-slot').length > 0) {
        $('.alert.alert-warning.time-slot-unavailable-info').removeClass('hide');
    }
}

/**
 * Handle time slot selection change
 * @param {jQuery} $timeSelect - The time select element
 */
function handleTimeSlotChange($timeSelect) {
    var selectedTime = $timeSelect.val();
    
    if (selectedTime) {
        // Store the start hour in the hidden input
        $('input[name="start_hour"]').val(selectedTime);
        
        // Get the end time if it's stored in data attributes
        var endTime = $timeSelect.find('option:selected').data('end-time');
        if (endTime) {
            $('input[name="end_hour"]').val(endTime);
        } else {
            // Calculate end time based on duration if we have it
            var duration = $('#appointment_duration').val();
            if (duration) {
                var startTimestamp = new Date('1970-01-01T' + selectedTime + ':00Z').getTime();
                var endTimestamp = startTimestamp + (duration * 60 * 1000);
                var endTimeStr = new Date(endTimestamp).toISOString().substr(11, 5);
                $('input[name="end_hour"]').val(endTimeStr);
            }
        }
    } else {
        $('input[name="start_hour"]').val('');
        $('input[name="end_hour"]').val('');
    }
}

/**
 * Load providers for a service
 * @param {string} serviceId - The service ID
 */
function appointlyLoadProviders(serviceId) {
    if (!serviceId) return;
    
    $('#provider_loading').removeClass('hide');
    var providerSelect = $('#provider_id');
    providerSelect.prop('disabled', true);

    var data = {
        service_id: serviceId
    };
    
    // Add CSRF token
    data[csrfTokenName] = csrfTokenValue;

    $.ajax({
        url: admin_url + 'appointly/appointments/get_providers_by_service',
        type: 'POST',
        data: data,
        success: function(response) {
            $('#provider_loading').addClass('hide');
            providerSelect.html('<option value="">' + appointlyLang.appointment_select_provider + '</option>');

            if (response && response.length > 0) {
                var primaryProvider = null;

                $.each(response, function(i, provider) {
                    var selected = '';
                    if (provider.is_primary == 1) {
                        selected = 'selected';
                        primaryProvider = provider.id;
                    }
                    providerSelect.append('<option value="' + provider.id + '" ' + selected + '>' +
                        provider.name + '</option>');
                });

                providerSelect.prop('disabled', false).selectpicker('refresh');

                // If there's a primary provider selected, trigger change to load dates
                if (primaryProvider) {
                    providerSelect.trigger('change');
                }
            } else {
                providerSelect.selectpicker('refresh');
            }
        }
    });
}

/**
 * Format date string for API calls
 * @param {string} dateStr - Date string in local format
 * @returns {string} Date string in YYYY-MM-DD format
 */
function appointlyFormatDate(dateStr) {
    if (!dateStr) return '';
    
    // Try to parse the date using the app's date format
    var parsedDate = moment(dateStr, app.options.date_format.toUpperCase());
    
    // Check if parsing was successful
    if (!parsedDate.isValid()) {
        // Try common date formats as fallback
        var formats = ['DD-MM-YYYY', 'MM-DD-YYYY', 'YYYY-MM-DD', 'DD/MM/YYYY', 'MM/DD/YYYY', 'DD.MM.YYYY', 'MM.DD.YYYY'];
        
        for (var i = 0; i < formats.length; i++) {
            parsedDate = moment(dateStr, formats[i]);
            if (parsedDate.isValid()) {
                break;
            }
        }
    }
    
    // If we still don't have a valid date, return original string
    if (!parsedDate.isValid()) {
        return dateStr;
    }
    
    // Return the date in YYYY-MM-DD format
    return parsedDate.format('YYYY-MM-DD');
}

/**
 * Reset time selection dropdown
 * @param {string} message - Optional custom message to display
 */
function resetTimeSelection(message) {
    var timesSelect = $('#available_times');
    var displayMessage = message || appointlyLang.appointment_date_required;
    timesSelect.html('<option value="">' + displayMessage + '</option>');
    timesSelect.prop('disabled', true).selectpicker('refresh');
    
    // Hide any warnings
    $('.time-slot-unavailable-info').addClass('hide');
}

/**
 * Reset time selection with service/provider required message
 */
function resetTimeSelectionRequireServiceProvider() {
    var message = appointlyLang.appointment_select_service_provider_first || 'Please select service and provider first';
    resetTimeSelection(message);
    
    // Focus on service field to guide user
    setTimeout(function() {
        var $serviceField = $('#service_id');
        if ($serviceField.length && $serviceField.is(':visible')) {
            $serviceField.focus();
            // If using selectpicker, open the dropdown
            if ($serviceField.data('selectpicker')) {
                $serviceField.selectpicker('toggle');
            }
        }
    }, 100);
}

/**
 * Reset date and time selections
 */
function resetDateAndTimeSelections() {
    $('#appointment_date').val('');
    resetTimeSelection();
}

/**
 * Handle appointment type change
 * @param {string} relType - The relationship type (internal, external, lead_related, internal_staff)
 * @param {boolean} isInit - Whether this is during initialization
 */
function appointlyHandleTypeChange(relType, isInit) {
    // Clear fields when switching types (but not during initialization)
    if (!isInit) {
        $('#contact_id, #rel_id').val('').selectpicker('refresh');
            $('#name, #email, #phone').val('');
        }

    // Handle each appointment type
    switch (relType) {
        case 'internal_staff':
            // Staff-only appointments
            $('.staffonly-hide').hide();
            $('#service_id, #provider_id').closest('.form-group').hide();
            $('#service_id, #provider_id').val('').prop('disabled', true).selectpicker('refresh');
            $('#duration_wrapper, .staff-only-fields').removeClass('hidden');
            
            // Hide all contact-related fields
            $('#select_contacts, #rel_id_wrapper, #div_name, #div_email, #div_phone').addClass('hidden');
            $('#contact_id, #rel_id').val('').selectpicker('refresh');
            $('#name, #email, #phone').val('');
            
            // Clear any service/provider validation messages and reload time slots if date is selected
            var selectedDate = $('#appointment_date').val();
            if (selectedDate) {
                // For staff-only, immediately load time slots without validation
                $('#slot_loading').removeClass('hide');
                var providerId = $('input[name="created_by"]').val() || '1';
                var formattedDate = appointlyFormatDate ? appointlyFormatDate(selectedDate) : selectedDate;
                appointlyGetTimeSlots(0, providerId, formattedDate, '', $('#timezone').val() || '', 0);
            } else {
                // Clear validation message and reset to normal state
                resetTimeSelection();
            }
            break;

        case 'internal':
            // Internal contact appointments
            $('.staffonly-hide').show();
            $('#service_id, #provider_id').closest('.form-group').show();
            $('#service_id, #provider_id').prop('disabled', false).selectpicker('refresh');
            $('#related_field_wrapper, #select_contacts').removeClass('hidden');
            
            // Hide other type-specific fields
            $('#rel_id_wrapper, #div_name, #div_email, #div_phone, #duration_wrapper').addClass('hidden');
            $('#rel_id').val('').selectpicker('refresh');
            $('#name, #email, #phone').val('');

            // Don't clear contact_id during initialization to preserve pre-selected values
            if (!isInit) {
                $('#contact_id').val('').selectpicker('refresh');
            }
            
            // Clear any previous validation messages
            resetTimeSelection();
            break;

        case 'lead_related':
            // Lead-related appointments
            $('.staffonly-hide').show();
            $('#service_id, #provider_id').closest('.form-group').show();
            $('#service_id, #provider_id').prop('disabled', false).selectpicker('refresh');
            $('#related_field_wrapper, #rel_id_wrapper').removeClass('hidden');
            $('#rel_id_select').show();
            $('#rel_id').prop('disabled', false);
            initLeadSearch();
            
            // Hide other type-specific fields
            $('#select_contacts, #div_name, #div_email, #div_phone, #duration_wrapper').addClass('hidden');
            $('#contact_id').val('').selectpicker('refresh');
            $('#name, #email, #phone').val('');
            
            // Clear any previous validation messages
            resetTimeSelection();
            break;

        case 'external':
            // External appointments
            $('.staffonly-hide').show();
            $('#service_id, #provider_id').closest('.form-group').show();
            $('#service_id, #provider_id').prop('disabled', false).selectpicker('refresh');
            $('#related_field_wrapper, #div_name, #div_email, #div_phone').removeClass('hidden');
            $('#div_name input, #div_email input, #div_phone input').prop('disabled', false).prop('readonly', false);
            
            // Hide other type-specific fields
            $('#select_contacts, #rel_id_wrapper, #duration_wrapper').addClass('hidden');
            $('#contact_id, #rel_id').val('').selectpicker('refresh');
            
            // Clear any previous validation messages
            resetTimeSelection();
            break;

        default:
            // Fallback for unknown types
            $('.staffonly-hide').show();
            $('#service_id, #provider_id').closest('.form-group').show();
            $('#service_id, #provider_id').prop('disabled', false).selectpicker('refresh');
            $('#related_field_wrapper').removeClass('hidden');
            break;
    }

    // Refresh all selectpickers
    $('select').selectpicker('refresh');

    // Update the relation type selector if not initializing
    if (!isInit) {
        $('#rel_type').val(relType).selectpicker('refresh');
    }
}

/**
 * Initialize lead search functionality
 */
function initLeadSearch() {
    setTimeout(function() {
        if (typeof init_ajax_search === 'function') {
            // Force destroy any existing search first
            if ($('#rel_id').data('search')) {
                $('#rel_id').data('search').destroy();
            }

            // Initialize lead search
            init_ajax_search('lead', $('#rel_id'), {
                rel_id: $('#rel_id').val(),
                type: 'leads'
            });

            $('#rel_id_wrapper').removeClass('hide');
            $('#rel_id_select').show();
            $('#rel_id').selectpicker('refresh');
        }
    }, 300);
}

/**
 * Fetch contact data for a lead or contact
 * @param {number} contactId - The contact ID
 * @param {boolean} isLead - Whether this is a lead
 */
function appointlyFetchContactData(contactId, isLead) {
    if (!contactId) return;

    // Show loading indicator
    $('#provider_loading').removeClass('hide');

    var data = {
        contact_id: contactId,
        lead: isLead ? 1 : 0
    };
    
    // Add CSRF token
    data[csrfTokenName] = csrfTokenValue;

    $.ajax({
        url: admin_url + 'appointly/appointments/fetch_contact_data',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            $('#provider_loading').addClass('hide');

            if (response) {
                // Make fields visible and read-only (not disabled!)
                $('#div_name, #div_email, #div_phone').removeClass('hidden');
                $('#div_name input, #div_email input, #div_phone input').prop('disabled', false).prop('readonly', true);

                // Get contact details
                var fullName = '';
                if (typeof response.firstname !== 'undefined') {
                    fullName = response.firstname + ' ' + response.lastname;
                } else if (typeof response.name !== 'undefined') {
                    fullName = response.name;
                }

                var email = response.email || '';
                var phone = response.phonenumber || response.phone || '';
                var address = response.address || '';

                // Set field values
                $('#name').val(fullName);
                $('#email').val(email);
                $('#phone').val(phone);

                // Set location/address if available
                if (address && $('#location').val() === '') {
                    $('#location').val(address);
                }
            } else {
                // Reset contact fields if no data
                appointlyResetContactFields();
            }
        }
    });
}

/**
 * Reset contact fields (name, email, phone)
 */
function appointlyResetContactFields() {
    // Hide all fields
    $('#div_name, #div_email, #div_phone').addClass('hidden');

    // Reset values
    $('#name, #email, #phone').val('');
}

/**
 * Format time for display based on app time format
 * @param {string} timeValue - Time in 24-hour format (HH:MM)
 * @param {string} endTimeValue - Optional end time for range display
 * @returns {string} Formatted time string
 */
function appointlyFormatTimeDisplay(timeValue, endTimeValue) {
    if (!timeValue) return '';
    
    var formattedTime = '';
    
    // Check if we should use 24-hour format - default to 12-hour (AM/PM) format
    var use24Hour = false;
    if (app && app.options && app.options.time_format == 24) {
        use24Hour = true;
    }

    if (!use24Hour) {
        // 12-hour format with AM/PM (default)
        var timeParts = timeValue.split(':');
        var hour = parseInt(timeParts[0], 10);
        var minute = timeParts[1];
        var period = hour >= 12 ? 'PM' : 'AM';

        // Convert hour to 12-hour format
        hour = hour % 12;
        hour = hour ? hour : 12; // Convert 0 to 12
        
        formattedTime = hour + ':' + minute + ' ' + period;
        
        // Add end time if provided
        if (endTimeValue) {
            var endTimeParts = endTimeValue.split(':');
            var endHour = parseInt(endTimeParts[0], 10);
            var endMinute = endTimeParts[1];
            var endPeriod = endHour >= 12 ? 'PM' : 'AM';
            
            endHour = endHour % 12;
            endHour = endHour ? endHour : 12;
            
            formattedTime += ' - ' + endHour + ':' + endMinute + ' ' + endPeriod;
        }
    } else {
        // 24-hour format
        formattedTime = timeValue;
        
        // Add end time if provided
        if (endTimeValue) {
            formattedTime += ' - ' + endTimeValue;
        }
    }
    
    return formattedTime;
}

/**
 * Configure date picker with past dates handling and blocked days
 * @param {Array} blockedDays - Array of blocked days in YYYY-MM-DD format
 * @param {boolean} disablePastDates - Whether to show past dates
 * @param {string} relType - Appointment relationship type
 * @param {Object} providerSchedule - Optional provider schedule data
 * @return {Object} The date picker options
 */
function appointlyConfigureDatePicker(blockedDays, disablePastDates, relType, providerSchedule) {
    // Default options
    var options = {
        dayOfWeekStart: app.options.calendar_first_day,
        format: app.options.date_format,
        timepicker: false,
        scrollInput: false,
        validateOnBlur: false
    };
    
    // Set minDate based on disablePastDates setting
    if (disablePastDates) {
        options.minDate = 0;
    }
    
    // Add beforeShowDay function to handle blocked days AND company schedule
    options.beforeShowDay = function(date) {
        // If staff-only, allow all days (staff meetings can be scheduled anytime)
        if (relType === 'internal_staff') {
            return [""];
        }
        
        // Format date to YYYY-MM-DD for comparison
        var formattedDate = date.getFullYear() + '-' +
            padZero(date.getMonth() + 1) + '-' +
            padZero(date.getDate());
        
        // Check if the date is in the blocked days array
        if (blockedDays && blockedDays.indexOf(formattedDate) !== -1) {
            return ["blocked-date"];
        }
        
        // Check provider schedule first if available
        if (providerSchedule) {
            var dayOfWeek = date.getDay(); // 0 = Sunday, 1 = Monday, etc.
            
            // Convert JS day (0=Sunday) to provider schedule day number (1=Monday, 7=Sunday)
            var providerDayNumber;
            if (dayOfWeek === 0) {
                providerDayNumber = 7; // Sunday
            } else {
                providerDayNumber = dayOfWeek; // Monday-Saturday
            }
            
            // Check if provider is available on this day
            var providerDaySchedule = providerSchedule[providerDayNumber];
            if (providerDaySchedule && !providerDaySchedule.enabled) {
                return ["provider-unavailable"];
            }
        }
        
        // Check company schedule for this day of week
        var dayOfWeek = date.getDay(); // 0 = Sunday, 1 = Monday, etc.
        var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        var dayName = dayNames[dayOfWeek];
        
        // Check if we have company schedule data available
        if (typeof window.appointlyCompanySchedule !== 'undefined' && window.appointlyCompanySchedule) {
            var daySchedule = window.appointlyCompanySchedule[dayName];
            if (daySchedule && !daySchedule.is_enabled) {
                return ["company-closed"];
            }
        }
        
        return [""];
    };
    
    // Add onGenerate event to style blocked dates and company closed days
    options.onGenerate = function(ct) {
        setTimeout(function() {
            // Add proper tooltips to all date cells
            $('.xdsoft_date').each(function() {
                var $this = $(this);
                var dateData = $this.data();

                if (dateData) {
                    // Format as YYYY-MM-DD for comparison
                    var month = parseInt(dateData.month) + 1; // months are 0-based
                    var dateStr = dateData.year + '-' +
                        ('0' + month).slice(-2) + '-' +
                        ('0' + dateData.date).slice(-2);

                    // Create a proper Date object for this cell
                    var jsDate = new Date(dateData.year, dateData.month, dateData.date);
                    var dayOfWeek = jsDate.getDay(); // 0 = Sunday, 1 = Monday, etc.
                    
                    // Check if this is a past date
                    var isPastDate = jsDate < new Date().setHours(0, 0, 0, 0);
                    
                    // Check if date is blocked
                    var isBlocked = blockedDays && blockedDays.indexOf(dateStr) !== -1;
                    
                    // Check if provider is unavailable on this day
                    var isProviderUnavailable = false;
                    if (providerSchedule) {
                        var providerDayNumber = dayOfWeek === 0 ? 7 : dayOfWeek; // Convert to 1-7 format
                        var providerDaySchedule = providerSchedule[providerDayNumber];
                        isProviderUnavailable = providerDaySchedule && !providerDaySchedule.enabled;
                    }
                    
                    // Check if company is closed on this day
                    var isCompanyClosed = false;
                    if (typeof window.appointlyCompanySchedule !== 'undefined' && window.appointlyCompanySchedule) {
                        var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        var dayName = dayNames[dayOfWeek];
                        var daySchedule = window.appointlyCompanySchedule[dayName];
                        isCompanyClosed = daySchedule && !daySchedule.is_enabled;
                    }

                    if (isBlocked) {
                        $this.attr('title', appointlyLang.appointment_blocked_days || 'Company Holiday/Blocked Date');
                        $this.addClass('xdsoft_disabled blocked-date');
                        $this.css('cursor', 'not-allowed');
                        // Add the lock icon
                        if (!$this.find('.blocked-icon').length) {
                            $this.append('<i class="fa fa-lock blocked-icon"></i>');
                    }
                    } else if (isProviderUnavailable && !isPastDate) {
                        $this.addClass('xdsoft_disabled provider-unavailable');
                        $this.attr('title', appointlyLang.appointment_provider_unavailable || 'Provider does not work on this day');
                        // $this.css('cursor', 'not-allowed');
                    } else if (isCompanyClosed) {
                        $this.addClass('xdsoft_disabled company-closed');
                        $this.attr('title', 'Company is closed on this day');
                        $this.css('cursor', 'not-allowed');
                    } else if (isPastDate) {
                        $this.attr('title', 'Past date');
                        $this.addClass('xdsoft_disabled');
                        // $this.css('cursor', 'not-allowed');
                        $this.removeClass('xdsoft_current');
                    } else if (!$this.attr('title')) {
                        $this.attr('title', appointlyLang.appointment_available_days || 'Available for booking');
                    }
                }
            });
        }, 100);
    };
    
    return options;
}

// Helper function to pad numbers with leading zeros
function padZero(num) {
    return (num < 10 ? '0' : '') + num;
}

/**
 * Toggle visibility of optional field in forms
 * @param {string} fieldId - The ID of the field container to show
 * @param {HTMLElement} linkElement - The link element that was clicked
 */
function appointlyToggleOptionalField(fieldId, linkElement) {
    // Show the field container
    document.getElementById(fieldId).style.display = 'block';
    
    // Hide the link that was clicked
    linkElement.style.display = 'none';
    
    // Focus on the first input/select/textarea in the revealed container
    const firstInput = document.getElementById(fieldId).querySelector('input, select, textarea');
    if (firstInput) {
        firstInput.focus();
    }
    
    return false;
} 
