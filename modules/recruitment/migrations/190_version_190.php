<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_190 extends App_module_migration
{
    public function up()
    {
        // Version 1.9.0 packages the existing 1.8.10 runtime/signature changes.
        // No database schema or data changes are required.
    }

    public function down()
    {
        // Upgrade-only migration.
    }
}
