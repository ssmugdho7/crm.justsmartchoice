<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * StyleFlow 1.1.6 navigation/template recovery migration.
 *
 * Code-only migration. Template recovery is insert-missing-only at runtime;
 * existing template rows, edits, assignments, CRM data and settings are kept.
 */
class Migration_Version_116 extends App_module_migration
{
    public function up()
    {
        update_option('styleflow_version', '1.1.6');
    }

    public function down()
    {
        // Upgrade only. Preserve all StyleFlow and CRM data.
    }
}
