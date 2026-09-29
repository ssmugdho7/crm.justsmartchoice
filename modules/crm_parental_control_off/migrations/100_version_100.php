<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_100 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('crm_parental_control', 'install.php');
    }

    public function down()
    {
        // Non-destructive rollback. Tables and backups stay in place.
    }
}
