<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_356 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_enterprise_current_version', '3.5.6');
        update_option('smart_choice_enterprise_latest_version', '3.5.6');
        update_option('smart_choice_core_latest_version', '3.5.6');
        update_option('smart_choice_latest_version', '3.5.6');
        update_option('latest_version', '3.5.6');
        update_option('update_info_message', '');
    }

    public function down()
    {
        // Forward-only module migration.
    }
}
