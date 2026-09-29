$(function() {
    'use strict';

    // Initialize charts once DOM is ready
    initMonthlyStatsChart();
    initPopularServicesChart();

    // Initialize datepickers
    initDatepickers();
    
    // Initialize staff performance table
    initStaffPerformanceTable();

    // Handle form submission
    $('#date_filter_form').on('submit', function(e) {
        e.preventDefault();
        refreshStats();
    });
    
    // Get the correct moment format using AppointlyUtils
    var momentFormat = AppointlyUtils.convertPHPDateFormatToMoment(app.options.date_format);
    
    //  direct event listeners to date inputs to ensure data-sql-date is updated
    $('input[name="date_from"], input[name="date_to"]').on('change', function() {
        var $input = $(this);
        var dateVal = $input.val();
        
        if (dateVal && moment(dateVal, momentFormat).isValid()) {
            var sqlDate = moment(dateVal, momentFormat).format('YYYY-MM-DD');
            $input.attr('data-sql-date', sqlDate);
        }
    });
});

/**
 * Initialize all datepickers with proper SQL date handling
 */
function initDatepickers() {
    // Make sure necessary date inputs have data-sql-date attributes
    ensureSqlDateAttributes();
    
    // Unbind any existing datepicker to prevent duplicates
    $('.datepicker').datetimepicker('destroy');
    
    // Get the correct moment format using AppointlyUtils
    var momentFormat = AppointlyUtils.convertPHPDateFormatToMoment(app.options.date_format);
    
    // Initialize datepickers
    $('.datepicker').each(function() {
        var $input = $(this);
        $input.datetimepicker({
            format: app.options.date_format,
            timepicker: false,
            scrollInput: false,
            dayOfWeekStart: app.options.calendar_first_day || 0,
            onSelectDate: function(ct, $input) {
                // When date is selected, update the SQL date attribute
                var newDate = $input.val();
                if (newDate && moment(newDate, momentFormat).isValid()) {
                    var sqlDate = moment(newDate, momentFormat).format('YYYY-MM-DD');
                    $input.attr('data-sql-date', sqlDate);
                }
            }
        });
    });
}

/**
 * Ensure date inputs have SQL date attributes set
 */
function ensureSqlDateAttributes() {
    // Get the correct moment format using AppointlyUtils
    var momentFormat = AppointlyUtils.convertPHPDateFormatToMoment(app.options.date_format);
    
    $('input.datepicker, input[name="date_from"], input[name="date_to"]').each(function() {
        var $input = $(this);
        // Check if data-sql-date is missing or empty
        if (!$input.attr('data-sql-date')) {
            var dateVal = $input.val();
            if (dateVal && moment(dateVal, momentFormat).isValid()) {
                var sqlDate = moment(dateVal, momentFormat).format('YYYY-MM-DD');
                $input.attr('data-sql-date', sqlDate);
            }
        }
    });
}

/**
 * Initialize the monthly stats chart
 */
function initMonthlyStatsChart() {
    var ctx = document.getElementById('monthly_stats');
    if (!ctx) return;

    // Create chart
    window.monthlyStatsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyStatsLabels,
            datasets: [
                {
                    label: totalAppointmentsLabel,
                    data: monthlyStatsTotal,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1,
                    showLine: true
                },
                {
                    label: completedAppointmentsLabel,
                    data: monthlyStatsCompleted,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1,
                    showLine: true
                },
                {
                    label: cancelledAppointmentsLabel,
                    data: monthlyStatsCancelled,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1,
                    showLine: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            spanGaps: true
        }
    });
}

/**
 * Initialize the popular services chart
 */
function initPopularServicesChart() {
    var ctx = document.getElementById('popular_services');
    if (!ctx) return;

    // Create chart
    window.popularServicesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: popularServicesLabels,
            datasets: [
                {
                    data: popularServicesData,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                        '#5a5c69', '#6610f2', '#6f42c1', '#fd7e14', '#20c997'
                    ],
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            },
            cutout: '60%'
        }
    });
}

/**
 * Refresh statistics based on current date filters
 */
function refreshStats() {
    // Get date values from form inputs
    var dateFromInput = $('input[name="date_from"]').val();
    var dateToInput = $('input[name="date_to"]').val();

    // Validate input
    if (!dateFromInput || !dateToInput) {
        alert(appointlyLang.appointly_date_range_required);
        return;
    }
    
    // Get the app date format using AppointlyUtils
    var momentFormat = AppointlyUtils.convertPHPDateFormatToMoment(app.options.date_format);
    
    // Always recalculate SQL dates from the current input values
    var dateFrom, dateTo;
    
    try {
        // Try to parse the dates using moment
        dateFrom = moment(dateFromInput, momentFormat).format('YYYY-MM-DD');
        dateTo = moment(dateToInput, momentFormat).format('YYYY-MM-DD');
        
        // Check if either date is invalid
        if (dateFrom === 'Invalid date' || dateTo === 'Invalid date') {
            // Fallback to data-sql-date attributes if available
            dateFrom = $('input[name="date_from"]').attr('data-sql-date') || dateFrom;
            dateTo = $('input[name="date_to"]').attr('data-sql-date') || dateTo;
            
            if (dateFrom === 'Invalid date' || dateTo === 'Invalid date') {
                throw new Error('Could not parse dates correctly');
            }
        }
    } catch (e) {
        console.error('Error parsing dates:', e);
        alert(appointlyLang.appointly_date_range_required);
        return;
    }
    
    // Update the data-sql-date attributes
    $('input[name="date_from"]').attr('data-sql-date', dateFrom);
    $('input[name="date_to"]').attr('data-sql-date', dateTo);

    // Show loading indicator
    $('body').append('<div class="dt-loader"></div>');
    
    $.ajax({
        url: admin_url + 'appointly/reports/refresh_stats',
        type: 'POST',
        data: {
            date_from: dateFrom,
            date_to: dateTo
        },
        dataType: 'json',
        success: function(response) {
            $('.dt-loader').remove();
            
            if (response && response.success) {
                var data = response.data;
                
                // Update statistic counters
                $('#total_appointments').text(data.total_appointments);
                $('#completed_appointments').text(data.completed_appointments);
                $('#cancelled_appointments').text(data.cancelled_appointments);
                
                // Update staff performance table
                updateStaffPerformanceTable(data.staff_performance);
                
                // Update charts
                updateCharts(data);
            } else {
                alert(appointlyLang.appointly_error_refreshing_stats);
            }
        },
        error: function(xhr, status, error) {
            $('.dt-loader').remove();
            console.error('AJAX error:', error);
            alert(appointlyLang.appointly_error_refreshing_stats);
        }
    });
}

/**
 * Update charts with new data
 */
function updateCharts(data) {
    // Update monthly stats chart
    if (window.monthlyStatsChart && data.monthly_stats) {
        window.monthlyStatsChart.data.labels = data.monthly_stats.labels;
        window.monthlyStatsChart.data.datasets[0].data = data.monthly_stats.total;
        window.monthlyStatsChart.data.datasets[1].data = data.monthly_stats.completed;
        window.monthlyStatsChart.data.datasets[2].data = data.monthly_stats.cancelled;
        window.monthlyStatsChart.update();
    }
    
    // Update popular services chart
    if (window.popularServicesChart && data.popular_services) {
        window.popularServicesChart.data.labels = data.popular_services.labels;
        window.popularServicesChart.data.datasets[0].data = data.popular_services.counts;
        window.popularServicesChart.update();
    }
}

/**
 * Update staff performance table with new data
 */
function updateStaffPerformanceTable(staffPerformance) {
    var $tbody = $('#staff_performance_table tbody');
    $tbody.empty();

    if (staffPerformance && staffPerformance.length > 0) {
        staffPerformance.forEach(function(staff) {
            // Calculate completion rate if it doesn't exist
            var completionRate = staff.completion_rate !== undefined ? 
                staff.completion_rate : 
                (staff.completed !== undefined && staff.total_appointments > 0 ? 
                    Math.round((staff.completed / staff.total_appointments) * 100) : 0);
            
            // Use staff_name if available
            var staffName = staff.staff_name || ('Staff #' + staff.staff_id);
            
            $tbody.append(`
                <tr>
                    <td>
                        <a href="${admin_url}staff/profile/${staff.staff_id}">
                            ${staffName}
                        </a>
                    </td>
                    <td>${staff.total_appointments}</td>
                    <td>${staff.completed || 0}</td>
                    <td>${staff.cancelled || 0}</td>
                    <td>${completionRate}%</td>
                </tr>
            `);
        });
    } else {
        $tbody.append(`
            <tr>
                <td colspan="5" class="text-center">${appointlyLang.appointly_no_staff_performance_data || 'No data available for the selected period'}</td>
            </tr>
        `);
    }
}

/**
 * Initialize the staff performance DataTable
 */
function initStaffPerformanceTable() {
    if ($('.table-staff-performance').length === 0) {
        return;
    }
    
    // Get date values for the server-side requests
    var staffPerformanceServerParams = function() {
        return {
            date_from: $('input[name="date_from"]').attr('data-sql-date') || $('input[name="date_from"]').val(),
            date_to: $('input[name="date_to"]').attr('data-sql-date') || $('input[name="date_to"]').val()
        };
    };

    // Initialize DataTable with server-side processing
    var staffTable = initDataTable('.table-staff-performance', 
        admin_url + 'appointly/reports/table', 
        [], 
        [], 
        staffPerformanceServerParams, 
        [1, 'desc']
    );
    
    // Re-initialize table when date filters change
    $('#date_filter_form').on('submit', function() {
        staffTable.DataTable().ajax.reload();
        return false;
    });
}
