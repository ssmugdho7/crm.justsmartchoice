<?php defined('BASEPATH') or exit('No direct script access allowed');

$servicesTable = db_prefix() . 'appointly_services';
$serviceOrderColumn = $this->ci->db->field_exists('sort_order', $servicesTable) ? 'sort_order' : 'id';

$aColumns = [
    $serviceOrderColumn,
    'name',
    'description',
    'duration',
    'price',
    'color',
    'active',
    'id'
];

$sIndexColumn = 'id';
$sTable       = $servicesTable;

$result = data_tables_init($aColumns, $sIndexColumn, $sTable);

$output  = $result['output'];
$rResult = $result['rResult'];

$output['aaData'] = [];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = '<span class="appointly-service-drag" style="cursor:move"><i class="fa-solid fa-grip-vertical"></i></span>';

    // Name column with color and description
    $nameColumn = '<div class="tw-flex tw-items-center tw-space-x-2">';
    $serviceColors = [
        'Free In-Home Consultation' => '#169179',
        'Remodeling & Renovation Planning Session' => '#3598db',
        'Project Estimate & Scope Review' => '#f59e0b',
        'Engineering / Permit Consultation' => '#8b5cf6',
        'Repair & Troubleshooting Visit' => '#ef4444',
    ];
    $displayColor = isset($serviceColors[$aRow['name']]) ? $serviceColors[$aRow['name']] : (!empty($aRow['color']) ? $aRow['color'] : '#169179');
    $nameColumn .= '<span style="display:inline-block;width:26px;height:12px;border-radius:999px;background-color:' . html_escape($displayColor) . ';flex:0 0 auto;"></span>';
    $nameColumn .= '<div>';
    $nameColumn .= '<a href="' . admin_url('appointly/services/service/' . $aRow['id']) . '" class="tw-font-medium">' . $aRow['name'] . '</a>';
    if (!empty($aRow['description'])) {
        $nameColumn .= '<div class="tw-text-neutral-500 tw-text-xs">' . substr($aRow['description'], 0, 100) . (strlen($aRow['description']) > 100 ? '...' : '') . '</div>';
    }
    $nameColumn .= '</div>';
    $nameColumn .= '</div>';
    $row[] = $nameColumn;

    // Duration
    $row[] = $aRow['duration'];

    // Price
    $row[] = app_format_money($aRow['price'], get_base_currency());

    // Staff members - Query the service_staff table
    $staff_names = [];

    // Get providers from service_staff table
    $this->ci->db->select('s.staffid, s.firstname, s.lastname');
    $this->ci->db->from(db_prefix() . 'appointly_service_staff ss');
    $this->ci->db->join(db_prefix() . 'staff s', 's.staffid = ss.staff_id', 'left');
    $this->ci->db->where('ss.service_id', $aRow['id']);
    $this->ci->db->where('ss.is_provider', 1);
    $staff_query = $this->ci->db->get();

    if ($staff_query && $staff_query->num_rows() > 0) {
        foreach ($staff_query->result() as $staff) {
            $staff_names[] = '<a href="' . admin_url('staff/profile/' . $staff->staffid) . '" target="_blank" class="tw-inline-block tw-bg-neutral-100 tw-rounded tw-px-2 tw-py-1 tw-text-sm tw-mr-1 tw-mb-1">'
                . htmlspecialchars($staff->firstname . ' ' . $staff->lastname)
                . '</a>';
        }
    }

    $row[] = !empty($staff_names) ? implode(' ', $staff_names) : '<span class="tw-text-neutral-400">' . _l('service_no_providers') . '</span>';

    // Check if the service is in use
    $this->ci->db->select('a.id');
    $this->ci->db->from(db_prefix() . 'appointly_appointments a');
    $this->ci->db->join(db_prefix() . 'appointly_appointment_services aps', 'a.id = aps.appointment_id', 'inner');
    $this->ci->db->where('aps.service_id', $aRow['id']);
    $this->ci->db->where('a.status !=', 'cancelled');
    $this->ci->db->where('a.status !=', 'completed');
    $is_service_in_use = $this->ci->db->get()->num_rows() > 0;

    // Active status with toggle switch
    $toggleChecked = $aRow['active'] ? 'checked' : '';
    $toggleDisabled = $is_service_in_use ? 'data-service-in-use="1"' : '';

    $row[] = '<div class="onoffswitch">' .
        '<input type="checkbox" data-switch-url="' . admin_url() . 'appointly/services/change_status" ' .
        'name="onoffswitch" class="onoffswitch-checkbox" ' .
        'id="s_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $toggleChecked . ' ' . $toggleDisabled . '>' .
        '<label class="onoffswitch-label" for="s_' . $aRow['id'] . '"></label>' .
        '</div>';

    // Options
    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
    $options .= '<a href="' . admin_url('appointly/services/service/' . $aRow['id']) . '" ' .
        'class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">' .
        '<i class="fa-regular fa-pen-to-square fa-lg"></i>' .
        '</a>';

    // Only show delete button if service is not in use
    if (!$is_service_in_use) {
        $options .= '<a href="#" onclick="delete_service(' . $aRow['id'] . '); return false;" ' .
            'class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">' .
            '<i class="fa-regular fa-trash-can fa-lg"></i>' .
            '</a>';
    } else {
        $options .= '<span class="tw-text-neutral-300 tw-cursor-not-allowed" title="' . _l('appointly_service_in_use_warning') . '">' .
            '<i class="fa-regular fa-trash-can fa-lg"></i>' .
            '</span>';
    }

    $options .= '</div>';

    $row[] = $options;

    $row['DT_RowId']='appointly-service-'.(int)$aRow['id'];
    $output['aaData'][]=$row;
}

echo json_encode($output);
die();
