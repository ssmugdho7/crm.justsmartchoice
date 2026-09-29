<?php

defined('BASEPATH') || exit('No direct script access allowed');

/*
    Module Name: Custom Data Tables
    Description: Custom Data Tables gives Smart Choice Contractors USA a safer and cleaner way to customize Perfex CRM list views without editing core CRM files. Staff with permission can choose which columns appear on Leads, Customers, Projects, Tasks, Invoices, Estimates, Proposals, Contracts, Expenses, and Items. The module includes a table style designer, staff-level table preferences, safer defaults, and a modern admin experience designed for construction-company operations, project tracking, sales follow-up, and daily CRM productivity.
    Version: 2.0.0
    Requires at least: 3.4.1
    Author: Smart Choice Contractors USA / Harold Cabrera
*/

define('CUSTOMTABLES_MODULE', 'customtables');

register_activation_hook(CUSTOMTABLES_MODULE, 'customtables_module_activate_hook');
function customtables_module_activate_hook() {
    require_once __DIR__ . '/install.php';
}

register_language_files(CUSTOMTABLES_MODULE, [CUSTOMTABLES_MODULE]);

$CI = &get_instance();
$CI->load->helper(CUSTOMTABLES_MODULE . '/customtables');
$CI->load->library(CUSTOMTABLES_MODULE . '/customtables_lib');
$CI->config->load(CUSTOMTABLES_MODULE . '/config');

require_once __DIR__ . '/includes/assets.php';
require_once __DIR__ . '/includes/staff_permissions.php';
require_once __DIR__ . '/includes/sidebar_menu_links.php';

$show_columns = [
    'leads' => '',
    'clients' => '',
    'proposals' => '',
    'estimates' => '',
    'projects' => '',
    'tasks' => '',
    'invoices' => '',
    'contracts' => '',
    'expenses'  => '',
    'items'     => '',
];
// hooks for all tables.
$tables = [
    'leads' => 'leads',
    'customers' => 'clients',
    'proposals' => 'proposals',
    'estimates' => 'estimates',
    'projects' => 'projects',
    'tasks' => 'tasks',
    'invoices' => 'invoices',
    'contracts' => 'contracts',
    'expenses'  => 'expenses',
    'items'     => 'items',
];

foreach ($tables as $key => $table) {
    if ('expenses' == $table && 'projects' == get_instance()->uri->segment(2)) {
        return;
    }
    if ('reports' == get_instance()->uri->segment(2)) {
        return;
    }
    $show_columns[$table] = json_decode(get_staff_meta(get_staff_user_id(), $table . '_show_columns'));

    $temp_config_columns = config_item($table . '_columns');
    $cfTable = $table;
    if ('proposals' == $table || 'estimates' == $table || 'invoices' == $table) {
        $cfTable = rtrim($table, 's');
    }
    if ('clients' == $table) {
        $cfTable = 'customers';
    }
    $custom_fields = get_table_custom_fields($cfTable);

    $custom_fields_add = [];
    foreach ($custom_fields as $ckey => $value) {
        $selectAs = (is_cf_date($value) ? 'date_picker_cvalue_' . $ckey : 'cvalue_' . $ckey);
        $acolumns = '(SELECT value FROM ' . db_prefix() . 'customfieldsvalues WHERE ' . db_prefix() . 'customfieldsvalues.relid=' . db_prefix() . 'tasks.id AND ' . db_prefix() . 'customfieldsvalues.fieldid=' . $value['id'] . ' AND ' . db_prefix() . 'customfieldsvalues.fieldto="' . $value['fieldto'] . '" LIMIT 1) as ' . $selectAs;
        $custom_fields_add[] = [
            'column' => ('tasks' == $table) ? $acolumns : 'ctable_' . $ckey . '.value as ' . $selectAs,
            'label' => $value['name'],
            'initial' => true,
            'required' => true,
            'th_attrs' => ['data-type' => $value['type'], 'data-custom-field' => 1],
        ];
    }
    $temp_config_columns = array_merge($temp_config_columns, $custom_fields_add);
    $config_columns[$table] = $temp_config_columns;

    if ($key == 'customers') {
        $key = 'clients';
    }

    if ($show_columns[$key]) {
        if ($key == 'clients') {
            $key = 'customers';
        }
        hooks()->add_filter("{$key}_table_columns", function ($tableData) use ($table, $show_columns, $config_columns) {
            return render_heads($show_columns[$table], $config_columns[$table]);
        });
        hooks()->add_filter("{$key}_table_sql_columns", function ($aColumns) use ($table, $show_columns, $config_columns) {
            return sql_columns($show_columns[$table], $config_columns[$table]);
        });
        hooks()->add_filter("{$key}_table_row_data", function ($parseHTML, $data) use ($table, $show_columns, $config_columns) {
            return render_columns($table, $parseHTML, $data, $show_columns[$table], $config_columns[$table]);
        }, 10, 2);
        // Make empty value for initial columns
        hooks()->add_filter('datatables_sql_query_results', function ($result, $data) use ($show_columns, $config_columns) {
            return datatablesSqlQueryResults($result, $data, $show_columns, $config_columns);
        }, 10, 2);
    }
}

function datatablesSqlQueryResults($result, $data, $show_columns, $config_columns) {
    $tableName = str_replace(db_prefix(), '', $data['table']);
    if ('expenses' == $tableName && 'projects' == get_instance()->uri->segment(2)) {
        return $result;
    }
    if ('reports' == get_instance()->uri->segment(2)) {
        return $result;
    }

    $restrictedUrls = [
        admin_url($tableName . '/table'),
        admin_url($tableName . '/table/' . get_instance()->uri->segment(4)),
        admin_url('tasks/table?bulk_actions=true'),
        admin_url('contracts/table?project_id=' . get_instance()->input->get('project_id')),
    ];

    if (in_array(current_full_url(), $restrictedUrls)) {
        foreach ($result as &$row) {
            $tables = ['leads', 'clients', 'proposals', 'estimates', 'invoices', 'expenses', 'projects', 'tasks', 'contracts'];
            // Apply perfex date format in custom fields
            array_walk($row, fn(&$v, $k) => strpos($k, 'date_picker_') !== false ? $v = _d($v) : null);
            // Check if the current table name is in the list of tables
            if (in_array($tableName, $tables) && $show_columns[$tableName]) {
                processEmptyColumns($row, $show_columns[$tableName], $config_columns[$tableName]);
            }
        }
    }
    return $result;
}

function processEmptyColumns(&$row, $show_columns, $config_columns) {
    $tableData = getColumnFromTable($show_columns, $config_columns)['available_options'];
    $emptyColumns = array_filter($tableData, function ($column) {
        return $column['initial'];
    });

    $emptyColumnNames = array_column($emptyColumns, 'column');

    foreach ($emptyColumnNames as $column) {
        if (false !== strpos($column, ' as ')) {
            $column = strafter($column, ' as ');
        }
        $row[$column] = '';
    }
}

// manage THEAD based on selection
function render_heads($show_columns, $config_columns) {
    $columns = getColumnFromTable($show_columns, $config_columns);
    $newTableData = [];

    foreach ($columns['selected_options'] as $column) {
        $newTableData[] = [
            'name' => $column['label'],
            'th_attrs' => $column['th_attrs'] ?? ['class' => 'toggleable', 'id' => 'th-' . $column['column']],
        ];
    }

    return $newTableData;
}

// manage aColumns based on selection
function sql_columns($show_columns, $config_columns) {
    $columns = getColumnFromTable($show_columns, $config_columns);

    foreach ($columns['selected_options'] as $column) {
        $newColumns[] = $column['column'];
    }

    return $newColumns;
}

// make data based on data and initial columns
function render_columns($type, $parseHTML, $data, $show_columns, $config_columns) {
    $default_values = [];
    array_walk($parseHTML, function ($value, $key) use (&$default_values) {
        if (!is_numeric($key)) {
            $default_values[$key] = $value;
        }
    });

    // Set tableprefix
    $prefix = db_prefix() . $type . '.';
    $columns = getColumnFromTable($show_columns, $config_columns);
    $selectedColumns = $columns['selected_options'];

    // Date field list
    $dateColumns = [
        'datefinished',
        'last_recurring_date',
        'date_finished',
        'project_created',
        'date',
        'acceptance_date',
        'invoiced_date',
        $prefix . 'datecreated',
        $prefix . 'dateadded',
    ];

    // Country field list
    $countryColumns = [
        $prefix . 'country',
        $prefix . 'billing_country',
        $prefix . 'shipping_country',
    ];

    $newhtml = [];
    foreach ($selectedColumns as $key => $column) {
        if (isset($parseHTML[$key]) && $column['initial']) {
            $row = $parseHTML[$key];
        } else {
            if (false !== strpos($column['column'], ' as ')) {
                $column['column'] = strafter($column['column'], ' as ');
            }
            switch ($column['column']) {
                case 'email':
                    $row = $data['email'] ? '<a href="mailto:' . $data['email'] . '">' . $data['email'] . '</a>' : '';
                    break;

                case 'phonenumber':
                    $row = $data['phonenumber'] ? '<a href="tel:' . $data['phonenumber'] . '">' . $data['phonenumber'] . '</a>' : '';
                    break;

                case 'project_cost':
                    $currency = get_instance()->projects_model->get_currency($data['id']);
                    $row = app_format_money($data['project_cost'], $currency);
                    break;

                case in_array($column['column'], $dateColumns):
                    $row = _dt($data[$column['column']]);
                    break;

                case in_array($column['column'], $countryColumns):
                    $row = get_country_name($data[$column['column']]);
                    break;

                default:
                    $row = $data[$column['column']];
                    break;
            }
        }
        $newhtml[] = $row;
    }

    return array_merge($newhtml, $default_values);
}


// Smart Choice Standard Permission Registration
if (!function_exists('customtables_smart_choice_standard_permissions')) {
    function customtables_smart_choice_standard_permissions()
    {
        if (function_exists('register_staff_capabilities')) {
            register_staff_capabilities('customtables', [
                'capabilities' => [
                    'view_own' => _l('permission_view_own'),
                    'view'     => _l('permission_view'),
                    'create'   => _l('permission_create'),
                    'edit'     => _l('permission_edit'),
                    'delete'   => _l('permission_delete'),
                ],
            ], 'Customtables');
        }
    }
}
hooks()->add_action('admin_init', 'customtables_smart_choice_standard_permissions');
