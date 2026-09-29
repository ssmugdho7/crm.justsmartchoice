<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_212 extends App_module_migration
{
    public function up()
    {
        require_once(module_dir_path('purchasing_hub') . 'install.php');
        update_option('purchasing_hub_module_version', '2.1.2');
        update_option('purchasing_hub_last_modified', '2026-06-30');
        update_option('purchasing_hub_quality_rating', '5');
        return true;
    }

    public function down()
    {
        return true;
    }
}
