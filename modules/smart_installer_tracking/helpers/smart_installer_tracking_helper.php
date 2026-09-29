<?php

defined('BASEPATH') or exit('No direct script access allowed');

function smart_installer_tracking_label($value, bool $upper = false): string
{
    $text = is_scalar($value) ? (string) $value : '';
    $text = str_replace(['_', '-'], ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text) ?: '';
    $text = trim(ucwords(strtolower($text)));
    return $upper ? strtoupper($text) : $text;
}

function smart_installer_tracking_clean_text($value): string
{
    return smart_installer_tracking_label($value, false);
}

function smart_installer_tracking_title($value): string
{
    return smart_installer_tracking_label($value, true);
}

function smart_installer_tracking_public_url(string $token): string
{
    return site_url('smart-installer-tracking/client/' . rawurlencode($token));
}

function smart_installer_tracking_mask_api_key(string $key): string
{
    $key = trim($key);
    if ($key === '') {
        return '';
    }
    if (strlen($key) <= 8) {
        return str_repeat('*', strlen($key));
    }
    return substr($key, 0, 4) . str_repeat('*', max(0, strlen($key) - 8)) . substr($key, -4);
}

function smart_installer_tracking_google_key(): string
{
    $keys = [
        'smart_installer_tracking_google_maps_api_key',
        'google_api_key',
        'google_maps_api_key',
        'google_map_api_key',
        'perfex_google_api_key',
    ];
    foreach ($keys as $key) {
        $value = trim((string) get_option($key));
        if ($value !== '') {
            return $value;
        }
    }
    return '';
}
