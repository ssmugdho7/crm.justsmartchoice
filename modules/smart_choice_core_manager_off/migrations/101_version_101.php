<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        update_option('sc_core_manager_version', '1.0.1');
        update_option('sc_core_build_version', 'SC-3.5.4-1.0.1');
    }

    public function down()
    {
        // Non-destructive rollback.
    }
}
