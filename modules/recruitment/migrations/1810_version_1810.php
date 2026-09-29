<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_1810 extends App_module_migration
{
    public function up()
    {
        // Version 1.8.10 contains the digital signature interaction fix only.
        // No database schema or data changes are required for this release.
    }

    public function down()
    {
        // Upgrade-only migration.
    }
}
