<?php

defined('BASEPATH') or exit('No direct script access allowed');

function ga_valid_measurement_id($value)
{
    return preg_match('/^G-[A-Z0-9]+$/i', trim((string) $value)) === 1;
}

function ga_valid_property_id($value)
{
    return preg_match('/^[0-9]+$/', trim((string) $value)) === 1;
}

function ga_normalize_url($url)
{
    $url = trim((string) $url);
    if ($url !== '' && !preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }
    return filter_var($url, FILTER_VALIDATE_URL) ? rtrim($url, '/') : '';
}

function ga_encrypt_secret($value)
{
    $value = (string) $value;
    if ($value === '') return '';
    if (function_exists('app_encrypt_decrypt')) {
        return app_encrypt_decrypt($value, 'e');
    }
    return base64_encode($value);
}

function ga_decrypt_secret($value)
{
    $value = (string) $value;
    if ($value === '') return '';
    if (function_exists('app_encrypt_decrypt')) {
        $decrypted = app_encrypt_decrypt($value, 'd');
        return $decrypted !== false ? $decrypted : '';
    }
    $decoded = base64_decode($value, true);
    return $decoded !== false ? $decoded : '';
}

function ga_tracking_snippet($measurementId)
{
    $measurementId = strtoupper(trim((string) $measurementId));
    if (!ga_valid_measurement_id($measurementId)) return '';
    return "<!-- Google tag (gtag.js) -->\n<script async src=\"https://www.googletagmanager.com/gtag/js?id={$measurementId}\"></script>\n<script>\nwindow.dataLayer = window.dataLayer || [];\nfunction gtag(){dataLayer.push(arguments);}\ngtag('js', new Date());\ngtag('config', '{$measurementId}', {'anonymize_ip': true});\n</script>";
}

function ga_audit($action, $details = '')
{
    $CI = &get_instance();
    if (!$CI->db->table_exists(db_prefix() . 'ga_audit_log')) return;
    $CI->db->insert(db_prefix() . 'ga_audit_log', [
        'staff_id'     => get_staff_user_id() ?: null,
        'action'       => substr((string) $action, 0, 100),
        'details'      => is_string($details) ? $details : json_encode($details),
        'ip_address'   => $CI->input->ip_address(),
        'date_created' => date('Y-m-d H:i:s'),
    ]);
}
