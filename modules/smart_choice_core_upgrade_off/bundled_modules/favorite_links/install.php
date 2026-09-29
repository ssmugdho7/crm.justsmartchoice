<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

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

add_option('favorite_links_enabled', '1');
add_option('favorite_links_open_new_tab', '0');
add_option('favorite_links_show_in_menu', '1');
add_option('favorite_links_show_star_near_search', '1');
add_option('favorite_links_enable_hotkeys', '1');
add_option('favorite_links_default_target', '_self');
add_option('favorite_links_smartchoice_version', '2.0.2');
add_option('favorite_links_mobile_star_fix', '1');
