<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_120 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('sales_center', 'install.php');
    }

    public function down()
    {
        // Safe rollback: no destructive changes. Data remains preserved.
    }
}
