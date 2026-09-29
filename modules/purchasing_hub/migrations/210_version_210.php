<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_210 extends App_module_migration
{
    public function up()
    {
        require_once(module_dir_path('purchasing_hub') . 'install.php');
    }
}
