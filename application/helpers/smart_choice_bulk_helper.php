<?php

defined('BASEPATH') or exit('No direct script access allowed');

function sc_bulk_table_targets()
{
    return ['invoices','estimates','proposals','contracts'];
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

hooks()->add_action('app_admin_footer', function(){
    echo '<script src="'.base_url('assets/js/smart_choice_bulk.js?v=430').'"></script>';
});
