<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Links
Module URI: https://justsmartchoice.com/webdeveloper.php
Description: Smart Choice Links combines the old Favorite Links workflow and the Smart Choice quick-link workflow into one clean module. It adds one star on the left side and one star on the right side of the CRM top search bar, organized shortcut lists, categories, icons, staff/role visibility, internal CRM links, external links, menu search helpers, Setup settings, English/Spanish language support, and a built-in usage guide. Install this module and remove duplicate Favorite Links folders to avoid confusion.
Version: 1.2.0
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

define('SMART_CHOICE_LINKS_MODULE_NAME', 'smart_choice_links');
define('SMART_CHOICE_LINKS_TABLE', db_prefix() . 'smart_choice_links');
define('SMART_CHOICE_LINKS_VERSION', '1.2.0');

register_activation_hook(SMART_CHOICE_LINKS_MODULE_NAME, 'smart_choice_links_activation_hook');
register_deactivation_hook(SMART_CHOICE_LINKS_MODULE_NAME, 'smart_choice_links_deactivation_hook');
register_uninstall_hook(SMART_CHOICE_LINKS_MODULE_NAME, 'smart_choice_links_uninstall_hook');

register_language_files(SMART_CHOICE_LINKS_MODULE_NAME, [SMART_CHOICE_LINKS_MODULE_NAME]);

hooks()->add_action('admin_init', 'smart_choice_links_register_permissions', 5);

hooks()->add_action('admin_init', 'smart_choice_links_admin_init');
hooks()->add_action('app_admin_head', 'smart_choice_links_admin_head');
hooks()->add_action('app_admin_footer', 'smart_choice_links_admin_footer');
hooks()->add_filter('module_smart_choice_links_action_links', 'smart_choice_links_action_links');

function smart_choice_links_activation_hook()
{
    require_once(__DIR__ . '/install.php');
    update_option('smart_choice_links_enabled', '1');
    update_option('smart_choice_links_show_topbar', '1');
    update_option('smart_choice_links_show_left_star', '1');
    update_option('smart_choice_links_show_right_star', '1');
    update_option('smart_choice_links_enable_menu_search', '1');
    update_option('smart_choice_links_version', SMART_CHOICE_LINKS_VERSION);
}

function smart_choice_links_deactivation_hook()
{
    update_option('smart_choice_links_enabled', '0');
}

function smart_choice_links_uninstall_hook()
{
    require_once(__DIR__ . '/uninstall.php');
}

function smart_choice_links_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('smart_choice_links/settings') . '">' . _l('settings') . '</a>';
    $actions[] = '<a href="' . admin_url('smart_choice_links') . '">' . _l('smart_choice_links_manage') . '</a>';
    return $actions;
}

function smart_choice_links_register_permissions()
{
    register_staff_capabilities('smart_choice_links', [
        'view_own' => _l('permission_view_own'),
        'view'     => _l('permission_view'),
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ], _l('smart_choice_links'));
}

function smart_choice_links_user_can_manage()
{
    return is_admin() || has_permission('smart_choice_links', '', 'view') || has_permission('smart_choice_links', '', 'view_own');
}

function smart_choice_links_user_can_edit()
{
    return is_admin() || has_permission('smart_choice_links', '', 'edit');
}

function smart_choice_links_admin_init()
{
    $CI = &get_instance();

    if (smart_choice_links_user_can_manage()) {
        $CI->app_menu->add_sidebar_menu_item('smart-choice-links-main', [
            'name' => _l('smart_choice_links'),
            'href' => '#',
            'icon' => 'fa fa-star',
            'position' => 63,
        ]);
        $CI->app_menu->add_sidebar_children_item('smart-choice-links-main', [
            'slug' => 'smart-choice-links-manage',
            'name' => _l('smart_choice_links_manage'),
            'href' => admin_url('smart_choice_links'),
            'icon' => 'fa fa-link',
            'position' => 1,
        ]);
        $CI->app_menu->add_sidebar_children_item('smart-choice-links-main', [
            'slug' => 'smart-choice-links-settings',
            'name' => _l('smart_choice_links_settings'),
            'href' => admin_url('smart_choice_links/settings'),
            'icon' => 'fa fa-cog',
            'position' => 2,
        ]);
        $CI->app_menu->add_sidebar_children_item('smart-choice-links-main', [
            'slug' => 'smart-choice-links-health',
            'name' => _l('smart_choice_links_health_check'),
            'href' => admin_url('smart_choice_links/health'),
            'icon' => 'fa fa-heartbeat',
            'position' => 3,
        ]);
        $CI->app_menu->add_sidebar_children_item('smart-choice-links-main', [
            'slug' => 'smart-choice-links-help',
            'name' => _l('smart_choice_links_how_to_use'),
            'href' => admin_url('smart_choice_links/help'),
            'icon' => 'fa fa-book',
            'position' => 4,
        ]);
    }

    if (is_admin() || smart_choice_links_user_can_edit()) {
        $CI->app_menu->add_setup_menu_item('smart-choice-links-setup', [
            'name' => _l('smart_choice_links_settings'),
            'href' => admin_url('smart_choice_links/settings'),
            'icon' => 'fa fa-star',
            'position' => 63,
        ]);
    }

    if (isset($CI->app) && method_exists($CI->app, 'add_settings_section') && (is_admin() || smart_choice_links_user_can_edit())) {
        $CI->app->add_settings_section('smart_choice_links', [
            'name' => _l('smart_choice_links_settings'),
            'view' => 'smart_choice_links/settings',
            'position' => 95,
        ]);
    }

    smart_choice_links_ensure_database();
}

function smart_choice_links_admin_head()
{
    if (get_option('smart_choice_links_enabled') === '0') {
        return;
    }

    echo '<link href="' . module_dir_url(SMART_CHOICE_LINKS_MODULE_NAME, 'assets/css/smart_choice_links.css') . '?v=1.1.4" rel="stylesheet" type="text/css" />';
}

function smart_choice_links_admin_footer()
{
    if (get_option('smart_choice_links_enabled') === '0' || get_option('smart_choice_links_show_topbar') === '0') {
        return;
    }

    $CI = &get_instance();

    if (!$CI->db->table_exists(SMART_CHOICE_LINKS_TABLE)) {
        smart_choice_links_ensure_database();
    }

    // Render the hidden link source BEFORE JavaScript runs.
    // This fixes the issue where the stars appeared but the popup had no links.
    $CI->load->view('smart_choice_links/includes/topbar_dropdown');

    echo '<script>window.smartChoiceLinksEnableMenuSearch = "' . (get_option('smart_choice_links_enable_menu_search') ?: '1') . '";</script>';
    echo '<script>window.smartChoiceLinksSearchMainLabel = ' . json_encode(_l('smart_choice_links_search_main_menu')) . ';window.smartChoiceLinksSearchSetupLabel = ' . json_encode(_l('smart_choice_links_search_setup_menu')) . ';</script>';
    echo '<script>window.smartChoiceLinksEnableCommandPalette = "' . (get_option('smart_choice_links_enable_command_palette') ?: '0') . '";</script>';
    echo '<script>window.smartChoiceLinksSettingsUrl = "' . admin_url('smart_choice_links/settings') . '";</script>';
    echo '<script>window.smartChoiceLinksManageUrl = "' . admin_url('smart_choice_links') . '";</script>';
    echo '<style id="smart-choice-links-dynamic-css">:root{--scl-panel-width:' . (int)(get_option('smart_choice_links_panel_width') ?: 285) . 'px;--scl-row-height:' . (int)(get_option('smart_choice_links_row_height') ?: 34) . 'px;--scl-icon-size:' . (int)(get_option('smart_choice_links_icon_size') ?: 16) . 'px;--scl-font-size:' . (int)(get_option('smart_choice_links_font_size') ?: 13) . 'px;--scl-padding:' . (int)(get_option('smart_choice_links_padding') ?: 8) . 'px;--scl-radius:' . (int)(get_option('smart_choice_links_radius') ?: 8) . 'px;--scl-animation-speed:' . (int)(get_option('smart_choice_links_animation_speed') ?: 180) . 'ms;}</style>';
    echo '<script src="' . module_dir_url(SMART_CHOICE_LINKS_MODULE_NAME, 'assets/js/smart_choice_links.js') . '?v=1.1.4"></script>';
}

function smart_choice_links_ensure_database()
{
    $CI = &get_instance();

    if (!$CI->db->table_exists(SMART_CHOICE_LINKS_TABLE)) {
        $CI->db->query("CREATE TABLE `" . SMART_CHOICE_LINKS_TABLE . "` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(191) NOT NULL,
            `url` TEXT NOT NULL,
            `category` VARCHAR(100) NULL DEFAULT NULL,
            `placement` VARCHAR(20) NOT NULL DEFAULT 'both',
            `icon` VARCHAR(100) NULL DEFAULT 'fa fa-link',
            `target` VARCHAR(20) NOT NULL DEFAULT '_self',
            `rel` VARCHAR(100) NULL DEFAULT 'noopener noreferrer',
            `text_color` VARCHAR(20) NULL DEFAULT '',
            `text_shadow` TINYINT(1) NOT NULL DEFAULT 0,
            `position` INT(11) NOT NULL DEFAULT 1,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `is_internal` TINYINT(1) NOT NULL DEFAULT 1,
            `notes` TEXT NULL,
            `visible_to_roles` TEXT NULL,
            `visible_to_staff` TEXT NULL,
            `created_by` INT(11) NULL DEFAULT NULL,
            `datecreated` DATETIME NULL DEFAULT NULL,
            `updated_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    $columns = [
        'category'         => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `category` VARCHAR(100) NULL DEFAULT NULL",
        'placement'        => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `placement` VARCHAR(20) NOT NULL DEFAULT 'both'",
        'icon'             => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `icon` VARCHAR(100) NULL DEFAULT 'fa fa-link'",
        'target'           => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `target` VARCHAR(20) NOT NULL DEFAULT '_self'",
        'rel'              => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `rel` VARCHAR(100) NULL DEFAULT 'noopener noreferrer'",
        'text_color'       => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `text_color` VARCHAR(20) NULL DEFAULT ''",
        'text_shadow'      => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `text_shadow` TINYINT(1) NOT NULL DEFAULT 0",
        'position'         => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `position` INT(11) NOT NULL DEFAULT 1",
        'is_active'        => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `is_active` TINYINT(1) NOT NULL DEFAULT 1",
        'is_internal'      => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `is_internal` TINYINT(1) NOT NULL DEFAULT 1",
        'notes'            => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `notes` TEXT NULL",
        'visible_to_roles' => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `visible_to_roles` TEXT NULL",
        'visible_to_staff' => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `visible_to_staff` TEXT NULL",
        'created_by'       => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `created_by` INT(11) NULL DEFAULT NULL",
        'datecreated'      => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `datecreated` DATETIME NULL DEFAULT NULL",
        'updated_at'       => "ALTER TABLE `" . SMART_CHOICE_LINKS_TABLE . "` ADD `updated_at` DATETIME NULL DEFAULT NULL",
    ];

    foreach ($columns as $column => $sql) {
        if (!$CI->db->field_exists($column, SMART_CHOICE_LINKS_TABLE)) {
            $CI->db->query($sql);
        }
    }

    $defaults = [
        'smart_choice_links_enabled'              => '1',
        'smart_choice_links_show_topbar'          => '1',
        'smart_choice_links_show_left_star'       => '1',
        'smart_choice_links_show_right_star'      => '1',
        'smart_choice_links_open_new_tab'         => '0',
        'smart_choice_links_default_open_behavior' => '_self',
        'smart_choice_links_title_left'           => 'Smart Choice Quick Links',
        'smart_choice_links_title_right'          => 'Smart Choice Favorites',
        'smart_choice_links_footer_note'          => 'Smart Choice Contractors USA / Harold Cabrera',
        'smart_choice_links_button_label'         => '',
        'smart_choice_links_fix_bad_urls'         => '1',
        'smart_choice_links_panel_width'          => '285',
        'smart_choice_links_panel_min_width'      => '220',
        'smart_choice_links_panel_max_width'      => '500',
        'smart_choice_links_row_height'           => '34',
        'smart_choice_links_icon_size'            => '16',
        'smart_choice_links_font_size'            => '13',
        'smart_choice_links_padding'              => '8',
        'smart_choice_links_radius'               => '8',
        'smart_choice_links_animation_speed'      => '180',
        'smart_choice_links_enable_menu_search'   => '1',
        'smart_choice_links_enable_command_palette' => '0',
        'smart_choice_links_enable_setup_shortcut' => '1',
        'smart_choice_links_version'              => SMART_CHOICE_LINKS_VERSION,
    ];

    foreach ($defaults as $name => $value) {
        if (get_option($name) === false) {
            add_option($name, $value);
        }
    }

    update_option('smart_choice_links_version', SMART_CHOICE_LINKS_VERSION);

    smart_choice_links_import_old_favorite_links();
    smart_choice_links_seed_defaults();
    smart_choice_links_seed_active_shortcuts_if_empty();
}


function smart_choice_links_seed_active_shortcuts_if_empty()
{
    $CI = &get_instance();

    if (!$CI->db->table_exists(SMART_CHOICE_LINKS_TABLE)) {
        return;
    }

    $activeCount = (int) $CI->db->where('is_active', 1)->count_all_results(SMART_CHOICE_LINKS_TABLE);
    if ($activeCount > 0) {
        return;
    }

    $defaults = [
        ['Dashboard', 'admin/', 'CRM', 'left', 'fa fa-home', 1],
        ['Customers', 'admin/clients', 'CRM', 'left', 'fa fa-users', 2],
        ['Projects', 'admin/projects', 'Operations', 'left', 'fa fa-briefcase', 3],
        ['Estimates', 'admin/estimates', 'Sales', 'left', 'fa fa-file-text-o', 4],
        ['Invoices', 'admin/invoices', 'Sales', 'left', 'fa fa-file-invoice-dollar', 5],
        ['Reports', 'admin/reports', 'Management', 'right', 'fa fa-chart-bar', 1],
        ['Settings', 'admin/settings', 'Admin', 'right', 'fa fa-cog', 2],
        ['Modules', 'admin/modules', 'Admin', 'right', 'fa fa-puzzle-piece', 3],
        ['Staff', 'admin/staff', 'Admin', 'right', 'fa fa-user', 4],
        ['Media', 'admin/utilities/media', 'Tools', 'right', 'fa fa-picture-o', 5],
    ];

    foreach ($defaults as $row) {
        $CI->db->insert(SMART_CHOICE_LINKS_TABLE, [
            'title' => $row[0],
            'url' => smart_choice_links_normalize_url($row[1]),
            'category' => $row[2],
            'placement' => $row[3],
            'icon' => $row[4],
            'target' => '_self',
            'rel' => 'noopener noreferrer',
            'position' => $row[5],
            'is_active' => 1,
            'is_internal' => 1,
            'created_by' => get_staff_user_id(),
            'datecreated' => date('Y-m-d H:i:s'),
        ]);
    }
}

function smart_choice_links_import_old_favorite_links()
{
    $CI = &get_instance();
    $oldTable = db_prefix() . 'perfex_menu_links';

    if (!$CI->db->table_exists($oldTable)) {
        return;
    }

    $oldLinks = $CI->db->get($oldTable)->result();
    foreach ($oldLinks as $old) {
        $title = trim((string)($old->pml_title ?? ''));
        $url = trim((string)($old->pml_link ?? ''));

        if ($title === '' || $url === '') {
            continue;
        }

        $exists = $CI->db->where('title', $title)->like('url', $url)->count_all_results(SMART_CHOICE_LINKS_TABLE);
        if ($exists > 0) {
            continue;
        }

        $CI->db->insert(SMART_CHOICE_LINKS_TABLE, [
            'title'       => $title,
            'url'         => smart_choice_links_normalize_url($url),
            'category'    => 'Imported Favorites',
            'placement'   => 'left',
            'icon'        => 'fa fa-star',
            'target'      => !empty($old->pml_target) ? $old->pml_target : '_self',
            'rel'         => !empty($old->pml_rels) ? $old->pml_rels : 'noopener noreferrer',
            'position'    => isset($old->pml_order) ? (int)$old->pml_order : 1,
            'is_active'   => 1,
            'is_internal' => 1,
            'notes'       => 'Imported automatically from the old Favorite Links module.',
            'created_by'  => get_staff_user_id(),
            'datecreated' => date('Y-m-d H:i:s'),
        ]);
    }
}

function smart_choice_links_seed_defaults()
{
    $CI = &get_instance();

    if ($CI->db->count_all_results(SMART_CHOICE_LINKS_TABLE) > 0) {
        return;
    }

    $defaults = [
        ['Dashboard', 'admin/', 'CRM', 'left', 'fa fa-home', 1],
        ['Customers', 'admin/clients', 'CRM', 'left', 'fa fa-users', 2],
        ['Leads', 'admin/leads', 'Sales', 'left', 'fa fa-filter', 3],
        ['Estimates', 'admin/estimates', 'Sales', 'left', 'fa fa-file-text-o', 4],
        ['Invoices', 'admin/invoices', 'Sales', 'left', 'fa fa-file-invoice-dollar', 5],
        ['Projects', 'admin/projects', 'Operations', 'right', 'fa fa-briefcase', 1],
        ['Tasks', 'admin/tasks', 'Operations', 'right', 'fa fa-check-square', 2],
        ['Reports', 'admin/reports', 'Management', 'right', 'fa fa-chart-bar', 3],
        ['Modules', 'admin/modules', 'Admin', 'right', 'fa fa-puzzle-piece', 4],
        ['Settings', 'admin/settings', 'Admin', 'right', 'fa fa-cog', 5],
        ['Setup Menu', 'admin/settings', 'Admin', 'both', 'fa fa-sliders-h', 6],
        ['Modules', 'admin/modules', 'Admin', 'right', 'fa fa-puzzle-piece', 7],
    ];

    foreach ($defaults as $row) {
        $CI->db->insert(SMART_CHOICE_LINKS_TABLE, [
            'title' => $row[0],
            'url' => smart_choice_links_normalize_url($row[1]),
            'category' => $row[2],
            'placement' => $row[3],
            'icon' => $row[4],
            'target' => '_self',
            'rel' => 'noopener noreferrer',
            'position' => $row[5],
            'is_active' => 1,
            'is_internal' => 1,
            'created_by' => get_staff_user_id(),
            'datecreated' => date('Y-m-d H:i:s'),
        ]);
    }
}

function smart_choice_links_normalize_url($url)
{
    $url = trim((string) $url);

    if ($url === '') {
        return '';
    }

    $lowerOriginal = strtolower(str_replace(' ', '', $url));
    if (in_array($lowerOriginal, ['admin/modules', '/admin/modules'], true)) {
        return admin_url('modules');
    }

    $url = str_replace(' ', '', $url);
    $url = str_replace(['ttps://', 'ttp://'], ['https://', 'http://'], $url);
    $url = preg_replace('#h{2,}ttps://#i', 'https://', $url);
    $url = preg_replace('#h{2,}ttp://#i', 'http://', $url);
    $url = preg_replace('#^(https?):/{1,3}#i', '$1://', $url);

    $protocolCount = preg_match_all('#https?://#i', $url, $protocolMatches, PREG_OFFSET_CAPTURE);
    if ($protocolCount && $protocolCount > 1) {
        $last = end($protocolMatches[0]);
        $url = substr($url, $last[1]);
    }

    $crmDomain = parse_url(site_url(), PHP_URL_HOST) ?: 'crm.justsmartchoice.com';
    $lastCrmPos = strripos($url, $crmDomain);
    if ($lastCrmPos !== false && stripos(substr($url, 0, $lastCrmPos), $crmDomain) !== false) {
        $url = 'https://' . substr($url, $lastCrmPos);
    }

    $badModulePatterns = [
        '#^https?://[^/]+/admin/(moduless|modeless)/?$#i',
        '#^/admin/(moduless|modeless)/?$#i',
        '#^admin/(moduless|modeless)/?$#i',
        '#^//adminmodules/?$#i',
        '#^/adminmodules/?$#i',
        '#^adminmodules/?$#i',
    ];

    foreach ($badModulePatterns as $pattern) {
        if (preg_match($pattern, $url)) {
            return admin_url('modules');
        }
    }

    if (preg_match('#^https?://[^/]+/modules/?$#i', $url) || preg_match('#^/?modules/?$#i', $url)) {
        return admin_url('modules');
    }

    if (strpos($url, $crmDomain . '/') === 0) {
        $url = 'https://' . $url;
    }

    $parts = parse_url($url);
    if (is_array($parts) && isset($parts['scheme'], $parts['host'])) {
        $path     = isset($parts['path']) ? preg_replace('#/+#', '/', $parts['path']) : '';
        $query    = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        $url      = $parts['scheme'] . '://' . $parts['host'] . $path . $query . $fragment;
    }

    if (strpos($url, 'admin/') === 0) {
        $url = admin_url(ltrim(substr($url, 6), '/'));
    } elseif (strpos($url, '/admin/') === 0) {
        $url = site_url(ltrim($url, '/'));
    } elseif (strpos($url, 'settings') === 0) {
        $url = admin_url('settings');
    }

    if (!preg_match('#^(https?:|mailto:|tel:|sms:|/)#i', $url)) {
        $url = admin_url(ltrim($url, '/'));
    }

    return $url;
}

function smart_choice_links_url_for_edit($url)
{
    $url = smart_choice_links_normalize_url($url);
    $base = rtrim(admin_url(), '/') . '/';

    if (stripos($url, $base) === 0) {
        return 'admin/' . ltrim(substr($url, strlen($base)), '/');
    }

    return $url;
}

function smart_choice_links_user_can_view($link)
{
    if (is_admin()) {
        return true;
    }

    $staffId = get_staff_user_id();

    if (!empty($link->visible_to_staff)) {
        $staff = array_filter(array_map('trim', explode(',', $link->visible_to_staff)));
        if (in_array((string) $staffId, $staff, true)) {
            return true;
        }
    }

    if (!empty($link->visible_to_roles)) {
        $CI = &get_instance();
        $staffRow = $CI->db->select('role')->where('staffid', $staffId)->get(db_prefix() . 'staff')->row();
        $roleId = $staffRow ? (string) $staffRow->role : '';
        $roles = array_filter(array_map('trim', explode(',', $link->visible_to_roles)));
        if ($roleId !== '' && in_array($roleId, $roles, true)) {
            return true;
        }

        return false;
    }

    return true;
}


hooks()->add_action('app_admin_head', 'smart_choice_links_smart_choice_normalize_assets');
function smart_choice_links_smart_choice_normalize_assets()
{
    echo '<link href="' . module_dir_url('smart_choice_links', 'assets/css/smart_choice_module_normalize.css') . '?v=103" rel="stylesheet" type="text/css" />';
}
