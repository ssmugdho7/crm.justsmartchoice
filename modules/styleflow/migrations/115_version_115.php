<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * StyleFlow 1.1.5 recovery migration.
 *
 * Module-only version marker. It intentionally does not load CodeIgniter's
 * Migration library, alter Perfex core migration configuration, recreate
 * tables, or reseed templates.
 */
class Migration_Version_115 extends App_module_migration
{
    public function up()
    {
        update_option('styleflow_version', '1.1.5');
    }

    public function down()
    {
        // Upgrade-only migration. Existing StyleFlow data is preserved.
    }
}
