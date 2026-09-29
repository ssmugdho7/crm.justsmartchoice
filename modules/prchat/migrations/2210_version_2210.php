<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.2.10 migration.
 *
 * Perfex derives the migration identifier for module version 2.2.10 by
 * removing the dots, resulting in migration version 2210. This release is
 * code-only; no schema, option, credential, upload, message, group, or
 * permission data changes are required.
 */
class Migration_Version_2210 extends App_module_migration
{
    public function up()
    {
        // Intentionally empty. Perfex records successful migration completion.
    }

    public function down()
    {
        // Upgrade-only; no destructive rollback.
    }
}
