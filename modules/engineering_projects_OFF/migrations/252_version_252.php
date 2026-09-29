<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_252 extends CI_Migration
{
    public function up()
    {
        $install = module_dir_path('engineering_projects') . 'install.php';
        if (file_exists($install)) { require_once($install); }
    }

    public function down() {}
}
