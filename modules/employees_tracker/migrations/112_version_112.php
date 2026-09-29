<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_112 extends App_module_migration
{
    public function up()
    {
        update_option('employees_tracker_module_version', '1.1.2');
        return true;
    }

    public function down()
    {
        return true;
    }
}
