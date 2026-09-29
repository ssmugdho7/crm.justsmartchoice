<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Legacy StyleFlow 1.1.0 migration retained for compatibility.
 *
 * Schema creation and template seeding are handled by install.php. Keeping
 * this migration lightweight prevents duplicate installer work and HTTP 500
 * errors during database upgrade.
 */
class Migration_Version_001 extends App_module_migration
{
    public function up()
    {
        update_option('styleflow_version', '1.1.0');
    }

    public function down()
    {
        // Upgrade only. Preserve all data.
    }
}
