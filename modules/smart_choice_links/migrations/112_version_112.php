<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_112 extends App_module_migration
{
    public function up()
    {
        if (function_exists('smart_choice_links_ensure_database')) {
            smart_choice_links_ensure_database();
        }

        update_option('smart_choice_links_version', '1.1.2');
        if (get_option('smart_choice_links_enabled') === '') {
            update_option('smart_choice_links_enabled', '1');
        }
        if (get_option('smart_choice_links_show_topbar') === '') {
            update_option('smart_choice_links_show_topbar', '1');
        }
        if (get_option('smart_choice_links_show_left_star') === '') {
            update_option('smart_choice_links_show_left_star', '1');
        }
        if (get_option('smart_choice_links_show_right_star') === '') {
            update_option('smart_choice_links_show_right_star', '1');
        }
        if (get_option('smart_choice_links_enable_menu_search') === '') {
            update_option('smart_choice_links_enable_menu_search', '1');
        }
        if (get_option('smart_choice_links_enable_command_palette') === '') {
            update_option('smart_choice_links_enable_command_palette', '0');
        }
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}
