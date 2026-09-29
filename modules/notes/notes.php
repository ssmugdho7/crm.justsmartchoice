<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Notes
Description: Centralized Smart Choice note management for staff, customers, leads, contracts, proposals, invoices, estimates, tickets, and projects with priority, branded colors, attachments, import, export, and filtering.
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
Version: 1.2.5
*/

define('NOTES_MODULE_NAME', 'notes');
define('NOTES_UPLOAD_FOLDER', FCPATH . 'modules/notes/uploads/');

register_language_files(NOTES_MODULE_NAME, [NOTES_MODULE_NAME]);

register_activation_hook(NOTES_MODULE_NAME, 'notes_module_activation_hook');

function notes_module_activation_hook()
{
    $CI = &get_instance();

    try {
        if (!is_dir(NOTES_UPLOAD_FOLDER)) {
            @mkdir(NOTES_UPLOAD_FOLDER, 0755, true);
        }

        if (is_dir(NOTES_UPLOAD_FOLDER) && !file_exists(NOTES_UPLOAD_FOLDER . 'index.html')) {
            @file_put_contents(NOTES_UPLOAD_FOLDER . 'index.html', '');
        }

        require_once __DIR__ . '/install.php';
    } catch (Throwable $exception) {
        log_message('error', 'Notes activation repair failed: ' . $exception->getMessage());
        // Do not block Perfex from activating the module. Health/database repair remains available.
    }
}

hooks()->add_action('admin_init', 'note_module_menu_items');

function note_module_menu_items()
{
    $CI = &get_instance();

    if (!(is_admin() || staff_can('view', 'note_manage') || staff_can('view_global', 'note_manage') || staff_can('view_own', 'note_manage'))) {
        return;
    }

    // Use one canonical menu slug. If an older embedded Notes link exists, replace it.
    if (method_exists($CI->app_menu, 'remove_sidebar_menu_item')) {
        foreach (['notes', 'notes_manage', 'smart_choice_notes'] as $slug) {
            $CI->app_menu->remove_sidebar_menu_item($slug);
        }
    }

    $CI->app_menu->add_sidebar_menu_item('notes', [
        'href'     => admin_url('notes/note'),
        'name'     => _l('notes_smart_choice_notes'),
        'position' => 50,
        'icon'     => 'fa-regular fa-note-sticky',
    ]);

    // Match the working Appointly pattern: expose a direct, functional
    // module Settings entry while keeping the existing Notes list untouched.
    if (is_admin()) {
        $CI->app_menu->add_sidebar_children_item('notes', [
            'slug'     => 'notes-settings',
            'name'     => _l('settings'),
            'href'     => admin_url('settings?group=notes_settings'),
            'position' => 90,
            'icon'     => 'fa-solid fa-sliders',
        ]);
    }
}

hooks()->add_action('admin_init', 'note_module_manage_permission');

function note_module_manage_permission()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'        => _l('permission_view') . ' (' . _l('permission_own') . ')',
        'view_global' => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'create'      => _l('permission_create'),
        'edit'        => _l('permission_edit'),
        'delete'      => _l('permission_delete'),
    ];

    register_staff_capabilities('note_manage', $capabilities, _l('notes_smart_choice_notes'));
}



hooks()->add_action('admin_init', 'notes_register_settings_section');

function notes_register_settings_section()
{
    $CI = &get_instance();

    if (!is_admin()) {
        return;
    }

    if (version_compare(get_app_version(), '3.2.0', '<')) {
        $CI->app->add_settings_section_child('other', 'notes_settings', [
            'id'                    => 'notes_settings',
            'name'                  => _l('notes_settings_manage'),
            'view'                  => 'notes/settings',
            'icon'                  => 'fa-regular fa-note-sticky fa-fw fa-lg',
            'position'              => 65,
            'without_submit_button' => true,
        ]);
    } else {
        // The explicit child ID is required. Without it, Perfex derives the
        // basename "settings", which the customized CRM settings screen hides.
        $CI->app->add_settings_section('notes-settings', [
            'title'    => _l('notes_settings'),
            'position' => 65,
            'children' => [
                [
                    'id'                    => 'notes_settings',
                    'name'                  => _l('notes_settings_manage'),
                    'view'                  => 'notes/settings',
                    'icon'                  => 'fa-regular fa-note-sticky fa-fw fa-lg',
                    'position'              => 10,
                    'without_submit_button' => true,
                ],
            ],
        ]);
    }
}

hooks()->add_action('app_admin_footer', function () {
    if (is_admin() || staff_can('note_manage', 'note_manage') || staff_can('view', 'note_manage') || staff_can('view_global', 'note_manage')) {
        echo '<script src="' . base_url('modules/notes/assets/perfex_notes.js?v=122') . '"></script>';
    }
});

hooks()->add_filter('project_tabs', function ($tabs) {
    $tabs['note_project_notes'] = [
        'slug'     => 'note_project_notes',
        'name'     => _l('project_note_list'),
        'icon'     => 'fa-regular fa-note-sticky',
        'view'     => 'notes/v_project_notes',
        'position' => 54,
    ];

    return $tabs;
});

hooks()->add_action('after_invoice_preview_template_rendered', function () {
    echo '<script>perfex_note_set_note_tab();</script>';
});

hooks()->add_action('after_proposal_view_as_client_link', function () {
    echo '<script>perfex_note_set_note_tab();</script>';
});

hooks()->add_action('after_estimate_view_as_client_link', function () {
    echo '<script>perfex_note_set_note_tab();</script>';
});

// Load Notes styling only on Notes pages so it cannot affect native CRM tables.
hooks()->add_action('app_admin_head', function () {
    $CI = &get_instance();
    $uri = method_exists($CI->uri, 'uri_string') ? $CI->uri->uri_string() : '';
    if (strpos($uri, 'admin/notes') !== false) {
        echo '<link rel="stylesheet" href="' . module_dir_url('notes', 'assets/smart_choice_standard.css?v=122') . '">';
    }
});
