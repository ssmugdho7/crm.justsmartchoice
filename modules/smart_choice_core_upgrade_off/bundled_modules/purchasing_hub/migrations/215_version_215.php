<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_215 extends App_module_migration
{
    public function up()
    {
        $install = module_dir_path('purchasing_hub') . 'install.php';
        if (file_exists($install)) { require_once($install); }
        if (function_exists('update_option')) {
            if (get_option('purchasing_hub_module_version') === '') { add_option('purchasing_hub_module_version', '2.1.5'); }
            else { update_option('purchasing_hub_module_version', '2.1.5'); }
        }
        return true;
    }

    public function down()
    {
        return true;
    }
}
