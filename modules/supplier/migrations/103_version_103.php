<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_103 extends App_module_migration
{
    public function up()
    {
        // Supplier v1.0.3 update migration.
        // No database structure changes are required for this release.
        // This file exists so Perfex can complete the upgrade from module version 1.0.3.
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
