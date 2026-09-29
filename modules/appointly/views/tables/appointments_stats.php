<?php defined('BASEPATH') or exit('No direct script access allowed');

// Only include actual database columns here
$aColumns = [
    'staffid',
    'firstname',
    'lastname'
];

// Define which columns can be sorted in the database
$notsortable = []; // We'll override this to control sorting

$sIndexColumn = 'staffid';
$sTable       = db_prefix() . 'staff';

// Get date filters from post
$date_from = $this->ci->input->post('date_from');
$date_to = $this->ci->input->post('date_to');

// Default date range if not provided
if (empty($date_from)) {
    $date_from = date('Y-m-01'); // First day of current month
}
if (empty($date_to)) {
    $date_to = date('Y-m-t'); // Last day of current month
}

// Only active staff members
$where = [];
array_push($where, 'AND active = 1');

// Override ordering to ensure we only sort by valid database columns
if (isset($_POST['order']) && isset($_POST['order'][0]['column'])) {
    $order_column_index = $_POST['order'][0]['column'];
    // If trying to sort by a non-database column, force sorting by staffid
    if ($order_column_index > 0) { // First column (staff name) is valid
        $_POST['order'][0]['column'] = 0; // Sort by staff id as default
    }
}

// Initialize the basic DataTable with only the actual database columns
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where);

$output  = $result['output'];
$rResult = $result['rResult'];

$output['aaData'] = [];

// Load the reports model to get performance data
$this->ci->load->model('appointly/reports_model');
$staff_performance = $this->ci->reports_model->get_staff_performance($date_from, $date_to);

// Track if we found any staff with appointments
$hasAppointments = false;

// Process data
foreach ($rResult as $aRow) {
    $staffId = $aRow['staffid'];

    // Find this staff's performance data
    $performanceData = null;
    foreach ($staff_performance as $performance) {
        if ($performance['staffid'] == $staffId) {
            $performanceData = $performance;
            break;
        }
    }

    // Skip staff with no appointments
    if (!$performanceData || !isset($performanceData['total']) || $performanceData['total'] == 0) {
        continue;
    }

    // We found at least one staff with appointments
    $hasAppointments = true;

    $row = [];

    // Staff Member with link to profile
    $staffName = trim($aRow['firstname'] . ' ' . $aRow['lastname']);
    $row[] = '<a href="' . admin_url('staff/profile/' . $staffId) . '">' . $staffName . '</a>';

    // Total Appointments
    $row[] = $performanceData['total'] ?? 0;

    // Completed Appointments
    $row[] = $performanceData['completed'] ?? 0;

    // Cancelled Appointments
    $row[] = $performanceData['cancelled'] ?? 0;

    // Completion Rate
    $completionRate = $performanceData['completion_rate'] ?? 0;
    $row[] = $completionRate . '%';

    //  data for custom sorting (these values won't be displayed)
    $row['DT_RowData'] = [
        'total' => $performanceData['total'] ?? 0,
        'completed' => $performanceData['completed'] ?? 0,
        'cancelled' => $performanceData['cancelled'] ?? 0,
        'completion_rate' => $completionRate
    ];

    //  row to output
    $output['aaData'][] = $row;
}

// If no staff with appointments were found, add a row indicating no data
if (!$hasAppointments) {
    $row = [
        '<span class="no-data-message">' . _l('appointly_no_data_found') . '</span>',
        '0',
        '0',
        '0',
        '0%'
    ];
    $output['aaData'][] = $row;
}

// Apply custom sorting based on order parameter if needed
if (isset($_POST['order'])) {
    $order = $_POST['order'][0];
    $columnIndex = $order['column'];
    $direction = $order['dir'];

    // If sorting by a calculated column (1=total, 2=completed, 3=cancelled, 4=rate)
    if ($columnIndex > 0) {
        // Map column index to field name
        $sortFields = ['total', 'completed', 'cancelled', 'completion_rate'];
        // Adjust index for the sortFields array (column 1->0, 2->1, etc.)
        $sortFieldIndex = $columnIndex - 1;

        if (isset($sortFields[$sortFieldIndex])) {
            $field = $sortFields[$sortFieldIndex];

            usort($output['aaData'], function ($a, $b) use ($field, $direction) {
                // Get values to compare (using DT_RowData for proper numeric comparison)
                $aVal = isset($a['DT_RowData'][$field]) ? $a['DT_RowData'][$field] : 0;
                $bVal = isset($b['DT_RowData'][$field]) ? $b['DT_RowData'][$field] : 0;

                // Convert to integers or floats for proper comparison
                if (is_numeric($aVal)) $aVal = floatval($aVal);
                if (is_numeric($bVal)) $bVal = floatval($bVal);

                // Compare values based on direction
                if ($direction === 'asc') {
                    return $aVal <=> $bVal;
                } else {
                    return $bVal <=> $aVal;
                }
            });
        }
    }
}

echo json_encode($output);
die(); // Required for AJAX datatable response
