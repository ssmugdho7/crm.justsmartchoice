<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_175 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        update_option('recruitment_module_version', '1.7.5');
        // UI/PHP compatibility release. No destructive database changes.
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}
