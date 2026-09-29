<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_240 extends App_module_migration
{
    public function up()
    {
        require_once(__DIR__ . '/../install.php');
    }
}
