<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends CI_Migration
{
    public function up()
    {
        require_once(FCPATH . 'modules/employees_tracker/install.php');
        employees_tracker_module_install();
    }

    public function down()
    {
        return true;
    }
}
