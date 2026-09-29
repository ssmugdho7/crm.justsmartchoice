<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_140 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('subcontractors', 'install.php');
    }

    public function down()
    {
        return true;
    }
}
