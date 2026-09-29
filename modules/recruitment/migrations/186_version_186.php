<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_186 extends App_module_migration
{
    public function up()
    {
        // UI-only upgrade. No database or candidate data changes.
    }

    public function down()
    {
        // Upgrade-only migration.
    }
}
