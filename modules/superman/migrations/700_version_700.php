<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_700 extends App_module_migration
{
    public function up()
    {
        // Compatibility bridge migration for existing Superman installs.
        return true;
    }

    public function down()
    {
        // Required by Perfex migration rollback checks. Do not drop Smart Choice data.
        return true;
    }
}
