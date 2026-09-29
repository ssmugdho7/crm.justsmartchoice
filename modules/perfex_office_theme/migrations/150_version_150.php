<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_150 extends App_module_migration
{
    public function up()
    {
        update_option('perfex_office_theme_version', '1.5.0');
        update_option('smart_choice_theme_last_update', '1.5.0');
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
