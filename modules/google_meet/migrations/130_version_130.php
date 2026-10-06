<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_130 extends App_module_migration
{
    public function up()
    {
        require dirname(__DIR__) . '/install.php';
    }

    public function down()
    {
        // Keep links, notes and credentials when rolling back application files.
    }
}
