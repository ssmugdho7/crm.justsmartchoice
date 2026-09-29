<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_270 extends App_module_migration
{
    public function up()
    {
        require_once(__DIR__ . '/../install.php');
        update_option('smartsource_subcontractors_module_version', '2.7.0');
    }

    public function down()
    {
        return true;
    }
}
