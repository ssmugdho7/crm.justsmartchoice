<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('appointly/reports_model');
    }

    public function index()
    {
        if (!staff_can('view_reports', 'appointments')) {
            access_denied('view_reports');
        }

        // Set default date range (current month)
        $data['date_from'] = date('Y-m-01'); // First day of current month
        $data['date_to'] = date('Y-m-t');   // Last day of current month

        // Check if dates are passed via GET
        if ($this->input->get('date_from') && $this->input->get('date_to')) {
            $data['date_from'] = $this->try_parse_date($this->input->get('date_from'));
            $data['date_to'] = $this->try_parse_date($this->input->get('date_to'));
        }

        // Load report data
        $this->load_report_data($data, $data['date_from'], $data['date_to']);

        //  title and load the view
        $data['title'] = _l('appointly_reports_dashboard');
        $this->load->view('reports/dashboard', $data);
    }

    public function filter_data()
    {
        // Check if user has permission
        if (!staff_can('view_reports', 'appointments')) {
            ajax_access_denied();
        }

        // Validate date inputs
        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');

        // Try to parse dates or use defaults
        $date_from = $this->try_parse_date($date_from);
        $date_to = $this->try_parse_date($date_to);

        // Load required data
        $data = [];
        $this->load_report_data($data, $date_from, $date_to);

        // Return JSON response
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    public function get_date_range()
    {
        // Check permission
        if (!staff_can('view_reports', 'appointments')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied')
            ]);
            return;
        }

        // Get range param
        $range = $this->input->post('range');

        if (!$range) {
            echo json_encode([
                'success' => false,
                'message' => 'Range parameter is required'
            ]);
            return;
        }

        // Get current year
        $current_year = date('Y');

        // Get date range
        $dates = [];

        switch ($range) {
            case 'today':
                $dates['date_from'] = date('Y-m-d');
                $dates['date_to'] = date('Y-m-d');
                break;
            case 'this_week':
                $dates['date_from'] = date('Y-m-d', strtotime('monday this week'));
                $dates['date_to'] = date('Y-m-d', strtotime('sunday this week'));
                break;
            case 'this_month':
                $dates['date_from'] = date('Y-m-01');
                $dates['date_to'] = date('Y-m-t');
                break;
            case 'this_year':
                $dates['date_from'] = $current_year . '-01-01';
                $dates['date_to'] = $current_year . '-12-31';
                break;
            case 'last_month':
                $dates['date_from'] = date('Y-m-01', strtotime('first day of last month'));
                $dates['date_to'] = date('Y-m-t', strtotime('last day of last month'));
                break;
            case 'last_year':
                $previous_year = $current_year - 1;
                $dates['date_from'] = $previous_year . '-01-01';
                $dates['date_to'] = $previous_year . '-12-31';
                break;
            default:
                $dates['date_from'] = date('Y-m-01');
                $dates['date_to'] = date('Y-m-t');
                break;
        }

        // Return as JSON with display format
        echo json_encode([
            'success' => true,
            'date_from' => $dates['date_from'],
            'date_to' => $dates['date_to'],
            'date_from_display' => _d($dates['date_from']),
            'date_to_display' => _d($dates['date_to'])
        ]);
    }

    public function refresh_stats()
    {
        // Check if user has permission
        if (!staff_can('view_reports', 'appointments')) {
            ajax_access_denied();
        }

        // Validate date inputs
        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');

        if (!$date_from || !$date_to) {
            echo json_encode([
                'success' => false,
                'message' => _l('appointly_date_range_required')
            ]);
            return;
        }

        // Try to parse dates or use defaults
        $date_from = $this->try_parse_date($date_from);
        $date_to = $this->try_parse_date($date_to);

        // Load required data
        $data = [];
        $this->load_report_data($data, $date_from, $date_to);

        // Return JSON response
        echo json_encode([
            'success' => true,
            'data' => $data,
            'formatted_date_from' => _d($date_from),
            'formatted_date_to' => _d($date_to)
        ]);
    }

    private function load_report_data(&$data, $date_from = null, $date_to = null)
    {
        // If no dates provided, use current month
        if (!$date_from) {
            $date_from = date('Y-m-01');
        }
        if (!$date_to) {
            $date_to = date('Y-m-t');
        }

        // Load Reports model
        $this->load->model('appointly/reports_model');

        // Calculate total appointments in date range
        $total_appointments = $this->reports_model->get_total_appointments($date_from, $date_to);
        $data['total_appointments'] = $total_appointments;

        // Calculate completed appointments in date range
        $completed_appointments = $this->reports_model->get_completed_appointments($date_from, $date_to);
        $data['completed_appointments'] = $completed_appointments;

        // Calculate cancelled appointments in date range
        $cancelled_appointments = $this->reports_model->get_cancelled_appointments($date_from, $date_to);
        $data['cancelled_appointments'] = $cancelled_appointments;

        // Calculate staff performance
        $staff_performance = $this->reports_model->get_staff_performance($date_from, $date_to);
        $data['staff_performance'] = $staff_performance;

        // Calculate monthly statistics for the period
        $monthly_stats = $this->reports_model->get_monthly_appointments_stats($date_from, $date_to);

        // Prepare data for charts - extract dates for labels
        $labels = [];
        $totals = [];
        $completed = [];
        $cancelled = [];

        foreach ($monthly_stats as $day_stat) {
            // Format the date for display (e.g., Apr 01, Apr 02, etc.)
            $labels[] = date('M d', strtotime($day_stat['date']));
            $totals[] = (int)$day_stat['total'];
            $completed[] = (int)$day_stat['completed'];
            $cancelled[] = (int)$day_stat['cancelled'];
        }

        $data['monthly_stats'] = [
            'labels' => $labels,
            'total' => $totals,
            'completed' => $completed,
            'cancelled' => $cancelled
        ];

        // Get popular services
        $popular_services = $this->reports_model->get_popular_services($date_from, $date_to);
        $data['popular_services'] = [
            'labels' => array_column($popular_services, 'name'),
            'counts' => array_column($popular_services, 'count')
        ];

        //  formatted dates for display
        $data['formatted_date_from'] = _d($date_from);
        $data['formatted_date_to'] = _d($date_to);
    }

    /**
     * Helper function to try parsing date from different formats
     * @param string $date_string Date string to parse
     * @return string Date in SQL format (Y-m-d)
     */
    private function try_parse_date($date_string)
    {
        // Return today if no date provided
        if (empty($date_string)) {
            return date('Y-m-d');
        }

        // Already in SQL format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_string)) {
            return $date_string;
        }

        // Try parsing with DateTime
        try {
            // Check if we have app date format from settings
            $app_date_format = get_option('dateformat');
            $app_date_format = str_replace('Y', 'y', $app_date_format);
            $app_date_format = str_replace('m', 'M', $app_date_format);
            $app_date_format = str_replace('d', 'd', $app_date_format);

            $date = DateTime::createFromFormat($app_date_format, $date_string);

            // If failed with app format, try common formats
            if (!$date) {
                // Try common formats
                $formats = [
                    'd/m/Y',
                    'm/d/Y',
                    'Y/m/d',
                    'd-m-Y',
                    'm-d-Y',
                    'Y-m-d',
                    'd.m.Y',
                    'm.d.Y',
                    'Y.m.d',
                ];

                foreach ($formats as $format) {
                    $date = DateTime::createFromFormat($format, $date_string);
                    if ($date) {
                        break;
                    }
                }
            }

            // If still no valid date, try strtotime as last resort
            if (!$date && strtotime($date_string)) {
                $date = new DateTime($date_string);
            }

            // If we have a valid date, format it for SQL
            if ($date) {
                return $date->format('Y-m-d');
            }
        } catch (Exception $e) {
            log_message('error', 'Date parsing error: ' . $e->getMessage());
        }

        // Default to today if all else fails
        return date('Y-m-d');
    }


    /**
     * Get appointment stats table data
     */
    public function table()
    {
        if (!staff_can('view_reports', 'appointments')) {
            ajax_access_denied();
        }

        // Get date range from POST
        $date_from = $this->input->post('date_from');
        $date_to = $this->input->post('date_to');

        // Default to current month if no dates provided
        if (!$date_from) {
            $date_from = date('Y-m-01');
        }
        if (!$date_to) {
            $date_to = date('Y-m-t');
        }

        // Try to parse dates
        $date_from = $this->try_parse_date($date_from);
        $date_to = $this->try_parse_date($date_to);
        $total_appointments = $this->reports_model->get_total_appointments($date_from, $date_to);

        // Pass date parameters to the view
        $this->app->get_table_data(
            module_views_path('appointly', 'tables/appointments_stats'),
            [
                'date_from' => $date_from,
                'date_to' => $date_to,
                'total_appointments' => $total_appointments
            ]
        );
    }
}
