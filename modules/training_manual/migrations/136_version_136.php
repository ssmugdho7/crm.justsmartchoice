<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Training Manual 1.3.6
 *
 * Routing and post-save workflow repair only. No destructive schema or data changes.
 */
class Migration_Version_136 extends App_module_migration
{
    public function up()
    {
        // Keep a module-owned repair/version marker without overwriting user settings or content.
        update_option('training_manual_current_version', '1.3.6');
    }

    public function down()
    {
        // Upgrade-only migration. Existing manuals, articles, settings and uploads are preserved.
    }
}
