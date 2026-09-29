<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_121 extends App_module_migration
{
    public function up()
    {
        // Smart Choice compatibility migration for Superman v1.2.1.
        // Required so Perfex can upgrade the module version without throwing:
        // "No migration could be found with the version number: 121".
        // Do not drop, rename, or modify business data here.
        return true;
    }

    public function down()
    {
        // Required by Perfex migration rollback checks.
        // Intentionally preserves all Superman data and settings.
        return true;
    }
}
