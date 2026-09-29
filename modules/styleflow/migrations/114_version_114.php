<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * StyleFlow 1.1.4 module migration.
 *
 * IMPORTANT: This is an App_module_migration. StyleFlow intentionally does
 * not ship config/migration.php because that filename belongs to Perfex/CI
 * core database migrations and can override the CRM's global migration target.
 */
class Migration_Version_114 extends App_module_migration
{
    public function up()
    {
        update_option('styleflow_version', '1.1.4');
    }

    public function down()
    {
        // Upgrade only. Preserve all StyleFlow templates/settings and CRM data.
    }
}
