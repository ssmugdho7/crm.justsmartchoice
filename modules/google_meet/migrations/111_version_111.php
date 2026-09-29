<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        require_once(module_dir_path('google_meet') . 'install.php');
        return true;
    }

    public function down()
    {
        return true;
    }
}
