<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_200 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('smartsource_subcontractors', 'install.php');
    }
}
