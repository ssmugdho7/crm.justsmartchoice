<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_210 extends App_module_migration
{
    public function up()
    {
        if (function_exists('add_option')) {
            add_option('debug_mode_phpmyadmin_url', 'https://s111.bluehost.com:2083/3rdparty/phpMyAdmin/index.php');
            add_option('debug_mode_client_portal_enabled', '1');
            add_option('debug_mode_show_admin_banner', '1');
            add_option('debug_mode_cache_last_cleared', '');
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
