<?php

defined('BASEPATH') or exit('No direct script access allowed');

/** Only display prefixes change. Primary keys, sequence values and document URLs do not. */
function sc_numbering_documents()
{
    return [
        'invoice' => ['table' => 'invoices', 'feature' => 'invoices', 'model' => 'invoices_model'],
        'estimate' => ['table' => 'estimates', 'feature' => 'estimates', 'model' => 'estimates_model'],
        'credit_note' => ['table' => 'creditnotes', 'feature' => 'credit_notes', 'model' => 'credit_notes_model'],
        'proposal' => ['table' => 'proposals', 'feature' => 'proposals'],
        'contract' => ['table' => 'contracts', 'feature' => 'contracts'],
    ];
}

function sc_numbering_prefix($type, $id)
{
    $config = sc_numbering_documents()[$type] ?? null;
    if (!$config || !ctype_digit((string) $id) || (int) $id < 1) return '';
    $CI = &get_instance();
    $cached = $CI->app_object_cache->get('sc-number-prefix-' . $type . '-' . (int) $id);
    if (is_array($cached)) return $cached['prefix'] ?? ($type === 'proposal' ? (string) get_option('proposal_number_prefix') : '');
    $table = db_prefix() . $config['table'];
    // Old databases remain readable before the explicit additive migration is run.
    if (!$CI->db->field_exists('prefix', $table)) {
        return $type === 'proposal' ? (string) get_option('proposal_number_prefix') : '';
    }
    $record = $CI->db->select('prefix')->where('id', (int) $id)->get($table)->row();
    if ($record) $CI->app_object_cache->set('sc-number-prefix-' . $type . '-' . (int) $id, ['prefix' => $record->prefix]);
    return $record && $record->prefix !== null
        ? (string) $record->prefix
        : ($type === 'proposal' ? (string) get_option('proposal_number_prefix') : '');
}

function format_contract_number($id)
{
    return sc_numbering_prefix('contract', $id) . (int) $id;
}

/** Share the SQL expression with proposal table search/export and global search. */
function sc_proposal_number_sql()
{
    $CI = &get_instance();
    $table = db_prefix() . 'proposals';
    $default = $CI->db->escape((string) get_option('proposal_number_prefix'));
    $prefix = $CI->db->field_exists('prefix', $table) ? 'COALESCE(' . $table . '.prefix, ' . $default . ')' : $default;
    $padding = max(0, min(50, (int) get_option('number_padding_prefixes')));
    // LPAD truncates IDs longer than its target; PHP str_pad never does.
    return 'CONCAT(' . $prefix . ', LPAD(' . $table . '.id, GREATEST(' . $padding . ', CHAR_LENGTH(' . $table . '.id)), \'0\'))';
}

/** escape_like_str already escapes SQL quotes; do not pass it through db->escape again. */
function sc_numbering_like_pattern($query)
{
    $CI = &get_instance();
    return "'%" . $CI->db->escape_like_str($query) . "%'";
}

function sc_contract_number_sql()
{
    $CI = &get_instance();
    $table = db_prefix() . 'contracts';
    return $CI->db->field_exists('prefix', $table)
        ? "CONCAT(COALESCE({$table}.prefix, ''), {$table}.id)"
        : $table . '.id';
}

function sc_update_document_prefix($type, $id, $prefix)
{
    $config = sc_numbering_documents()[$type] ?? null;
    $failure = ['success' => false, 'message' => _l('invalid_form_data')];
    if (!$config || !ctype_digit((string) $id) || (int) $id < 1) return $failure;
    if (!is_staff_logged_in() || !staff_can('edit', $config['feature'])) {
        return ['success' => false, 'message' => _l('access_denied')];
    }
    if (!is_string($prefix)) return $failure;
    $prefix = trim($prefix);
    // Prefixes are plain text, including punctuation and Unicode; never accept markup/control bytes.
    if (!mb_check_encoding($prefix, 'UTF-8') || mb_strlen($prefix, 'UTF-8') > 50 || preg_match('/[<>\x00-\x1F\x7F]/u', $prefix)) return $failure;
    $CI = &get_instance();
    $table = db_prefix() . $config['table'];
    if (!$CI->db->field_exists('prefix', $table)) return $failure;
    $record = $CI->db->where('id', (int) $id)->get($table)->row();
    if (!$record) return $failure;

    $CI->db->trans_begin();
    $success = $CI->db->where('id', (int) $id)->update($table, ['prefix' => $prefix]);
    $CI->app_object_cache->delete('sc-number-prefix-' . $type . '-' . (int) $id);
    $formatter = 'format_' . $type . '_number';
    $number = $formatter((int) $id);
    if (isset($config['model'])) {
        // Keep the persisted value used by search, exports and API consumers in sync.
        $success = $success && $CI->db->where('id', (int) $id)->update($table, ['formatted_number' => $number]);
    }
    if (!$success || $CI->db->trans_status() === false) {
        $CI->db->trans_rollback();
        $CI->app_object_cache->delete('sc-number-prefix-' . $type . '-' . (int) $id);
        return $failure;
    }
    $CI->db->trans_commit();
    log_activity(ucwords(str_replace('_', ' ', $type)) . ' prefix updated [ID: ' . (int) $id . ']');
    return [
        'success' => true, 'message' => _l('updated_successfully', _l($type)),
        'prefix' => $prefix, 'formatted_number' => $number,
        'heading' => sc_sales_document_heading($type, $number),
    ];
}

function sc_render_prefix_editor($type, $record)
{
    $config = sc_numbering_documents()[$type] ?? null;
    if (!$config || !$record || !staff_can('edit', $config['feature'])) return;
    $CI = &get_instance();
    $CI->load->view('admin/includes/document_prefix_editor', [
        'prefix_type' => $type, 'prefix_id' => (int) $record->id,
        'document_prefix' => sc_numbering_prefix($type, $record->id),
    ]);
}
