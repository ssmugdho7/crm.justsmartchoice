<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_214 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Run the safe installer again. It uses table/field existence checks and does not delete data.
        $install = module_dir_path('purchasing_hub') . 'install.php';
        if (file_exists($install)) {
            require_once($install);
        }

        if (function_exists('get_option') && function_exists('add_option') && function_exists('update_option')) {
            if (get_option('purchasing_hub_module_version') === '') {
                add_option('purchasing_hub_module_version', '2.1.4');
            } else {
                update_option('purchasing_hub_module_version', '2.1.4');
            }
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
