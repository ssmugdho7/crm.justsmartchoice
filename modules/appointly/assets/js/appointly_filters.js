/**
 * Appointly Filters - Handle Google Calendar appointments filtering
 */
$(function() {
    // Listen for DataTables draw events to apply filtering
    $(document).on('draw.dt', '.table-appointments', function() {
        applyGoogleAppointmentFiltering();
    });

    // Check if current view is Google Calendar filtered
    function isGoogleCalendarView() {
        // Check both the input field and select dropdown
        var customView = $('input[name="custom_view"]').val();
        var googleFilter = $('select[name="google_calendar_synced"]').val() === '1';
        
        return customView === 'google_calendar_synced' || googleFilter;
    }
    
    // Check if we're on the "All" filter
    function isAllFilter() {
        // Check if any filter is active
        var hasActiveFilter = false;
        
        // Check all filter dropdowns - using new status filters
        $('select[name^="status_pending"], select[name^="status_cancelled"], select[name^="status_completed"], ' + 
          'select[name^="status_no-show"], select[name^="status_in-progress"], select[name^="internal"], select[name^="external"], select[name^="lead_related"], ' + 
          'select[name^="upcoming"], select[name^="missed"], ' +
          'select[name^="recurring"], select[name^="google_calendar_synced"]').each(function() {
            if ($(this).val() == '1') {
                hasActiveFilter = true;
                return false;
            }
        });
        
        // Check the custom view
        if ($('input[name="custom_view"]').val()) {
            hasActiveFilter = true;
        }
        
        // If no filter is active, we're on All
        return !hasActiveFilter;
    }
    
    // Apply Google Calendar appointment filtering
    function applyGoogleAppointmentFiltering() {
        // Only apply filtering if we're initialized
        if (!$('.table-appointments').length) return;
        
        // Check current filter state
        var isGoogleFilter = isGoogleCalendarView();
        var isAll = isAllFilter();
        
        // Process all rows in the appointments table
        $('.table-appointments tbody tr').each(function() {
            var $row = $(this);
            
            // Check if this row contains a Google Calendar icon
            var isGoogleAppointment = $row.find('.fa-brands.fa-google').length > 0;
            
            // Show Google appointments if we're on Google filter OR All filter
            if (isGoogleAppointment && !isGoogleFilter && !isAll) {
                // This is a Google appointment but we're not on Google filter or All
                // Hide this row
                $row.addClass('d-none');
            } else {
                // Show this row
                $row.removeClass('d-none');
            }
        });
    }
    
    // Initial application - use a larger timeout to ensure DataTables is ready
    setTimeout(applyGoogleAppointmentFiltering, 1000);
    
    // Listen for filter changes - dropdown select
    $('body').on('change', 'select[name="custom_view"], select[name^="google_calendar_synced"], ' +
                           'select[name^="status_pending"], select[name^="status_cancelled"], ' +
                           'select[name^="status_completed"], select[name^="status_no-show"], ' +
                           'select[name^="status_in-progress"], select[name^="internal"], ' +
                           'select[name^="external"], select[name^="lead_related"], ' +
                           'select[name^="upcoming"], select[name^="missed"], select[name^="recurring"]', function() {
        setTimeout(applyGoogleAppointmentFiltering, 500);
    });
    
    // Listen for filter changes - filter buttons 
    $('body').on('click', '.filter-group a, .dt-button', function() {
        setTimeout(applyGoogleAppointmentFiltering, 500);
    });
}); 
