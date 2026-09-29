<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * StyleFlow 1.1.3 database-upgrade reliability migration.
 *
 * This migration is intentionally lightweight. It performs no CREATE/ALTER,
 * no template seeding, and no installer recursion. It only records the module
 * version so the Perfex database-upgrade action can complete immediately.
 */
class Migration_Version_113 extends App_module_migration
{
    public function up()
    {
        update_option('styleflow_version', '1.1.3');
    }

    public function down()
    {
        // Upgrade only. Preserve templates, settings, and sales-document data.
    }
}
