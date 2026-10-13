<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(__DIR__) . '/libraries/Sc_license_crypto.php';

function scl_config()
{
    static $config;
    if ($config !== null) { return $config; }
    $config = [];
    // Set this environment variable in the host configuration; the file stays outside public_html.
    $path = getenv('SC_LICENSING_CONFIG');
    if ($path && is_file($path)) {
        $root = realpath(FCPATH);
        $real = realpath($path);
        if ($real && scl_private_path($real) && is_readable($real)) {
            $loaded = require $real;
            if (is_array($loaded)) { $config = $loaded; }
        }
    }
    return $config;
}

function scl_issuer()
{
    $c = scl_config();
    return ($c['mode'] ?? '') === 'issuer' && !empty($c['private_key_file'])
        && scl_private_path($c['private_key_file']) && is_readable($c['private_key_file']);
}

function scl_private_path($path)
{
    $real = realpath($path);
    $root = realpath(FCPATH);
    return $real && $root && strpos($real, $root . DIRECTORY_SEPARATOR) !== 0
        && strpos(str_replace('\\', '/', $real), '/public_html/') === false;
}

function scl_can($capability)
{
    return is_staff_logged_in() && (is_admin() || has_permission('sc_module_licensing', '', $capability));
}

function scl_licensed($module)
{
    static $memo = [];
    if (array_key_exists($module, $memo)) { return $memo[$module]; }
    $CI = &get_instance();
    if (!$CI->app_modules->is_active('sc_module_licensing')) { return $memo[$module] = false; }
    $CI->load->model('sc_module_licensing/sc_license_model');
    return $memo[$module] = $CI->sc_license_model->valid($module);
}

function scl_require($module)
{
    if (!scl_licensed($module)) { show_error(_l('scl_activation_required'), 403); exit; }
}
