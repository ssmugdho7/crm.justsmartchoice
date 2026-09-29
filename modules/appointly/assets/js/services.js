function delete_service(id) {
    if (confirm(app.lang.confirm_action_prompt)) {
        $.post(admin_url + 'appointly/services/delete/' + id)
            .done(function(response) {
                try {
                    response = JSON.parse(response);
                    if (response.success) {
                        alert_float('success', response.message);
                        $('.table-services').DataTable().ajax.reload(null, false);
                    } else {
                        alert_float('warning', response.message || "An error occurred while processing your request");
                    }
                } catch (e) {
                    alert_float('danger', "An error occurred while processing your request");
                }
            })
            .fail(function(error) {
                alert_float('danger', "An error occurred while processing your request");
            });
    }
}

// Initialize services table and form handling
$(function () {
    
    var ServiceServerParams = {};
    initDataTable('.table-services', admin_url + 'appointly/services/table', [5], [5], ServiceServerParams, [0, 'asc']);

    // Set active menu state
    $('li.menu-item-appointly').addClass('active');
    $('li.menu-item-appointly > ul').addClass('in');
    $('li.sub-menu-item-appointly-services').addClass('active');

    // Initialize selectpicker
    if ($.fn.selectpicker) {
        $('.selectpicker').selectpicker({
            showSubtext: true
        });
    }

    // Initialize color picker
    if (typeof($.fn.colorpicker) === 'function') {
        $('input[name="color"]').colorpicker();
    }

    // Handle buffer time help text
    $('[data-toggle="tooltip"]').tooltip();

    // Add buffer explanation in the form
    $('#buffer_before, #buffer_after').on('change input', function() {
        var id = $(this).attr('id');
        var value = parseInt($(this).val()) || 0;
        
        if (value > 0) {
            var helpText = $('#' + id + '_help');
            var originalText = helpText.data('original-text');
            
            if (!originalText) {
                // Store original text the first time
                originalText = helpText.html().split('(')[0].trim();
                helpText.data('original-text', originalText);
            }
            
            helpText.html(originalText + ' (' + value + ' ' + appointlyLang.durationMinutes + ')');
        } else {
            var helpText = $('#' + id + '_help');
            var originalText = helpText.data('original-text');
            
            if (originalText) {
                helpText.html(originalText);
            }
        }
    });

    // Store the original help text on page load
    $('#buffer_before_help, #buffer_after_help').each(function() {
        var originalText = $(this).html().split('(')[0].trim();
        $(this).data('original-text', originalText);
    });

    // Handle staff selection change
    $('#staff_members').on('change', function() {
        var selectedStaff = $(this).val();
        updatePrimaryProviderDropdown(selectedStaff);
    });

    // Intercept onoffswitch clicks for services in use
    $(document).on('click', '.onoffswitch-checkbox[data-service-in-use="1"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Show warning message
        alert_float('warning', appointlyLang.appointly_service_in_use_warning);
        
        // Ensure the checkbox stays in its current state
        return false;
    });

    // Handle active checkbox for services in use on the edit form
    $('#active').on('change', function() {
        var $checkbox = $(this);
        var isServiceInUse = $checkbox.prop('disabled'); // If disabled, service is in use
        
        // If service is in use and checkbox is somehow enabled and unchecked
        if (isServiceInUse && !$checkbox.prop('checked')) {
            // Prevent the change and show warning
            $checkbox.prop('checked', true);
            alert_float('warning', appointlyLang.appointly_service_in_use_warning);
            return false;
        }
    });

    // Initialize form validation using Perfex CRM's appFormValidator
    $('#service-form').appFormValidator({
        rules: {
            name: 'required',
            'staff_members[]': 'required',
            price: {
                required: true,
                number: true,
                min: 0
            },
            duration: {
                required: true,
                number: true,
                min: 15
            }
        },
        messages: {
            name: appointlyLang.serviceNameRequired,
            'staff_members[]': appointlyLang.staffMembersRequired,
            price: {
                required: appointlyLang.priceRequired,
                number: appointlyLang.priceInvalid,
                min: appointlyLang.priceInvalid
            },
            duration: {
                required: appointlyLang.durationRequired,
                number: appointlyLang.durationInvalid,
                min: appointlyLang.durationInvalid
            }
        },
        submitHandler: function(form) {
            var $form = $(form);
            var $submitBtn = $form.find('button[type="submit"]');

            // Prevent double submission
            if ($submitBtn.prop('disabled')) {
                return false;
            }
            
            // Store original button text
            var originalBtnText = $submitBtn.html();
            
            // Disable submit button and show loading
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + appointlyLang.submit);

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function(response) {
                    
                    if (response.success) {
                        if (response.warning) {
                            // Show warning message for services in use but still redirect
                            alert_float('warning', response.message);
                        } else {
                            // Show success message
                            alert_float('success', response.message);
                        }
                        
                        // Redirect to services list after a short delay
                        setTimeout(function() {
                            window.location.href = admin_url + 'appointly/services';
                        }, 1500);
                    } else {
                        // Show error message and stay on current page
                        alert_float('danger', response.message || 'An error occurred while processing your request');
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                        
                        // Scroll to top to ensure user sees the error message
                        $('html, body').animate({ scrollTop: 0 }, 300);
                    }
                }
            });
            
            return false; // Prevent default form submission
        }
    });

    // On service deletion
    $('body').on('click', '.delete-service', function(e) {
        e.preventDefault();
        var serviceId = $(this).data('id');
        if (confirm(app.lang.confirm_action_prompt)) {
            $.post(admin_url + 'appointly/services/delete/' + serviceId)
                .done(function(response) {
                    try {
                        response = JSON.parse(response);
                        if (response.success) {
                            alert_float('success', response.message);
                            $('.table-services').DataTable().ajax.reload(null, false);
                        } else {
                            alert_float('warning', response.message || "An error occurred while processing your request");
                        }
                    } catch (e) {
                        alert_float('danger', "An error occurred while processing your request");
                    }
                })
                .fail(function(error) {
                    alert_float('danger', "An error occurred while processing your request");
                });
        }
    });

    // Initialize primary provider dropdown when the page loads
    var initialSelectedStaff = $('#staff_members').val();
    if (initialSelectedStaff) {
        updatePrimaryProviderDropdown(initialSelectedStaff);
    }
});

// Function to update primary provider dropdown based on selected staff
function updatePrimaryProviderDropdown(selectedStaff) {
    var primaryProviderSelect = $('#primary_provider');
    var primaryProviderWrapper = $('#primary_provider_wrapper');
    
    // Clear current options
    primaryProviderSelect.find('option').remove();
    
    if (!selectedStaff || selectedStaff.length === 0) {
        // Hide primary provider selection if no staff selected
        primaryProviderWrapper.addClass('hide');
        return;
    }
    
    // Show primary provider selection
    primaryProviderWrapper.removeClass('hide');
    
    // Get current primary provider value
    var currentPrimaryProvider = primaryProviderSelect.data('current');
    
    // Add default option (only if there's more than one staff member)
    if (selectedStaff.length > 1) {
        primaryProviderSelect.append('<option value="">' + appointlyLang.selectProvider + '</option>');
    }
    
    // Add options for selected staff
    $.each(selectedStaff, function(i, staffId) {
        var staffOption = $('#staff_members option[value="' + staffId + '"]');
        if (staffOption.length) {
            var staffName = staffOption.text();
            var selected = '';
            
            // Select the staff member if:
            // 1. It matches the current primary provider, or
            // 2. There's no current primary provider and this is the first staff member, or
            // 3. There's only one staff member selected
            if (staffId == currentPrimaryProvider || 
                (!currentPrimaryProvider && i === 0) || 
                selectedStaff.length === 1) {
                selected = 'selected';
            }
            
            primaryProviderSelect.append('<option value="' + staffId + '" ' + selected + '>' + staffName + '</option>');
        }
    });
    
    // Refresh selectpicker if it's initialized
    if (typeof($.fn.selectpicker) === 'function') {
        primaryProviderSelect.selectpicker('refresh');
    }
}
