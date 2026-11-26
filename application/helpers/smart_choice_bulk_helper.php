<?php

defined('BASEPATH') or exit('No direct script access allowed');

function sc_bulk_table_targets()
{
    return ['invoices','estimates','proposals','contracts','projects','estimate_request'];
}
function sc_bulk_checkbox_html($feature, $id)
{
    if (staff_cant('delete', $feature)) return '';
    return '<div class="checkbox checkbox-primary sc-bulk-checkbox-wrap"><input type="checkbox" class="sc-bulk-row" data-feature="'.e($feature).'" value="'.(int)$id.'"><label></label></div>';
}
foreach (sc_bulk_table_targets() as $scFeature) {
    hooks()->add_filter($scFeature . '_table_row_data', function($row, $record) use ($scFeature) {
        $id = $record['id'] ?? $record[db_prefix() . $scFeature . '.id'] ?? null;
        if (staff_can('delete', $scFeature) && isset($row[0]) && is_numeric($id) && (int) $id > 0) {
            $row[0] = sc_bulk_checkbox_html($scFeature, $id) . $row[0];
        }
        return $row;
    }, 10, 2);
}

/** CSRF-protected button for module activate / deactivate / uninstall on the Modules page. */
function sc_module_action_form($action, $systemName, $label)
{
    $CI      = &get_instance();
    $danger  = $action === 'uninstall';
    $confirm = $danger ? ' onsubmit="return confirm_delete();"' : '';

    return '<form method="post" action="' . admin_url('modules/' . $action . '/' . rawurlencode($systemName)) . '" class="sc-module-action-form"' . $confirm . ' style="display:inline">'
        . '<input type="hidden" name="' . e($CI->security->get_csrf_token_name()) . '" value="' . e($CI->security->get_csrf_hash()) . '">'
        . '<button type="submit" class="btn btn-link sc-module-action-btn' . ($danger ? ' text-danger' : '') . '">' . e($label) . '</button></form>';
}

hooks()->add_action('app_admin_footer', function(){
    echo '<script src="'.base_url('assets/js/smart_choice_bulk.js?v=' . filemtime(FCPATH . 'assets/js/smart_choice_bulk.js')).'"></script>';
});
