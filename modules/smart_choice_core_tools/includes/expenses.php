<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Native Expenses remains the controller and persistence layer. Only these views are replaced.
hooks()->add_filter('app_view_data', 'scct_expenses_view');
hooks()->add_filter('admin_body_class', 'scct_expenses_body');
hooks()->add_filter('expenses_table_row_data', 'scct_expenses_row', 10, 2);
hooks()->add_filter('expenses_table_sql_join', 'scct_expenses_table_filter');
hooks()->add_action('app_admin_head', 'scct_expenses_head');
hooks()->add_action('app_admin_footer', 'scct_expenses_footer');
hooks()->add_action('admin_init', 'scct_expenses_admin_init');
hooks()->add_action('app_init', 'scct_expenses_download_guard');

function scct_expenses_context()
{
    return strtolower(get_instance()->router->fetch_class()) === 'expenses';
}

function scct_expenses_view($view)
{
    if (!scct_expenses_context()) {
        return $view;
    }
    $names = ['manage', 'expense', 'expense_preview_template', 'manage_categories', 'expenses_total_template'];
    foreach ($names as $name) {
        if ($view['path'] === 'admin/expenses/' . $name) {
            if ($name === 'expenses_total_template' && !isset($view['data']['scct_expense_summary'])) {
                return $view;
            }
            $view['path'] = '../../modules/smart_choice_core_tools/views/expenses/' . $name;
            if ($name === 'manage_categories') {
                $view['data']['scct_category_spending'] = scct_expenses_category_spending();
            }
            break;
        }
    }
    return $view;
}

function scct_expenses_body($classes)
{
    if (scct_expenses_context()) {
        $classes[] = 'scct-expenses';
    }
    return $classes;
}

function scct_expenses_head()
{
    if (scct_expenses_context()) {
        $file = 'assets/css/expenses-custom.css';
        echo '<link rel="stylesheet" href="' . e(module_dir_url('smart_choice_core_tools', $file) . '?v=' . filemtime(__DIR__ . '/../' . $file)) . '">';
    }
}

function scct_expenses_footer()
{
    if (scct_expenses_context()) {
        $file = 'assets/js/expenses.js';
        echo '<script src="' . e(module_dir_url('smart_choice_core_tools', $file) . '?v=' . filemtime(__DIR__ . '/../' . $file)) . '"></script>';
    }
}

function scct_expenses_status_labels()
{
    return [
        'all' => _l('scct_expenses_all'),
        'billable' => _l('expenses_list_billable'),
        'non_billable' => _l('scct_expenses_non_billable'),
        'not_invoiced' => _l('scct_expenses_not_invoiced'),
        'billed' => _l('scct_expenses_billed'),
    ];
}

// These predicates are shared by the summary and quick filters. Billed means linked to an invoice,
// independently of whether that invoice has been paid; the native invoice/payment badges remain.
function scct_expenses_predicates($alias)
{
    return [
        'all' => '1=1',
        'billable' => "$alias.billable=1",
        'non_billable' => "$alias.billable=0",
        'not_invoiced' => "$alias.billable=1 AND $alias.invoiceid IS NULL",
        'billed' => "$alias.billable=1 AND $alias.invoiceid IS NOT NULL",
    ];
}

function scct_expenses_table_filter($joins)
{
    if (!scct_expenses_context()) {
        return $joins;
    }
    $CI = &get_instance();
    $table = db_prefix() . 'expenses';
    $category = $CI->input->post('scct_expense_category');
    $status = $CI->input->post('scct_expense_status');
    $clauses = [];
    if (is_scalar($category) && ctype_digit((string) $category) && (int) $category > 0) {
        $clauses[] = "$table.category=" . (int) $category;
    }
    $predicates = scct_expenses_predicates($table);
    if (is_string($status) && isset($predicates[$status])) {
        $clauses[] = '(' . $predicates[$status] . ')';
    }
    // Extend the native INNER category join, retaining its saved filters, own-record WHERE and count query.
    foreach ($joins as &$join) {
        if (strpos($join, 'JOIN ' . db_prefix() . 'expenses_categories ON ') === 0 && $clauses) {
            $join .= ' AND ' . implode(' AND ', $clauses);
        }
    }
    unset($join);
    return $joins;
}

function scct_expenses_row($row, $expense)
{
    if (!scct_expenses_context()) {
        return $row;
    }
    if (preg_match('~<div class="row-options">(.*?)</div>~s', $row[2], $match)) {
        $row[2] = str_replace($match[0], '', $row[2]);
        $actions = str_replace(' | ', '', $match[1]);
        $actions = preg_replace('~<a href="([^"]+)" onclick=~', '<a class="exp-btn-view-pill" href="$1" onclick=', $actions);
        $actions = preg_replace('~<a href="([^"]+/expenses/expense/[^\"]+)"~', '<a class="exp-btn-edit-pill" href="$1"', $actions);
        $row[4] .= '<div class="exp-row-actions">' . $actions . '</div>';
    }
    $row[2] = preg_replace('~class="tw-font-medium"~', 'class="tw-font-medium exp-category-pill"', $row[2], 1);
    if (!empty($row[5])) {
        $row[5] = '<span class="exp-receipt-badge">' . $row[5] . '</span>';
    }
    return $row;
}

function scct_expenses_can_read($expense)
{
    return $expense && (staff_can('view', 'expenses') ||
        (staff_can('view_own', 'expenses') && (int) $expense->addedfrom === (int) get_staff_user_id()));
}

function scct_expenses_assert_record($id, $capability = null)
{
    $CI = &get_instance();
    $expense = is_scalar($id) && ctype_digit((string) $id) ? $CI->db->where('id', (int) $id)->get(db_prefix() . 'expenses')->row() : null;
    if (!scct_expenses_can_read($expense) || ($capability && !staff_can($capability, 'expenses'))) {
        access_denied('expenses');
    }
}

function scct_expenses_admin_init()
{
    if (!scct_expenses_context()) {
        return;
    }
    $CI = &get_instance();
    $method = strtolower($CI->router->fetch_method());
    $id = $CI->uri->segment(4);
    $capabilities = ['delete' => 'delete', 'expense' => 'edit', 'copy' => 'create', 'add_expense_attachment' => 'edit', 'delete_expense_attachment' => 'edit'];
    if ($id !== null && $id !== '' && (isset($capabilities[$method]) || in_array($method, ['convert_to_invoice', 'pdf'], true))) {
        scct_expenses_assert_record($id, $capabilities[$method] ?? null);
    }
    if ($method === 'bulk_action' && is_array($CI->input->post('ids'))) {
        foreach ($CI->input->post('ids') as $expenseId) {
            scct_expenses_assert_record($expenseId, $CI->input->post('mass_delete') ? 'delete' : 'edit');
        }
    }
    if ($method !== 'get_expenses_total' || !$CI->input->post()) {
        return;
    }
    if (!staff_can('view', 'expenses') && !staff_can('view_own', 'expenses')) {
        access_denied('expenses');
    }
    if ($CI->input->post('scct_expenses_workspace') !== '1') {
        return;
    }
    // Render before the legacy per-record summary: one exact decimal aggregate, not five N+1 queries.
    $CI->load->model('currencies_model');
    $CI->load->model('expenses_model');
    $currencies = $CI->currencies_model->get();
    $base = $CI->currencies_model->get_base_currency();
    $requestedCurrency = $CI->input->post('currency');
    $currencyId = is_scalar($requestedCurrency) && ctype_digit((string) $requestedCurrency) ? (int) $requestedCurrency : 0;
    if (!in_array($currencyId, array_map('intval', array_column($currencies, 'id')), true)) {
        $currencyId = (int) $base->id;
    }
    $years = $CI->expenses_model->get_expenses_years();
    if (!in_array(date('Y'), array_column($years, 'year'))) {
        array_unshift($years, ['year' => date('Y')]);
    }
    $data = [
        'currencies' => $currencies,
        '_currency' => $currencyId,
        'expenses_years' => $years,
        'scct_expense_years' => scct_expenses_years($CI->input->post()),
        'scct_expense_summary' => scct_expenses_summary($CI->input->post(), $currencyId),
    ];
    $CI->load->view('admin/expenses/expenses_total_template', $data);
    exit;
}

function scct_expenses_download_guard()
{
    $CI = &get_instance();
    if (strtolower($CI->router->fetch_class()) === 'download' && strtolower($CI->router->fetch_method()) === 'file'
        && $CI->uri->segment(3) === 'expense') {
        if (!is_staff_logged_in()) {
            show_404();
        }
        scct_expenses_assert_record($CI->uri->segment(4));
    }
}

function scct_expenses_tax_sql()
{
    return '(CAST(e.amount AS DECIMAL(24,8)) * (1 + COALESCE(t1.taxrate,0)/100 + COALESCE(t2.taxrate,0)/100))';
}

function scct_expenses_years($data)
{
    $years = isset($data['years']) && is_array($data['years']) ? $data['years'] : [];
    $years = array_values(array_unique(array_map('strval', array_filter($years, function ($year) {
        return is_scalar($year) && preg_match('/^[12][0-9]{3}$/D', (string) $year);
    }))));
    return $years ?: [date('Y')];
}

function scct_expenses_query_scope($data, $currencyId)
{
    $CI = &get_instance();
    $CI->db->from(db_prefix() . 'expenses e')
        ->join(db_prefix() . 'taxes t1', 't1.id=e.tax', 'left')
        ->join(db_prefix() . 'taxes t2', 't2.id=e.tax2', 'left')
        ->where('e.currency', (int) $currencyId);
    $CI->db->where_in('YEAR(e.date)', scct_expenses_years($data));
    foreach (['customer_id' => 'e.clientid', 'project_id' => 'e.project_id'] as $key => $column) {
        if (isset($data[$key]) && is_scalar($data[$key]) && ctype_digit((string) $data[$key]) && (int) $data[$key] > 0) {
            $CI->db->where($column, (int) $data[$key]);
        }
    }
    if (!staff_can('view', 'expenses')) {
        $CI->db->where('e.addedfrom', get_staff_user_id());
    }
}

function scct_expenses_summary($data, $currencyId)
{
    $CI = &get_instance();
    if (!staff_can('view', 'expenses') && !staff_can('view_own', 'expenses')) {
        access_denied('expenses');
    }
    scct_expenses_query_scope($data, $currencyId);
    $total = scct_expenses_tax_sql();
    foreach (scct_expenses_predicates('e') as $key => $predicate) {
        $CI->db->select("COALESCE(SUM(CASE WHEN $predicate THEN $total ELSE 0 END),0) AS {$key}_amount", false);
        $CI->db->select("COUNT(CASE WHEN $predicate THEN 1 END) AS {$key}_count", false);
    }
    $CI->db->select("COALESCE(ROUND(100 * SUM(CASE WHEN e.billable=1 THEN $total ELSE 0 END) / NULLIF(SUM($total),0),1),0) AS billable_ratio", false);
    $row = $CI->db->get()->row_array();
    $summary = ['metrics' => [], 'currency' => get_currency($currencyId), 'billable_ratio' => $row['billable_ratio']];
    foreach (scct_expenses_predicates('e') as $key => $predicate) {
        $summary['metrics'][$key] = ['amount' => $row[$key . '_amount'], 'count' => (int) $row[$key . '_count']];
    }
    return $summary;
}

function scct_expenses_category_spending()
{
    if (!is_admin()) {
        return [];
    }
    $CI = &get_instance();
    $total = scct_expenses_tax_sql();
    return $CI->db->select('e.category, c.name, e.currency, COUNT(*) AS expense_count', false)
        ->select("SUM($total) AS total", false)
        ->from(db_prefix() . 'expenses e')
        ->join(db_prefix() . 'expenses_categories c', 'c.id=e.category')
        ->join(db_prefix() . 'taxes t1', 't1.id=e.tax', 'left')
        ->join(db_prefix() . 'taxes t2', 't2.id=e.tax2', 'left')
        ->where('e.date >=', date('Y') . '-01-01')->where('e.date <', date('Y-m-d', strtotime('+1 day')))
        ->group_by(['e.category', 'c.name', 'e.currency'])->order_by('c.name', 'asc')->get()->result_array();
}

function scct_expenses_receipt($expense)
{
    $name = (string) $expense->attachment;
    $folder = realpath(get_upload_path_by_type('expense') . (int) $expense->expenseid);
    $path = $folder && $name !== '' && basename($name) === $name ? realpath($folder . '/' . $name) : false;
    $exists = $path && strpos($path, $folder . DIRECTORY_SEPARATOR) === 0 && is_file($path) && is_readable($path);
    $mime = $exists ? (new finfo(FILEINFO_MIME_TYPE))->file($path) : '';
    return ['exists' => (bool) $exists, 'mime' => $mime, 'name' => $name];
}
