<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * StyleFlow 1.1.1 compatibility migration.
 *
 * Database/schema work is intentionally not executed here. Perfex module
 * migrations must remain fast and retry-safe; installation/repair belongs
 * to install.php.
 */
class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        update_option('styleflow_version', '1.1.1');
    }

    public function down()
    {
        // Upgrade only. Preserve all StyleFlow and CRM data.
    }
}
