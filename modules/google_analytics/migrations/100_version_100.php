<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_100 extends App_module_migration
{
    public function up()
    {
        require dirname(__DIR__) . '/install.php';
        update_option('ga_module_version', '2.0.2');
    }

    public function down()
    {
        // Non-destructive rollback. Analytics data and settings are preserved.
    }
}
