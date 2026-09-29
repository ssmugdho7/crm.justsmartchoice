<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Solar Pro v1.0.4 compatibility marker.
 *
 * The database schema is intentionally unchanged. This migration advances
 * only the module's installed version after the HMVC/core-migration isolation
 * repair. Existing Solar Pro data and settings are preserved.
 */
class Migration_Version_104 extends App_module_migration
{
    public function up()
    {
        // No schema change required.
    }

    public function down()
    {
        // Non-destructive rollback marker.
    }
}
