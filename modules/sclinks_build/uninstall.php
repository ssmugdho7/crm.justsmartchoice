<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Safe uninstall: keeps link records by default.
 * To remove data permanently, uncomment the DROP TABLE line below after exporting a backup.
 */

delete_option('smart_choice_links_enabled');
delete_option('smart_choice_links_show_topbar');
delete_option('smart_choice_links_open_new_tab');
delete_option('smart_choice_links_title');
delete_option('smart_choice_links_footer_note');
delete_option('smart_choice_links_button_label');
delete_option('smart_choice_links_fix_bad_urls');
delete_option('smart_choice_links_version');

// $CI = &get_instance();
// $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'smart_choice_links`');
