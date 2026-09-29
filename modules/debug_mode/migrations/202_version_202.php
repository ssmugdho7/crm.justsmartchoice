<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Debug_mode_settings_roles extends App_module_migration
{
    public function up()
    {
        if (file_exists(__DIR__ . '/../install.php')) {
            require_once(__DIR__ . '/../install.php');
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}