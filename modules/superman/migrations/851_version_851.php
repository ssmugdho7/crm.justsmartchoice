<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_851 extends App_module_migration
{
    public function up()
    {
        // Smart Choice bridge migration to keep Superman upgrade sequence continuous.
        return true;
    }

    public function down()
    {
        // Preserve Superman data during rollback checks.
        return true;
    }
}
