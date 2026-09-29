<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        // Re-run the idempotent installer to guarantee all v1 tables/options exist.
        require_once APP_MODULES_PATH . 'smart_choice_call_center/install.php';
        if (function_exists('sccc_install')) {
            sccc_install();
        }
    }

    public function down()
    {
        // Non-destructive downgrade: keep call history, recordings, campaigns and settings.
    }
}
