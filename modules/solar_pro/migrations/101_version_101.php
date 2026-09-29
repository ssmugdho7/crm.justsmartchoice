<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('solar_pro', 'helpers/solar_pro_helper.php');
        $CI = &get_instance();
        solar_pro_install_schema($CI);
        solar_pro_install_options();
        solar_pro_seed_defaults($CI);
    }
}
