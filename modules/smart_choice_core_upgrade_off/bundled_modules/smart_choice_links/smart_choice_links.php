<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Links
Module URI: https://www.justsmartchoice.com
Description: Quality ★★★★★.  Smart Choice Links is a clean quick-links and favorites module built for Smart Choice Contractors USA. It adds organized CRM shortcuts near the top search bar, supports categories, icons, order, active/inactive status, internal/external links, notes, role/user visibility, and a settings page inside Perfex CRM.
Version: 1.0.7
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

define('SMART_CHOICE_LINKS_MODULE_NAME', 'smart_choice_links');
define('SMART_CHOICE_LINKS_TABLE', db_prefix() . 'smart_choice_links');

register_activation_hook(SMART_CHOICE_LINKS_MODULE_NAME, 'smart_choice_links_activation_hook');
register_deactivation_hook(SMART_CHOICE_LINKS_MODULE_NAME, 'smart_choice_links_deactivation_hook');
register_uninstall_hook(SMART_CHOICE_LINKS_MODULE_NAME, 'smart_choice_links_uninstall_hook');

register_language_files(SMART_CHOICE_LINKS_MODULE_NAME, [SMART_CHOICE_LINKS_MODULE_NAME]);

hooks()->add_action('admin_init', 'smart_choice_links_admin_init');
hooks()->add_action('app_admin_head', 'smart_choice_links_admin_head');
hooks()->add_action('app_admin_footer', 'smart_choice_links_admin_footer');
hooks()->add_filter('module_smart_choice_links_action_links', 'smart_choice_links_action_links');

function smart_choice_links_activation_hook()
{
    require_once(__DIR__ . '/install.php');
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
    $actions[] = '<a href="' . admin_url('settings?group=smart_choice_links') . '">' . _l('settings') . '</a>';
    $actions[] = '<a href="' . admin_url('smart_choice_links') . '">' . _l('smart_choice_links_manage') . '</a>';
    return $actions;
}

function smart_choice_links_admin_init()
{
    $CI = &get_instance();

    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item('smart-choice-links-setup', [
            'name'     => _l('smart_choice_links'),
            'href'     => admin_url('smart_choice_links'),
            'position' => 63,
        ]);
    }

    $CI->app->add_settings_section('smart_choice_links', [
        'name'     => _l('smart_choice_links_settings'),
        'view'     => 'smart_choice_links/settings',
        'position' => 95,
    ]);

    smart_choice_links_ensure_database();
}

function smart_choice_links_admin_head()
{
    if (get_option('smart_choice_links_enabled') === '0') {
        return;
    }

    echo '<link href="' . module_dir_url(SMART_CHOICE_LINKS_MODULE_NAME, 'assets/css/smart_choice_links.css') . '?v=1.0.6" rel="stylesheet" type="text/css" />';
}

function smart_choice_links_admin_footer()
{
    if (get_option('smart_choice_links_enabled') === '0') {
        return;
    }

    $CI = &get_instance();

    if (!$CI->db->table_exists(SMART_CHOICE_LINKS_TABLE)) {
        smart_choice_links_ensure_database();
    }

    echo '<script src="' . module_dir_url(SMART_CHOICE_LINKS_MODULE_NAME, 'assets/js/smart_choice_links.js') . '?v=1.0.6"></script>';

    $CI->load->view('smart_choice_links/includes/topbar_dropdown');
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
        'smart_choice_links_enabled'        => '1',
        'smart_choice_links_show_topbar'    => '1',
        'smart_choice_links_open_new_tab'   => '0',
        'smart_choice_links_title'          => 'Smart Choice Links',
        'smart_choice_links_footer_note'    => 'Smart Choice Contractors USA / Harold Cabrera',
        'smart_choice_links_button_label'   => 'Links',
        'smart_choice_links_fix_bad_urls'   => '1',
        'smart_choice_links_version'        => '1.0.6',
    ];

    foreach ($defaults as $name => $value) {
        if (get_option($name) === false) {
            add_option($name, $value);
        }
    }
}

function smart_choice_links_normalize_url($url)
{
    $url = trim((string) $url);

    if ($url === '') {
        return '';
    }

    // Preserve the intended CRM Modules link exactly.
    $lowerOriginal = strtolower(str_replace(' ', '', $url));
    if (in_array($lowerOriginal, ['admin/modules', '/admin/modules'], true)) {
        return admin_url('modules');
    }

    // Remove accidental spaces and fix only protocol/copy-paste damage.
    $url = str_replace(' ', '', $url);
    $url = str_replace(['ttps://', 'ttp://'], ['https://', 'http://'], $url);
    $url = preg_replace('#h{2,}ttps://#i', 'https://', $url);
    $url = preg_replace('#h{2,}ttp://#i', 'http://', $url);
    $url = preg_replace('#^(https?):/{1,3}#i', '$1://', $url);

    // If several URLs were pasted together, keep the last complete URL.
    $protocolCount = preg_match_all('#https?://#i', $url, $protocolMatches, PREG_OFFSET_CAPTURE);
    if ($protocolCount && $protocolCount > 1) {
        $last = end($protocolMatches[0]);
        $url = substr($url, $last[1]);
    }

    // If the CRM domain is repeated, keep the last CRM destination.
    $crmDomain = 'crm.justsmartchoice.com';
    $lastCrmPos = strripos($url, $crmDomain);
    if ($lastCrmPos !== false && stripos(substr($url, 0, $lastCrmPos), $crmDomain) !== false) {
        $url = 'https://' . substr($url, $lastCrmPos);
    }

    $url = preg_replace('#^(https?):/{1,3}#i', '$1://', $url);

    // Convert only known malformed CRM paths. Do not rewrite the valid admin/modules value.
    $badModulePatterns = [
        '#^https?://crm\.justsmartchoice\.com/admin/(moduless|modeless)/?$#i',
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

    // If someone starts with /modules, it is usually the admin modules page.
    if (preg_match('#^https?://crm\.justsmartchoice\.com/modules/?$#i', $url) || preg_match('#^/?modules/?$#i', $url)) {
        return admin_url('modules');
    }

    // If the CRM domain appears without a scheme after cleanup, fix it.
    if (strpos($url, 'crm.justsmartchoice.com/') === 0) {
        $url = 'https://' . $url;
    }

    // Reduce duplicate slashes in path only, not after http(s).
    $parts = parse_url($url);
    if (is_array($parts) && isset($parts['scheme'], $parts['host'])) {
        $path     = isset($parts['path']) ? preg_replace('#/+#', '/', $parts['path']) : '';
        $query    = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        $url      = $parts['scheme'] . '://' . $parts['host'] . $path . $query . $fragment;
    }

    // Internal CRM paths should be entered as admin/settings, admin/invoices, etc.
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
