<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * StyleFlow 1.1.8 compatibility migration.
 *
 * Code-only repair: CRM Settings panel registration, clean sales-table labels,
 * and bundled Smart Choice preview assets. No existing records are changed.
 */
class Migration_Version_118 extends App_module_migration
{
    public function up()
    {
        add_option('styleflow_version', '1.1.8');
        update_option('styleflow_version', '1.1.8');
    }

    public function down()
    {
        // Upgrade-only module. Existing configuration/data is intentionally kept.
    }
}
