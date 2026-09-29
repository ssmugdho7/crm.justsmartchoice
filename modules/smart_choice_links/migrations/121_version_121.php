<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_121 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_links_version', '1.2.1');
        update_option('smart_choice_links_enable_menu_search', '1');
        update_option('smart_choice_links_show_left_star', '1');
        update_option('smart_choice_links_show_right_star', '1');
    }
}
