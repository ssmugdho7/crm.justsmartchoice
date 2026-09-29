<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_351 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_enterprise_current_version', '3.5.1');
        update_option('smart_choice_enterprise_latest_version', '3.5.1');
        update_option('smart_choice_core_current_version', '3.5.1');
        update_option('smart_choice_core_latest_version', '3.5.1');
        update_option('latest_version', '3.5.1');
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
