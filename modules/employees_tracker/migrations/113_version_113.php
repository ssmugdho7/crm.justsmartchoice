<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_113 extends App_module_migration
{
    public function up()
    {
        require_once(FCPATH . 'modules/employees_tracker/install.php');
        employees_tracker_module_install();
        update_option('employees_tracker_module_version', '1.1.3');
        return true;
    }

    public function down()
    {
        return true;
    }
}
