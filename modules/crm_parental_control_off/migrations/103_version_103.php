<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_103 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('crm_parental_control', 'install.php');
        if (function_exists('update_option')) {
            update_option('crm_parental_control_version', '1.0.3');
        }
    }

    public function down()
    {
        if (function_exists('update_option')) {
            update_option('crm_parental_control_version', '1.0.2');
        }
    }
}
