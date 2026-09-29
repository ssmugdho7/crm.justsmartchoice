<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('ideal', 'install.php');
        update_option('ideal_module_version', '1.1.0');
    }

    public function down()
    {
        return true;
    }
}
