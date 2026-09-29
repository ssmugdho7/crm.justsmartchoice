<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
    /* Keep existing styles */
    .date-range-presets a {
        padding: 5px 10px;
        margin-right: 5px;
        display: inline-block;
        background: #f9f9f9;
        border-radius: 3px;
        cursor: pointer;
    }

    .date-range-presets a.active {
        background: #1a73e8;
        color: white;
    }

    .stats-card {
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
        padding: 20px;
        margin-bottom: 20px;
        text-align: center;
    }

    .stats-card h3 {
        margin-top: 0;
        color: #333;
    }

    .stats-card .number {
        font-size: 32px;
        font-weight: bold;
        margin: 10px 0;
    }

    .stats-card.total {
        border-top: 3px solid #007bff;
    }

    .stats-card.completed {
        border-top: 3px solid #28a745;
    }

    .stats-card.cancelled {
        border-top: 3px solid #dc3545;
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 30px;
    }

    /* Empty state styling */
    .dataTables_empty {
        text-align: center !important;
        padding: 30px 0 !important;
        font-size: 16px !important;
        color: #777 !important;
        background-color: #f9f9f9 !important;
    }

    .no-data-message {
        display: block;
        width: 100%;
        text-align: center;
        padding: 15px;
        font-size: 16px;
        color: #666;
    }
</style>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('appointly_analytics_dashboard'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                            <div class="panel_s">
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php echo form_open('javascript:void(0);', ['id' => 'date_filter_form']); ?>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <?php
                                                    $date_from_sql = isset($date_from) ? $date_from : date('Y-m-01');
                                                    $date_from_display = _d($date_from_sql);
                                                    ?>
                                                    <div class="form-group" app-field-wrapper="date_from">
                                                        <label for="date_from" class="control-label"><?= _l('appointly_period_from'); ?></label>
                                                        <div class="input-group date">
                                                            <input type="text" id="date_from" name="date_from" class="form-control datepicker" value="<?= $date_from_display; ?>" data-sql-date="<?= $date_from_sql; ?>" autocomplete="off">
                                                            <div class="input-group-addon">
                                                                <i class="fa-regular fa-calendar calendar-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <?php
                                                    $date_to_sql = isset($date_to) ? $date_to : date('Y-m-t');
                                                    $date_to_display = _d($date_to_sql);
                                                    ?>
                                                    <div class="form-group" app-field-wrapper="date_to">
                                                        <label for="date_to" class="control-label"><?= _l('appointly_period_to'); ?></label>
                                                        <div class="input-group date">
                                                            <input type="text" id="date_to" name="date_to" class="form-control datepicker" value="<?= $date_to_display; ?>" data-sql-date="<?= $date_to_sql; ?>" autocomplete="off">
                                                            <div class="input-group-addon">
                                                                <i class="fa-regular fa-calendar calendar-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex justify-end tw-align-baseline items-center mtop25 ">
                                                    <div>
                                                        <div class="btn-group pull-right">
                                                            <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa fa-calendar mr-2"></i> <?= _l('appointly_date_range'); ?> <span class="caret"></span>
                                                            </a>
                                                            <ul class="dropdown-menu dropdown-menu-right">
                                                                <li>
                                                                    <a href="#" onclick="setDateRange('today'); return false;">
                                                                        <?= _l('appointly_today'); ?>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="#" onclick="setDateRange('this_week'); return false;">
                                                                        <?= _l('appointly_this_week'); ?>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="#" onclick="setDateRange('this_month'); return false;">
                                                                        <?= _l('appointly_this_month'); ?>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="#" onclick="setDateRange('this_year'); return false;">
                                                                        <?= _l('appointly_this_year'); ?>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="#" onclick="setDateRange('last_30_days'); return false;">
                                                                        <?= _l('appointly_last_30_days'); ?>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="#" onclick="setDateRange('last_month'); return false;">
                                                                        <?= _l('appointly_last_month'); ?>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="#" onclick="setDateRange('last_year'); return false;">
                                                                        <?= _l('appointly_last_year'); ?>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary apply-filter-btn"><?= _l('appointly_apply'); ?></button>
                                                </div>
                                            </div>
                                            <?php echo form_close(); ?>
                                        </div>
                                    </div>
                                    <hr class="hr-panel-separator">
                                    <!-- Statistics Cards -->
                                    <div class="row mtop15">
                                        <div class="col-md-4">
                                            <div class="panel_s">
                                                <div class="panel-body text-center">
                                                    <h3 class="tw-text-lg"><?= _l('appointly_appointment_total_appointments'); ?></h3>
                                                    <div class="mtop10">
                                                        <span class="tw-text-3xl tw-font-regular" id="total_appointments"><?= $total_appointments ?? 0; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="panel_s">
                                                <div class="panel-body text-center">
                                                    <h3 class="tw-text-lg"><?= _l('appointly_appointment_completed_appointments'); ?></h3>
                                                    <div class="mtop10">
                                                        <span class="tw-text-3xl tw-font-regular text-success" id="completed_appointments"><?= $completed_appointments ?? 0; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="panel_s">
                                                <div class="panel-body text-center">
                                                    <h3 class="tw-text-lg"><?= _l('appointly_appointment_cancelled_appointments'); ?></h3>
                                                    <div class="mtop10">
                                                        <span class="tw-text-3xl tw-font-regular text-danger" id="cancelled_appointments"><?= $cancelled_appointments ?? 0; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Charts -->
                                    <div class="row mtop25">
                                        <div class="col-md-8">
                                            <div class="panel_s">
                                                <div class="panel-body">
                                                    <h4 class="tw-font-medium"><?= _l('appointly_monthly_stats'); ?></h4>
                                                    <div class="relative" style="height: 350px;">
                                                        <canvas id="monthly_stats"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="panel_s">
                                                <div class="panel-body">
                                                    <h4 class="tw-font-medium"><?= _l('appointly_popular_services'); ?></h4>
                                                    <div class="relative" style="height: 350px;">
                                                        <canvas id="popular_services"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Staff Performance Table -->
                            <div class="row mtop25 no-padding">
                                <div class="col-md-12">
                                    <h4 class="tw-font-regular"><?php echo _l('appointly_staff_performance'); ?></h4>
                                    <hr class="mtop5 mbot10" />

                                    <div class="table-responsive tw-overflow-x-hidden">
                                        <?php
                                        $table_data = [
                                            _l('appointly_staff_member'),
                                            _l('appointly_appointment_total_appointments'),
                                            _l('appointly_appointment_completed_appointments'),
                                            _l('appointly_appointment_cancelled_appointments'),
                                            _l('appointly_appointment_completion_rate')
                                        ];

                                        render_datatable($table_data, 'appointments-stats', [], [
                                            'data-order-col' => '1',
                                            'data-order-type' => 'desc'
                                        ]);
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script src="<?php echo base_url('assets/plugins/Chart.js/Chart.min.js'); ?>"></script>

<!-- Specific report scripts -->
<script src="<?php echo module_dir_url('appointly', 'assets/js/reports.js'); ?>?v=<?= time(); ?>"></script>

<script>
    // Pass data to charts
    var monthlyStatsLabels = <?= json_encode($monthly_stats['labels'] ?? []); ?>;
    var monthlyStatsTotal = <?= json_encode($monthly_stats['total'] ?? []); ?>;
    var monthlyStatsCompleted = <?= json_encode($monthly_stats['completed'] ?? []); ?>;
    var monthlyStatsCancelled = <?= json_encode($monthly_stats['cancelled'] ?? []); ?>;

    var popularServicesLabels = <?= json_encode($popular_services['labels'] ?? []); ?>;
    var popularServicesData = <?= json_encode($popular_services['counts'] ?? []); ?>;

    // Labels for chart legends
    var totalAppointmentsLabel = "<?= _l('appointly_appointment_total_appointments'); ?>";
    var completedAppointmentsLabel = "<?= _l('appointly_appointment_completed_appointments'); ?>";
    var cancelledAppointmentsLabel = "<?= _l('appointly_appointment_cancelled_appointments'); ?>";

    // Custom translation strings (accessible in JS)
    var appointlyLang = {
        appointly_date_range_required: "<?= addslashes(_l('appointly_date_range_required')); ?>",
        appointly_no_staff_performance_data: "<?= addslashes(_l('appointly_no_staff_performance_data')); ?>",
        appointly_loading_data: "<?= addslashes(_l('appointly_loading_data')); ?>",
        appointly_error_refreshing_stats: "<?= addslashes(_l('appointly_error_refreshing_stats')); ?>"
    };

    $(function() {
        // Initialize charts
        initMonthlyStatsChart();
        initPopularServicesChart();

        // Initialize datepickers
        ensureSqlDateAttributes();

        var staffPerformanceServerParams = function() {
            return {
                date_from: $('input[name="date_from"]').attr('data-sql-date'),
                date_to: $('input[name="date_to"]').attr('data-sql-date')
            };
        };

        initDataTable('.table-appointments-stats',
            admin_url + 'appointly/reports/table',
            [],
            [],
            staffPerformanceServerParams,
            [1, 'desc']
        );

        // Handle form submission
        $('#date_filter_form').on('submit', function(e) {
            e.preventDefault();
            refreshStats();
        });
    });

    // Function to set date range and update the form
    function setDateRange(range) {
        // Show loading spinner
        $('body').append('<div class="dt-loader"></div>');

        $.ajax({
            url: admin_url + 'appointly/reports/get_date_range',
            type: 'POST',
            data: {
                range: range
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    // Update date inputs with formatted display dates
                    $('input[name="date_from"]').val(response.date_from_display);
                    $('input[name="date_to"]').val(response.date_to_display);

                    // Store SQL formatted dates as data attributes
                    $('input[name="date_from"]').attr('data-sql-date', response.date_from);
                    $('input[name="date_to"]').attr('data-sql-date', response.date_to);

                    // Automatically refresh the statistics and table
                    refreshStats();

                    // Refresh the table as well
                    $('.table-appointments-stats').DataTable().ajax.reload();
                } else {
                    console.error('Error setting date range:', response.message || 'Unknown error');
                    alert(appointlyLang.appointly_error_refreshing_stats);
                }
                $('.dt-loader').remove();
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                alert(appointlyLang.appointly_error_refreshing_stats);
                $('.dt-loader').remove();
            }
        });

        return false;
    }

    // Function to refresh the stats
    function refreshStats() {
        // Get date values from form inputs with SQL date attributes
        var dateFrom = $('input[name="date_from"]').attr('data-sql-date');
        var dateTo = $('input[name="date_to"]').attr('data-sql-date');

        if (!dateFrom || !dateTo) {
            alert(appointlyLang.appointly_date_range_required);
            return;
        }

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
                if (response.success) {
                    // Update statistics counters
                    $('#total_appointments').text(response.data.total_appointments);
                    $('#completed_appointments').text(response.data.completed_appointments);
                    $('#cancelled_appointments').text(response.data.cancelled_appointments);

                    // Update charts
                    if (window.monthlyStatsChart) {
                        window.monthlyStatsChart.data.labels = response.data.monthly_stats.labels;
                        window.monthlyStatsChart.data.datasets[0].data = response.data.monthly_stats.total;
                        window.monthlyStatsChart.data.datasets[1].data = response.data.monthly_stats.completed;
                        window.monthlyStatsChart.data.datasets[2].data = response.data.monthly_stats.cancelled;
                        window.monthlyStatsChart.update();
                    }

                    if (window.popularServicesChart) {
                        window.popularServicesChart.data.labels = response.data.popular_services.labels;
                        window.popularServicesChart.data.datasets[0].data = response.data.popular_services.counts;
                        window.popularServicesChart.update();
                    }

                    // Refresh the datatable
                    $('.table-appointments-stats').DataTable().ajax.reload();

                    // Show success message
                    alert_float('success', "<?= addslashes(_l('appointly_reports_updated_for_period')); ?>".replace('%s', response.formatted_date_from).replace('%s', response.formatted_date_to));
                } else {
                    alert_float('warning', response.message || "<?= addslashes(_l('appointly_no_data_for_period')); ?>");
                }
            },
            complete: function() {
                // Remove loading indicator
                $('.dt-loader').remove();
            }
        });
    }
</script>

</body>

</html>
