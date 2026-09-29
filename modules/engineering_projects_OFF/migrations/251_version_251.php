<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_251 extends App_module_migration
{
    public function up()
    {
        $install = __DIR__ . '/../install.php';
        if (file_exists($install)) {
            require_once($install);
        }
    }
}
