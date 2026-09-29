<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Blogging
Description: Website-first bilingual blogging, SEO, media, publishing, templates, analytics, and AI assistance for Perfex CRM.
Version: 1.1.6
Author: Smart Choice Contractors USA
Author URI: https://justsmartchoice.com/
Requires at least: 3.4.*
*/

define('PUBLISHX_MODULE_NAME', 'publishx');
define('PUBLISHX_VERSION', '1.1.6');

register_activation_hook(PUBLISHX_MODULE_NAME, 'publishx_module_activation_hook');
register_deactivation_hook(PUBLISHX_MODULE_NAME, 'publishx_module_deactivation_hook');
register_language_files(PUBLISHX_MODULE_NAME, [PUBLISHX_MODULE_NAME]);

hooks()->add_action('admin_init', 'publishx_register_permissions');
hooks()->add_action('admin_init', 'publishx_register_menu');
hooks()->add_action('admin_init', 'publishx_register_settings_section');
hooks()->add_action('app_admin_head', 'publishx_admin_assets');
hooks()->add_action('before_cron_run', 'publishx_scheduled_posts');

$CI = &get_instance();
$CI->load->helper('publishx/publishx');

function publishx_module_activation_hook()
{
    require_once __DIR__ . '/install.php';
}

function publishx_module_deactivation_hook()
{
    // Data and published website files are preserved intentionally.
}

function publishx_register_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'     => _l('permission_view') . ' (' . _l('permission_global') . ')',
            'create'   => _l('permission_create'),
            'edit'     => _l('permission_edit'),
            'delete'   => _l('permission_delete'),
        ],
    ];
    register_staff_capabilities('publishx_posts', $capabilities, _l('publishx_posts'));
    register_staff_capabilities('publishx_categories', $capabilities, _l('publishx_categories'));
    register_staff_capabilities('publishx_websites', $capabilities, _l('publishx_websites'));
    register_staff_capabilities('publishx_reports', $capabilities, _l('publishx_reports'));
    register_staff_capabilities('publishx_themes', $capabilities, _l('publishx_templates'));
}

function publishx_register_menu()
{
    $CI = &get_instance();
    if (!has_permission('publishx_posts', '', 'view') && !has_permission('publishx_posts', '', 'view_own')) {
        return;
    }
    $CI->app_menu->add_sidebar_menu_item('publishx', [
        'slug' => 'publishx', 'name' => _l('publishx'), 'position' => 16, 'icon' => 'fa-solid fa-blog',
    ]);
    $items = [
        ['publishx-posts', 'publishx_posts', 'publishx/posts', 'fa-regular fa-newspaper'],
        ['publishx-categories', 'publishx_categories', 'publishx/categories', 'fa-solid fa-tags'],
        ['publishx-websites', 'publishx_websites', 'publishx/websites', 'fa-solid fa-globe'],
        ['publishx-templates', 'publishx_templates', 'publishx/themes', 'fa-solid fa-palette'],
        ['publishx-media', 'publishx_media_library', 'publishx/media', 'fa-regular fa-images'],
        ['publishx-reports', 'publishx_reports', 'publishx/reports', 'fa-solid fa-chart-line'],
        ['publishx-how-to', 'publishx_how_to', 'publishx/how_to', 'fa-regular fa-circle-question'],
        ['publishx-settings', 'publishx_settings', 'settings?group=publishx-settings', 'fa-solid fa-gear'],
    ];
    foreach ($items as $i=>$item) {
        $CI->app_menu->add_sidebar_children_item('publishx', [
            'slug'=>$item[0], 'name'=>_l($item[1]), 'href'=>admin_url($item[2]), 'position'=>$i+1, 'icon'=>$item[3],
        ]);
    }
}

function publishx_register_settings_section()
{
    if (!is_admin()) {
        return;
    }

    $CI = &get_instance();
    if (!isset($CI->app)) {
        return;
    }

    if (version_compare(get_app_version(), '3.2.0', '<')) {
        $CI->app->add_settings_section_child('other', 'publishx-settings', [
            'name'     => _l('publishx_settings'),
            'view'     => 'publishx/settings_section',
            'position' => 80,
        ]);
        return;
    }

    $CI->app->add_settings_section('publishx-settings', [
        'title'    => _l('publishx_settings'),
        'position' => 80,
        'children' => [
            [
                'name'     => _l('publishx_manage_settings'),
                'view'     => 'publishx/settings_section',
                'icon'     => 'fa-solid fa-blog fa-fw fa-lg',
                'position' => 10,
            ],
        ],
    ]);
}

function publishx_admin_assets()
{
    $CI=&get_instance();
    $uri=$CI->uri->uri_string();
    if (strpos($uri, 'admin/publishx') !== 0) return;
    echo '<link rel="stylesheet" href="' . module_dir_url('publishx', 'assets/css/blogging.css') . '?v=' . PUBLISHX_VERSION . '">';
    echo '<script defer src="' . module_dir_url('publishx', 'assets/js/blogging.js') . '?v=' . PUBLISHX_VERSION . '"></script>';
}

function publishx_scheduled_posts()
{
    $CI=&get_instance();
    $CI->load->model('publishx/publishx_model');
    foreach ($CI->publishx_model->get_due_scheduled_posts() as $post) {
        $CI->publishx_model->updatePost($post['id'], ['status'=>0]);
        $CI->publishx_model->publish_to_website($post['id']);
    }
}
