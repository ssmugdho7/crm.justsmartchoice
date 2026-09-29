<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_138 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_office_theme_version', '1.3.8');
        return true;
    }

    public function down()
    {
        return true;
    }
}
