<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Reports model for Appointly module
 * Handles all report-related data retrieval and processing
 */
class Reports_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get total number of appointments for the given date range
     *
     * @param string $date_from Start date in Y-m-d format
     * @param string $date_to End date in Y-m-d format
     * @return int Total number of appointments
     */
    public function get_total_appointments($date_from, $date_to)
    {

        // Build the query directly instead of using get_compiled_select
        $this->db->select('COUNT(*) as total');
        $this->db->from(db_prefix() . 'appointly_appointments');
        $this->db->where('DATE(date) >=', $date_from);
        $this->db->where('DATE(date) <=', $date_to);

        // Log the query for debugging
        $query_string = $this->db->get_compiled_select();

        // Reset query and execute it properly
        $this->db->reset_query();

        // Execute the query directly
        $sql = "SELECT COUNT(*) as total FROM " . db_prefix() . "appointly_appointments 
                WHERE DATE(date) >= '$date_from' AND DATE(date) <= '$date_to'";
        $query = $this->db->query($sql);
        $result = $query->row();

        // Log the result

        return $result->total;
    }

    /**
     * Get total number of completed appointments for the given date range
     *
     * @param string $date_from Start date in SQL format (Y-m-d)
     * @param string $date_to End date in SQL format (Y-m-d)
     * @return int Total number of completed appointments
     */
    public function get_completed_appointments($date_from, $date_to)
    {

        $sql = "SELECT COUNT(id) as total FROM " . db_prefix() . "appointly_appointments 
                WHERE DATE(date) BETWEEN '$date_from' AND '$date_to' 
                AND status = 'completed'";

        $result = $this->db->query($sql)->row();


        return $result ? $result->total : 0;
    }

    /**
     * Get total number of cancelled appointments for the given date range
     *
     * @param string $date_from Start date in SQL format (Y-m-d)
     * @param string $date_to End date in SQL format (Y-m-d)
     * @return int Total number of cancelled appointments
     */
    public function get_cancelled_appointments($date_from, $date_to)
    {

        $sql = "SELECT COUNT(id) as total FROM " . db_prefix() . "appointly_appointments 
                WHERE DATE(date) BETWEEN '$date_from' AND '$date_to' 
                AND status = 'cancelled'";

        $result = $this->db->query($sql)->row();


        return $result ? $result->total : 0;
    }

    /**
     * Get staff performance data for the given date range
     *
     * @param string $date_from Start date in SQL format (Y-m-d)
     * @param string $date_to End date in SQL format (Y-m-d)
     * @return array Staff performance statistics
     */
    public function get_staff_performance($date_from, $date_to)
    {

        $sql = "SELECT 
                s.staffid,
                s.firstname,
                s.lastname,
                COUNT(a.id) as total,
                SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN a.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM " . db_prefix() . "staff s
                LEFT JOIN " . db_prefix() . "appointly_appointments a ON s.staffid = a.provider_id 
                    AND DATE(a.date) BETWEEN '$date_from' AND '$date_to'
                GROUP BY s.staffid
                ORDER BY total DESC";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        // Log the result

        return $result;
    }

    /**
     * Get monthly appointment statistics for the given date range
     *
     * @param string $date_from Start date in SQL format (Y-m-d)
     * @param string $date_to End date in SQL format (Y-m-d)
     * @return array Array of daily appointment statistics
     */
    public function get_monthly_appointments_stats($date_from, $date_to)
    {

        $sql = "SELECT 
                DATE(date) as date, 
                COUNT(*) as total, 
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'no-show' THEN 1 ELSE 0 END) as no_show
                FROM " . db_prefix() . "appointly_appointments                
                WHERE DATE(date) BETWEEN '$date_from' AND '$date_to'
                GROUP BY DATE(date)
                ORDER BY date ASC";

        $query = $this->db->query($sql);
        $result = $query->result_array();

        // Log the result

        // If no results or we want to ensure all days are included,
        // generate data for all days in the date range
        $start = new DateTime($date_from);
        $end = new DateTime($date_to);
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);

        // Create a lookup array from the results
        $date_data = [];
        foreach ($result as $row) {
            $date_data[$row['date']] = $row;
        }

        // Build the complete data set with all dates in range
        $complete_data = [];
        foreach ($period as $dt) {
            $date = $dt->format('Y-m-d');
            if (isset($date_data[$date])) {
                $complete_data[] = $date_data[$date];
            } else {
                $complete_data[] = [
                    'date' => $date,
                    'total' => 0,
                    'completed' => 0,
                    'cancelled' => 0
                ];
            }
        }

        //  the last day to include date_to
        $last_day = $end->format('Y-m-d');
        if (!isset($date_data[$last_day])) {
            $complete_data[] = [
                'date' => $last_day,
                'total' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
        }


        return $complete_data;
    }

    /**
     * Get popular services for the given date range
     *
     * @param string $date_from Start date in Y-m-d format
     * @param string $date_to End date in Y-m-d format
     * @return array Popular services data
     */
    public function get_popular_services($date_from, $date_to)
    {
        //  debug logging

        // Execute the query directly
        $sql = "SELECT 
                COALESCE(s.name, 'Unassigned') as name, 
                COUNT(*) as count
            FROM " . db_prefix() . "appointly_appointments a
            LEFT JOIN " . db_prefix() . "appointly_services s ON s.id = a.service_id
            WHERE a.date >= '$date_from'
            AND a.date <= '$date_to'
            GROUP BY s.id, s.name
            HAVING name IS NOT NULL
            ORDER BY count DESC
            LIMIT 5";

        // Log the query for debugging

        $query = $this->db->query($sql);
        $result = $query->result_array();

        // Log the result

        // If no results, return default structure
        if (empty($result)) {
            $result = [[
                'name' => 'No services yet',
                'count' => 0
            ]];
        }

        return $result;
    }

    /**
     * Get period comparison data for dashboard
     * 
     * @param string $current_from Current period start date
     * @param string $current_to Current period end date
     * @param string $previous_from Previous period start date
     * @param string $previous_to Previous period end date
     * @return array Comparison data
     */
    public function get_period_comparison($current_from, $current_to, $previous_from, $previous_to)
    {
        //  debug logging

        // Get current period stats
        $current_total = $this->get_total_appointments($current_from, $current_to);
        $current_completed = $this->get_completed_appointments($current_from, $current_to);
        $current_cancelled = $this->get_cancelled_appointments($current_from, $current_to);

        // Get previous period stats
        $previous_total = $this->get_total_appointments($previous_from, $previous_to);
        $previous_completed = $this->get_completed_appointments($previous_from, $previous_to);
        $previous_cancelled = $this->get_cancelled_appointments($previous_from, $previous_to);

        // Calculate percentages
        $total_change = $previous_total > 0 ? (($current_total - $previous_total) / $previous_total) * 100 : 0;
        $completed_change = $previous_completed > 0 ? (($current_completed - $previous_completed) / $previous_completed) * 100 : 0;
        $cancelled_change = $previous_cancelled > 0 ? (($current_cancelled - $previous_cancelled) / $previous_cancelled) * 100 : 0;

        $result = [
            'total' => [
                'current' => $current_total,
                'previous' => $previous_total,
                'percentage' => round($total_change, 1),
                'increased' => $total_change >= 0
            ],
            'completed' => [
                'current' => $current_completed,
                'previous' => $previous_completed,
                'percentage' => round($completed_change, 1),
                'increased' => $completed_change >= 0
            ],
            'cancelled' => [
                'current' => $current_cancelled,
                'previous' => $previous_cancelled,
                'percentage' => round($cancelled_change, 1),
                'increased' => $cancelled_change >= 0
            ]
        ];


        return $result;
    }
}
