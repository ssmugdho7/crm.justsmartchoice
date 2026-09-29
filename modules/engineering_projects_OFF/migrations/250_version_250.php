<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_250 extends App_module_migration
{
    public function up()
    {
        require_once(__DIR__ . '/../install.php');
    }
}
