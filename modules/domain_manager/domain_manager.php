<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Name: Domain Manager
 * Description: Manage domains with expiry tracking and client/project linking.
 * Version: 1.0.0
 * Requires at least: 3.0.*
 * Author: Hopperstack
 * Author URI: https://codecanyon.net/user/hopperstack
 */

define('DOMAIN_MANAGER_MODULE_NAME', 'domain_manager');
define('VERSION_DOMAIN_MANAGER', 100);
define('DOMAIN_MANAGER_MODULE_NAME_ITEM_ID', 'Domain Manager');

// Hooks
hooks()->add_action('admin_init', 'domain_manager_init_menu_items');
hooks()->add_action('admin_init', 'domain_manager_define_permissions');
hooks()->add_filter('module_domain_manager_action_links', 'domain_manager_add_action_links');
hooks()->add_filter('before_start_render_content', 'domain_manager_display_verification_warning');

$CI = &get_instance();

// Register language files
register_language_files(DOMAIN_MANAGER_MODULE_NAME, [DOMAIN_MANAGER_MODULE_NAME]);

// Load helper functions
$CI->load->helper(DOMAIN_MANAGER_MODULE_NAME . '/domain_manager');

/**
 * Initialize Domain Manager module menu items.
 */
function domain_manager_init_menu_items()
{
    $CI = &get_instance();
    
    if (has_permission(DOMAIN_MANAGER_MODULE_NAME, '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item(DOMAIN_MANAGER_MODULE_NAME, [
            'name'     => _l('domain_manager'),
            'icon'     => 'fa fa-server',
            'href'     => admin_url('domain_manager'),
            'position' => 30,
        ]);
    }

    if (has_permission(DOMAIN_MANAGER_MODULE_NAME, '', 'view')) {
        $CI->app_tabs->add_project_tab('domain_manager_projects', [
            'name'     => _l('domain_manager_projects'),
            'icon'     => 'fa fa-server',
            'view'     => 'domain_manager/admin/project_domain_manager',
            'position' => 10,
        ]);
    }

    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item(DOMAIN_MANAGER_MODULE_NAME, [
            'slug'     => 'domain_manager_setting',
            'name'     => _l('domain_manager_setting'),
            'href'     => admin_url('domain_manager/setting'),
            'position' => 35,
        ]);
    }

    $CI->app_tabs->add_customer_profile_tab('domain_manager_projects', [
        'name'     => _l('domain_manager_projects'),
        'icon'     => 'fa fa-server',
        'view'     => 'domain_manager/admin/client_domain_manager',
        'position' => 10,
    ]);
}

/**
 * Module activation hook.
 */
register_activation_hook(DOMAIN_MANAGER_MODULE_NAME, 'domain_manager_activate_module');

function domain_manager_activate_module()
{
    require_once __DIR__ . '/install.php';
}

/**
 * Display purchase code verification warning.
 */
function domain_manager_display_verification_warning()
{
    if (get_option('domain_manager_purchase_is_valid') != 1 && is_admin()) {
        echo '<div class="col-lg-12 mt-2">';
        echo '    <div class="alert alert-warning">';
        echo '        <h4>' . _l('domain_manager') . ' - ' . _l('verify_purchase_before_proceeding') . '</h4>';
        echo '        <p><a href="' . admin_url('domain_manager/setting') . '">' . _l('click_here_to_verify') . '</a></p>';
        echo '    </div>';
        echo '</div>';
    }
}

/**
 * Define module permissions.
 */
function domain_manager_define_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view'   => _l('permission_view') . ' (' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    register_staff_capabilities(DOMAIN_MANAGER_MODULE_NAME, $capabilities, _l('domain_manager'));
}

/**
 * Add settings link in module list.
 *
 * @param array $actions Current actions.
 * @return array
 */
function domain_manager_add_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('domain_manager/setting') . '">' . _l('settings') . '</a>';
    return $actions;
}
