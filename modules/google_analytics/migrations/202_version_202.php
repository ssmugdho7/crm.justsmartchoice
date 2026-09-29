<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_202 extends App_module_migration
{
    public function up()
    {
        // Run the complete, idempotent schema repair exactly once.
        require dirname(__DIR__) . '/install.php';
        update_option('ga_module_version', '2.0.2');

        return true;
    }

    public function down()
    {
        // Non-destructive rollback. Analytics data and settings are preserved.
        return true;
    }
}
