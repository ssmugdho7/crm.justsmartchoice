<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice Module Licensing
Description: Owner-controlled company licenses and signed module activation.
Version: 1.0.0
Requires at least: 3.4.1
Author: Smart Choice Contractors USA
*/
require_once __DIR__ . '/helpers/sc_module_licensing_helper.php';
register_language_files('sc_module_licensing', ['sc_module_licensing']);
register_activation_hook('sc_module_licensing', 'scl_install');
function scl_install() { require __DIR__ . '/install.php'; }
hooks()->add_action('admin_init', 'scl_admin_init');
function scl_admin_init()
{
    $CI = &get_instance();
    $caps = [];
    foreach (['view_own', 'view', 'create', 'edit', 'delete', 'manage_settings'] as $cap) {
        $caps[$cap] = _l('scl_permission_' . $cap);
    }
    register_staff_capabilities('sc_module_licensing', ['capabilities' => $caps], _l('scl_title'));
    if (scl_can('view') || scl_can('view_own') || scl_can('manage_settings') || scl_can('create')) {
        $CI->app_menu->add_setup_menu_item('sc-module-licensing', [
            'name' => _l('scl_title'), 'href' => admin_url('sc_module_licensing'), 'position' => 60,
        ]);
    }
}
hooks()->add_action('pre_activate_module', function ($module) {
    $manifest = $module['path'] . 'sc_license.php';
    if (is_file($manifest)) {
        $name = require $manifest;
        if ($name !== ($module['system_name'] ?? '')) {
            show_error(_l('scl_invalid'), 403); exit;
        }
        scl_require($name);
    }
});
