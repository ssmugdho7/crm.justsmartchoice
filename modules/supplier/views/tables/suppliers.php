<?php defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    '1',
    'supplier_name',
    'website',
    'phone',
    'email',
    'trade',
    'short_description',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'suppliers.id and rel_type="supplier" ORDER by tag_order ASC) as tags',
    'id',
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'suppliers';
$where        = [];
$join         = [];
$CI = &get_instance();
$trade = $CI->input->post('supplier_trade');
$location = (int) $CI->input->post('supplier_location');
$visibility = $CI->input->post('supplier_visibility');

if (is_string($trade) && $trade !== '') {
    $where[] = 'AND trade = ' . $CI->db->escape($trade);
}
if ($location > 0) {
    $where[] = 'AND location_id = ' . $location;
}
if (in_array($visibility, ['public', 'private'], true)) {
    $where[] = 'AND only_me = ' . (int) ($visibility === 'private');
}
$where[] = 'AND ' . $CI->supplier_model->visibility_condition();
$additional_select = ['id', 'supplier_type', 'registration_url', 'notes', 'location_id', 'only_me', 'staff_id', 'created_date'];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additional_select);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $id = (int)$aRow['id'];
    $url = trim((string)$aRow['website']);
    $href = $url !== '' ? prep_url($url) : '#';
    $phone = trim((string)$aRow['phone']);
    $email = trim((string)$aRow['email']);
    $phone_digits = preg_replace('/[^0-9+]/', '', $phone);

    $row = [];
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $id . '"><label></label></div>';
    $row[] = '<div class="supplier-one-line"><strong>' . html_escape($aRow['supplier_name']) . '</strong>' . (!empty($aRow['supplier_type']) ? ' <span class="supplier-mini-badge">' . html_escape($aRow['supplier_type']) . '</span>' : '') . '</div>';
    $row[] = $url !== '' ? '<a class="supplier-one-line supplier-link" target="_blank" href="' . html_escape($href) . '">' . html_escape(preg_replace('~^https?://~', '', $url)) . '</a>' : '';
    $row[] = $phone !== '' ? '<a class="supplier-one-line supplier-phone" href="tel:' . html_escape($phone_digits) . '"><i class="fa fa-phone"></i> ' . html_escape($phone) . '</a>' : '';
    $row[] = $email !== '' ? '<a class="supplier-one-line" href="mailto:' . html_escape($email) . '"><i class="fa fa-envelope"></i> ' . html_escape($email) . '</a>' : '';
    $row[] = '<span class="supplier-mini-badge supplier-trade-badge">' . html_escape($aRow['trade']) . '</span>';
    $row[] = '<div class="supplier-desc-one-line" title="' . html_escape($aRow['notes']) . '">' . html_escape($aRow['short_description']) . '</div>';
    $row[] = '<div class="supplier-tags-one-line">' . render_tags($aRow['tags']) . '</div>';
    $actions = '<div class="supplier-actions-cell">';
    if (has_permission('supplier', '', 'edit')) {
        $actions .= '<a class="btn btn-default btn-xs" href="#" onclick="init_supplier_modal(' . $id . '); return false;" aria-label="' . html_escape(_l('edit')) . '"><i class="fa fa-pencil"></i></a> ';
    }
    $actions .= '<a class="btn btn-default btn-xs" target="_blank" href="' . html_escape($href) . '"><i class="fa fa-external-link"></i></a> ';
    if (has_permission('supplier', '', 'view')) {
        $actions .= '<a class="btn btn-default btn-xs" href="#" onclick="supplierEmailModal(' . $id . '); return false;" title="' . _l('supplier_send_email') . '"><i class="fa fa-envelope"></i></a> ';
    }
    if (has_permission('supplier', '', 'delete')) {
        $actions .= '<a class="btn btn-danger btn-xs _delete" href="' . admin_url('supplier/delete/' . $id) . '"><i class="fa fa-trash"></i></a>';
    }
    $actions .= '<div class="supplier-hidden-meta">' . _l('supplier_location') . ': ' . (int)$aRow['location_id'] . '<br>' . _l('supplier_created_date') . ': ' . _dt($aRow['created_date']) . '</div>';
    $actions .= '</div>';
    $row[] = $actions;
    $row['DT_RowClass'] = 'has-row-options supplier-row-compact';
    $output['aaData'][] = $row;
}
