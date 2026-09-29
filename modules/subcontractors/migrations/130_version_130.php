<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_130 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('subcontractors', 'install.php');
    }

    public function down()
    {
        // Safe rollback: no destructive changes. Data remains preserved.
    }
}
