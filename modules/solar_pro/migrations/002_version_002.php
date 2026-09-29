<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Solar Pro v1.0.1 upgrade marker.
 *
 * Routing/menu fixes are file-level changes.  No destructive schema change is
 * required.  Perfex requires a sequential migration whenever the module
 * version changes, including when no database query is necessary.
 */
class Migration_Version_002 extends App_module_migration
{
    public function up()
    {
        // Intentionally empty: preserve all v1.0.0 Solar Pro records/settings.
    }
}
