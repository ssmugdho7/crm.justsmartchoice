<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_212 extends App_module_migration
{
    public function up()
    {
        if (function_exists('update_option')) {
            update_option('debug_mode_version', '2.1.2');
        }
        return true;
    }

    public function down()
    {
        return true;
    }
}
