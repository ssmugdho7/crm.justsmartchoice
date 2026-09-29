<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_120 extends App_module_migration
{
    public function up()
    {
        if (function_exists('smart_choice_links_ensure_database')) {
            smart_choice_links_ensure_database();
        }
        update_option('smart_choice_links_enabled', '1');
        update_option('smart_choice_links_show_topbar', '1');
        update_option('smart_choice_links_show_left_star', '1');
        update_option('smart_choice_links_show_right_star', '1');
        update_option('smart_choice_links_enable_menu_search', '1');
        update_option('smart_choice_links_version', '1.2.0');
    }
    public function down() { }
}
