<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Favorite Links
Module URI: https://www.justsmartchoice.com
Description: Favorite Links helps Smart Choice Contractors USA keep important CRM shortcuts, daily tools, project resources, vendor links, supplier pages, and frequently used business links available beside the CRM search bar. This Smart Choice version fixes asset paths, PHP 8.5 compatibility issues, mobile star placement, link normalization, and adds a CRM settings section while preserving the original module workflow.
Version: 2.0.2
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://www.justsmartchoice.com
Requires at least: 2.3.*
*/

define('FAVORITE_LINKS_MODULE_NAME', 'favorite_links');
define('FAVORITE_LINKS_MODULE_DISPLAY_NAME', 'Favorite Links');
// Backward-compatible constant used by older files.
define('PERFEX_MENU_LINK_MODULE', FAVORITE_LINKS_MODULE_NAME);

register_language_files(FAVORITE_LINKS_MODULE_NAME, [FAVORITE_LINKS_MODULE_NAME]);

hooks()->add_action('admin_init', 'favorite_links_smartchoice_admin_init');
hooks()->add_action('app_admin_footer', 'favorite_links_smartchoice_footer');
hooks()->add_action('app_admin_head', 'favorite_links_smartchoice_assets');
hooks()->add_action('app_customers_head', 'favorite_links_smartchoice_mobile_assets');
hooks()->add_action('app_customers_footer', 'favorite_links_smartchoice_mobile_footer_assets');

function favorite_links_smartchoice_admin_init()
{
    $CI =& get_instance();

    favorite_links_smartchoice_ensure_database();

    // Perfex 3.x settings section. Wrapped safely so older installs do not break.
    if (isset($CI->app) && method_exists($CI->app, 'add_settings_section')) {
        $CI->app->add_settings_section('favorite_links', [
            'name'     => _l('favorite_links_settings'),
            'view'     => 'favorite_links/settings',
            'position' => 80,
        ]);
    }
}

function favorite_links_smartchoice_ensure_database()
{
    $CI =& get_instance();
    $table = db_prefix() . 'perfex_menu_links';

    if (!$CI->db->table_exists($table)) {
        $CI->db->query('CREATE TABLE `' . $table . '` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `pml_title` varchar(255) DEFAULT NULL,
            `pml_link` varchar(255) DEFAULT NULL,
            `pml_rels` varchar(50) DEFAULT NULL,
            `pml_target` varchar(50) DEFAULT NULL,
            `pml_hotkeys` varchar(5) DEFAULT NULL,
            `pml_hotkey_numbers` int(11) DEFAULT NULL,
            `pml_order` int(11) NULL DEFAULT 1,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;');
    }

    if (!$CI->db->field_exists('pml_order', $table)) {
        $CI->db->query('ALTER TABLE `' . $table . '` ADD COLUMN `pml_order` INT(11) NULL DEFAULT 1 AFTER `pml_hotkey_numbers`;');
    }

    favorite_links_smartchoice_add_default_options();
}

function favorite_links_smartchoice_add_default_options()
{
    add_option('favorite_links_enabled', '1');
    add_option('favorite_links_open_new_tab', '0');
    add_option('favorite_links_show_in_menu', '1');
    add_option('favorite_links_show_star_near_search', '1');
    add_option('favorite_links_enable_hotkeys', '1');
    add_option('favorite_links_default_target', '_self');
    add_option('favorite_links_smartchoice_version', '2.0.2');
    add_option('favorite_links_mobile_star_fix', '1');
}

function favorite_links_smartchoice_assets()
{
    if (get_option('favorite_links_enabled') === '0') {
        return;
    }

    echo '<link href="' . module_dir_url(FAVORITE_LINKS_MODULE_NAME, 'assets/css/favorite_links_smartchoice.css') . '?v=202" rel="stylesheet" type="text/css" />';
    favorite_links_smartchoice_mobile_assets();
}

function favorite_links_smartchoice_mobile_assets()
{
    if (get_option('favorite_links_enabled') === '0') {
        return;
    }

    echo '<link href="' . module_dir_url(FAVORITE_LINKS_MODULE_NAME, 'assets/css/favorite_links_smartchoice_mobile_fix.css') . '?v=202" rel="stylesheet" type="text/css" />';
}

function favorite_links_smartchoice_footer()
{
    if (get_option('favorite_links_enabled') === '0') {
        return;
    }

    echo '<script src="' . module_dir_url(FAVORITE_LINKS_MODULE_NAME, 'assets/favorite_menu_link.js') . '?v=202"></script>';

    include __DIR__ . '/includes/perfex_menu_link_content.php';

    favorite_links_smartchoice_mobile_footer_assets();
}

function favorite_links_smartchoice_mobile_footer_assets()
{
    if (get_option('favorite_links_enabled') === '0') {
        return;
    }

    echo '<script src="' . module_dir_url(FAVORITE_LINKS_MODULE_NAME, 'assets/js/favorite_links_smartchoice_mobile_fix.js') . '?v=202"></script>';
}
