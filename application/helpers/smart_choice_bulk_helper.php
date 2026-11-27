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
/** Reuse the native row selector, preserving its IDs, labels and handlers. */
function sc_bulk_reuse_row_checkbox($html, $id, array $classes, $feature = null)
{
    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>');
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    foreach ($dom->getElementsByTagName('input') as $input) {
        if (strtolower($input->getAttribute('type')) !== 'checkbox' || $input->getAttribute('value') !== (string) (int) $id) continue;
        $existing = preg_split('/\s+/', trim($input->getAttribute('class')), -1, PREG_SPLIT_NO_EMPTY);
        $input->setAttribute('class', implode(' ', array_unique(array_merge($existing, $classes))));
        if ($feature !== null) $input->setAttribute('data-feature', $feature);
        // Replace only the matching input; never reserialize customer text or the row.
        $replaced = false;
        return preg_replace_callback('/<input\b[^>]*>/i', function ($match) use ($id, $dom, $input, &$replaced) {
            if ($replaced) return $match[0];
            $probe = new DOMDocument();
            @$probe->loadHTML($match[0]);
            $node = $probe->getElementsByTagName('input')->item(0);
            if ($node && strtolower($node->getAttribute('type')) === 'checkbox' && $node->getAttribute('value') === (string) (int) $id) { $replaced = true; return $dom->saveHTML($input); }
            return $match[0];
        }, $html);
    }
    return null;
}
foreach (sc_bulk_table_targets() as $scFeature) {
    hooks()->add_filter($scFeature . '_table_row_data', function($row, $record) use ($scFeature) {
        $id = $record['id'] ?? $record[db_prefix() . $scFeature . '.id'] ?? null;
        if (staff_can('delete', $scFeature) && isset($row[0]) && is_numeric($id) && (int) $id > 0) {
            $reused = sc_bulk_reuse_row_checkbox($row[0], $id, ['sc-bulk-row'], $scFeature);
            $row[0] = $reused ?? (sc_bulk_checkbox_html($scFeature, $id) . $row[0]);
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
