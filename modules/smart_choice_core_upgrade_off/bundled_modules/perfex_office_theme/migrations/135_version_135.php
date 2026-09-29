<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_135 extends App_module_migration
{
    public function up()
    {
        require_once(module_dir_path('perfex_office_theme', 'install.php'));

    }

    public function down()
    {
        return true;
    }
}
