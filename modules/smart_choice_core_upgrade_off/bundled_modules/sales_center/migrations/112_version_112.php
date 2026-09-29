<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_112 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('sales_center', 'install.php');
    }
}
