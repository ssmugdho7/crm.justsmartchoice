<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_100 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('supplier') . 'install.php';
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
