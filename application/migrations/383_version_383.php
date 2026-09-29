<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_383 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_crm_build', '3.8.3 SC');
    }

    public function down()
    {
        // Upgrade-only migration. Existing project files and data are preserved.
    }
}
