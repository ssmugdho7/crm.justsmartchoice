<?php
/**
 * Module Name: Cabinet Maker
 * Description: Integrated kitchen and cabinet design, visualization, cut lists, nesting, costing, customer sharing, and project collaboration.
 * Version: 1.3.0
 * Requires at least: 3.4.0
 * Author: Smart Choice Contractors USA
 * Creator: Smart Choice Development
 * Company: Smart Choice Contractors USA
 */
defined('BASEPATH') or exit('No direct script access allowed');

define('CABINET_MAKER_MODULE_NAME', 'cabinet_maker');
define('CABINET_MAKER_VERSION', '1.3.0');

register_activation_hook(CABINET_MAKER_MODULE_NAME, 'cabinet_maker_module_activate');
register_deactivation_hook(CABINET_MAKER_MODULE_NAME, 'cabinet_maker_module_deactivate');
register_uninstall_hook(CABINET_MAKER_MODULE_NAME, 'cabinet_maker_module_uninstall');
register_language_files(CABINET_MAKER_MODULE_NAME, [CABINET_MAKER_MODULE_NAME]);

hooks()->add_action('admin_init', 'cabinet_maker_admin_init');
hooks()->add_action('app_admin_assets', 'cabinet_maker_admin_assets');
hooks()->add_action('app_client_assets', 'cabinet_maker_customer_assets');
hooks()->add_action('after_cron_run', 'cabinet_maker_cron');
hooks()->add_action('app_admin_footer', 'cabinet_maker_admin_footer');

function cabinet_maker_module_activate()
{
    require_once __DIR__ . '/install.php';
}

function cabinet_maker_module_deactivate()
{
    update_option('cabinet_maker_last_deactivated', date('Y-m-d H:i:s'));
}

function cabinet_maker_module_uninstall()
{
    require_once __DIR__ . '/uninstall.php';
}

function cabinet_maker_admin_init()
{
    $CI = &get_instance();
    $CI->load->helper(CABINET_MAKER_MODULE_NAME . '/cabinet_maker');

    register_staff_capabilities('cabinet_maker', [
        'capabilities' => [
            'view_own'      => _l('permission_view_own'),
            'view'          => _l('permission_view'),
            'create'        => _l('permission_create'),
            'edit'          => _l('permission_edit'),
            'delete'        => _l('permission_delete'),
            'approve'       => _l('cabinet_maker_permission_approve'),
            'share'         => _l('cabinet_maker_permission_share'),
            'manufacturing' => _l('cabinet_maker_permission_manufacturing'),
            'settings'      => _l('cabinet_maker_permission_settings'),
        ],
    ], _l('cabinet_maker'));


    if (has_permission('cabinet_maker', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('cabinet-maker', [
            'name'     => _l('cabinet_maker'),
            'icon'     => 'fa fa-cubes',
            'position' => 36,
        ]);
        $CI->app_menu->add_sidebar_children_item('cabinet-maker', [
            'slug'     => 'cabinet-maker-designs',
            'name'     => _l('cabinet_maker_designs'),
            'href'     => admin_url('cabinet_maker'),
            'icon'     => 'fa fa-cube',
            'position' => 1,
        ]);
        $CI->app_menu->add_sidebar_children_item('cabinet-maker', [
            'slug'     => 'cabinet-maker-materials',
            'name'     => _l('cabinet_maker_materials'),
            'href'     => admin_url('cabinet_maker/materials'),
            'icon'     => 'fa fa-clone',
            'position' => 2,
        ]);
        $CI->app_menu->add_sidebar_children_item('cabinet-maker', [
            'slug'     => 'cabinet-maker-vendors',
            'name'     => _l('cabinet_maker_vendors'),
            'href'     => admin_url('cabinet_maker/vendors'),
            'icon'     => 'fa fa-truck',
            'position' => 3,
        ]);
        $CI->app_menu->add_sidebar_children_item('cabinet-maker', [
            'slug'     => 'cabinet-maker-offcuts',
            'name'     => _l('cabinet_maker_offcuts'),
            'href'     => admin_url('cabinet_maker/offcuts'),
            'icon'     => 'fa fa-recycle',
            'position' => 4,
        ]);
        $CI->app_menu->add_sidebar_children_item('cabinet-maker', [
            'slug'     => 'cabinet-maker-health',
            'name'     => _l('cabinet_maker_health_check'),
            'href'     => admin_url('cabinet_maker/health_check'),
            'icon'     => 'fa fa-heartbeat',
            'position' => 5,
        ]);

        if (isset($CI->app_tabs) && method_exists($CI->app_tabs, 'add_project_tab')) {
            $CI->app_tabs->add_project_tab('cabinet_maker', [
                'name'     => _l('cabinet_maker'),
                'icon'     => 'fa fa-cubes',
                'view'     => 'cabinet_maker/project/tab',
                'position' => 68,
            ]);
        }
    }

    if (has_permission('cabinet_maker', '', 'settings')) {
        $CI->app_menu->add_setup_menu_item('cabinet-maker-settings', [
            'name'     => _l('cabinet_maker'),
            'href'     => admin_url('cabinet_maker/settings'),
            'position' => 70,
        ]);
    }
}

function cabinet_maker_maybe_register_email_templates()
{
    if (get_option('cabinet_maker_email_templates_registered') === '1') {
        return;
    }

    try {
        cabinet_maker_register_email_templates();
        update_option('cabinet_maker_email_templates_registered', '1');
    } catch (Throwable $e) {
        log_message('error', 'Cabinet Maker email template registration: ' . $e->getMessage());
    }
}

function cabinet_maker_safe_initialize()
{
    // Reserved for non-destructive runtime checks. Database installation runs only in the activation hook.
    return true;
}

function cabinet_maker_admin_assets()
{
    $uri = uri_string();
    if (strpos($uri, 'cabinet_maker') === false && strpos($uri, 'projects/view') === false) {
        return;
    }
    $CI = &get_instance();
    $CI->app_css->add('cabinet-maker-css', module_dir_url(CABINET_MAKER_MODULE_NAME, 'assets/css/cabinet_maker.css'));
    $CI->app_scripts->add('cabinet-maker-js', module_dir_url(CABINET_MAKER_MODULE_NAME, 'assets/js/cabinet_maker.js'));
}

function cabinet_maker_customer_assets()
{
    if (strpos(uri_string(), 'cabinet-maker/') === false) {
        return;
    }
    $CI = &get_instance();
    if (isset($CI->app_css)) {
        $CI->app_css->add('cabinet-maker-client-css', module_dir_url(CABINET_MAKER_MODULE_NAME, 'assets/css/cabinet_maker.css'));
    }
    if (isset($CI->app_scripts)) {
        $CI->app_scripts->add('cabinet-maker-client-js', module_dir_url(CABINET_MAKER_MODULE_NAME, 'assets/js/cabinet_maker.js'));
    }
}

function cabinet_maker_cron()
{
    $CI = &get_instance();
    $CI->load->helper(CABINET_MAKER_MODULE_NAME . '/cabinet_maker');
    if (!$CI->db->table_exists(cabinet_maker_table('shares'))) {
        return;
    }
    $CI->load->model(CABINET_MAKER_MODULE_NAME . '/Cabinet_maker_model');
    $CI->Cabinet_maker_model->expire_old_share_links();
}


function cabinet_maker_admin_footer()
{
    if (!is_admin() || strpos(uri_string(), 'admin/modules') === false) {
        return;
    }
    $downloadUrl = admin_url('cabinet_maker/download_module');
    echo '<script>(function(){var rows=document.querySelectorAll("table tbody tr");for(var i=0;i<rows.length;i++){var row=rows[i];if(row.textContent.indexOf("Cabinet Maker")===-1){continue;}var area=row.querySelector("td:last-child")||row; if(area.querySelector(".cabinet-maker-download")){return;}var a=document.createElement("a");a.className="btn btn-default btn-xs cabinet-maker-download";a.href=' . json_encode($downloadUrl) . ';a.innerHTML="<i class=\\"fa fa-download\\"></i> Download";area.appendChild(document.createTextNode(" "));area.appendChild(a);return;}})();</script>';
}
