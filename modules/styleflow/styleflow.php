<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: StyleFlow
Description: Sales document template manager for invoices, estimates, and proposals with independent activation and editable styling.
Version: 1.1.8
Author: Smart Choice Contractors USA / Harold Cabrera
Requires at least: 3.4.*
*/

define('STYLEFLOW_MODULE_NAME', 'styleflow');
define('STYLEFLOW_VERSION', '1.1.8');

/**
 * Remove the legacy StyleFlow migration config shipped by broken 1.1.1-1.1.3
 * builds. That file is not a module migration file; under HMVC it can be merged
 * into CodeIgniter's global migration configuration and trigger Perfex's core
 * database-upgrade gate. This cleanup runs before any StyleFlow controller.
 */
function styleflow_remove_legacy_core_migration_config()
{
    $legacy = __DIR__ . '/config/migration.php';
    if (!is_file($legacy)) {
        return true;
    }

    // Rename first so CodeIgniter can no longer resolve config/migration.php.
    $quarantine = __DIR__ . '/config/migration.styleflow-disabled.php';
    if (@rename($legacy, $quarantine)) {
        @unlink($quarantine);
        clearstatcache(true, $legacy);
        return !is_file($legacy);
    }

    if (@unlink($legacy)) {
        clearstatcache(true, $legacy);
        return !is_file($legacy);
    }

    // Last-resort neutralization for hosts that deny delete/rename but allow write.
    $safe = "<?php\ndefined('BASEPATH') or exit('No direct script access allowed');\n// StyleFlow legacy migration config neutralized.\n";
    if (@file_put_contents($legacy, $safe, LOCK_EX) !== false) {
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($legacy, true);
        }
        return true;
    }

    return false;
}

styleflow_remove_legacy_core_migration_config();

hooks()->add_action('admin_init', 'styleflow_register_settings_section', 5);
hooks()->add_action('admin_init', 'styleflow_module_init', 20);
register_activation_hook(STYLEFLOW_MODULE_NAME, 'styleflow_module_activation_hook');
register_language_files(STYLEFLOW_MODULE_NAME, [STYLEFLOW_MODULE_NAME]);

$CI = &get_instance();
$CI->load->helper(STYLEFLOW_MODULE_NAME . '/styleflow');

function styleflow_module_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

function styleflow_register_settings_section()
{
    $CI = &get_instance();

    if (!staff_can('view', 'settings')) {
        return;
    }

    // Match the direct module-view registration pattern used by working CRM
    // modules. Keeping the section slug as "styleflow" preserves the standard
    // /admin/settings?group=styleflow URL and the CRM's right-side panel.
    if (version_compare(get_app_version(), '3.2.0', '<')) {
        $CI->app->add_settings_section_child('other', 'styleflow', [
            'name'     => _l('styleflow_settings'),
            'view'     => 'styleflow/settings_panel',
            'position' => 75,
        ]);
        return;
    }

    $CI->app->add_settings_section('styleflow', [
        'title'    => _l('styleflow_settings'),
        'position' => 75,
        'children' => [
            [
                'position' => 10,
                'name'     => _l('styleflow_settings'),
                'view'     => 'styleflow/settings_panel',
                'icon'     => 'fa-solid fa-palette fa-fw',
            ],
        ],
    ]);
}

function styleflow_module_init()
{
    $CI = &get_instance();

    register_staff_capabilities('styleflow', [
        'capabilities' => [
            'view' => _l('permission_view'),
            'edit' => _l('permission_edit'),
        ],
    ], _l('styleflow'));

    if (has_permission('styleflow', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('styleflow', [
            'slug' => 'styleflow',
            'name' => _l('styleflow'),
            'position' => 6,
            'icon' => 'fas fa-palette',
            'href' => admin_url('styleflow/manage_templates'),
        ]);

        $CI->app_menu->add_sidebar_children_item('styleflow', [
            'slug' => 'styleflow-manage-templates',
            'name' => _l('styleflow_document_templates'),
            'href' => admin_url('styleflow/manage_templates'),
            'position' => 1,
        ]);
    }

}

// Apply selected StyleFlow colors/layout to customer-facing sales document views.
hooks()->add_action('app_customers_head', 'styleflow_public_document_styles');
hooks()->add_action('app_customers_footer', 'styleflow_public_document_staff_badge');
