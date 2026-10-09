<?php defined('BASEPATH') or exit('No direct script access allowed');

function sc_sales_links_get($rel_type, $rel_id)
{
    $CI = &get_instance();
    $table = db_prefix() . 'sc_sales_links';
    if (!$CI->db->table_exists($table)) {
        return [];
    }
    return $CI->db->where('rel_type', $rel_type)->where('rel_id', (int)$rel_id)->order_by('sort_order', 'asc')->order_by('id', 'asc')->get($table)->result_array();
}

function sc_sales_links_public_html($rel_type, $rel_id)
{
    $links = sc_sales_links_get($rel_type, $rel_id);
    if (!$links) {
        return '';
    }
    $html = '<div class="smart-choice-sales-links"><hr><p class="bold mbot10"><i class="fa fa-link"></i> ' . html_escape(_l('sc_related_links')) . '</p>';
    foreach ($links as $link) {
        $label = trim((string)$link['title']) !== '' ? $link['title'] : $link['url'];
        $html .= '<div class="mbot8"><a href="' . html_escape($link['url']) . '" target="_blank" rel="noopener noreferrer"><i class="fa fa-external-link"></i> ' . html_escape($label) . '</a></div>';
    }
    return $html . '</div>';
}


function sc_sales_attachment_preview_url(array $attachment): string
{
    if (!empty($attachment['external']) && !empty($attachment['external_link'])) {
        return (string) $attachment['external_link'];
    }

    $identifier = !empty($attachment['attachment_key']) ? $attachment['attachment_key'] : ($attachment['id'] ?? '');
    if ($identifier === '') {
        return '';
    }

    return site_url('download/file/sales_attachment/' . rawurlencode((string) $identifier)) . '?preview=1';
}

function sc_sales_attachment_download_url(array $attachment): string
{
    if (!empty($attachment['external']) && !empty($attachment['external_link'])) {
        return (string) $attachment['external_link'];
    }

    $identifier = !empty($attachment['attachment_key']) ? $attachment['attachment_key'] : ($attachment['id'] ?? '');
    return $identifier === '' ? '' : site_url('download/file/sales_attachment/' . rawurlencode((string) $identifier));
}

/** Check storage before embedding an attachment in a customer document. */
function sc_sales_attachment_available(array $attachment): bool
{
    if (!empty($attachment['external'])) {
        return !empty($attachment['external_link']);
    }
    $type = (string) ($attachment['rel_type'] ?? '');
    $name = (string) ($attachment['file_name'] ?? '');
    $id = (int) ($attachment['rel_id'] ?? 0);
    if (!in_array($type, ['proposal', 'estimate', 'invoice'], true) || $id <= 0 || $name === '') {
        return false;
    }
    $path = get_upload_path_by_type($type) . $id . '/' . $name;
    // Match the download handler's configured storage path, including custom storage hooks.
    $path = hooks()->apply_filters('download_file_path', $path, [
        'folder' => 'sales_attachment',
        'attachmentid' => (string) (!empty($attachment['attachment_key']) ? $attachment['attachment_key'] : ($attachment['id'] ?? '')),
    ]);
    return is_string($path) && is_file($path) && is_readable($path);
}

function sc_sales_attachment_unavailable_html(array $attachment): string
{
    return '<div class="col-md-12 mbot15 sc-attachment-unavailable"><div class="alert alert-warning">'
        . '<p class="bold">' . html_escape((string) ($attachment['file_name'] ?? '')) . '</p>'
        . '<p>' . html_escape(_l('sc_attachment_unavailable')) . '</p></div></div>';
}

// Sales-document translation controls were retired in CRM 4.2.0.
// Customer language remains the native Perfex English/Spanish profile language.

function sc_sales_customer_attachments($relType, $relId)
{
    $CI = &get_instance();
    if (!in_array($relType, ['proposal','estimate','invoice'], true) || (int)$relId <= 0) { return []; }
    return $CI->db->where('rel_type', $relType)->where('rel_id', (int)$relId)->where('visible_to_customer', 1)->order_by('dateadded', 'desc')->get(db_prefix().'files')->result_array();
}

function sc_attach_sales_files_to_mail_template($template, $relType, $relId)
{
    $CI = &get_instance();
    if (!$CI->input->post('attach_uploaded_files') || !in_array($relType, ['proposal','estimate','invoice'], true)) { return; }
    $rows = sc_sales_customer_attachments($relType, (int)$relId);
    $base = rtrim(get_upload_path_by_type($relType), '/\\') . DIRECTORY_SEPARATOR . (int)$relId . DIRECTORY_SEPARATOR;
    foreach ($rows as $row) {
        if (!empty($row['external'])) { continue; }
        $name = basename((string)($row['file_name'] ?? ''));
        $path = $base . $name;
        if ($name === '' || !is_file($path) || !is_readable($path)) { continue; }
        $template->add_attachment(['attachment'=>$path,'filename'=>$name,'type'=>(string)($row['filetype'] ?? get_mime_by_extension($name)),'read'=>true]);
    }
}
