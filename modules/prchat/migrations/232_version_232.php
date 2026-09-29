<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.3.2 - message action visibility/UI compatibility release.
 *
 * Code-only migration. No schema, settings, credentials, uploads, messages,
 * groups, or permissions are modified.
 */
class Migration_Version_232 extends App_module_migration
{
    public function up()
    {
        // Code-only release. Intentionally no database changes.
    }

    public function down()
    {
        // Upgrade-only.
    }
}
