<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Smartchoice_mobile_star_fix extends App_module_migration
{
    public function up()
    {
        add_option('favorite_links_enabled', '1');
        add_option('favorite_links_open_new_tab', '1');
        add_option('favorite_links_show_in_menu', '1');
        add_option('favorite_links_smartchoice_version', '2.0.1');
        add_option('favorite_links_mobile_star_fix', '1');
    }

    public function down()
    {
        return true;
    }
}
