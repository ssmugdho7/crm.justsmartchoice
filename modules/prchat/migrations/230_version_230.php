<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat 2.3.0 upgrade-path stabilization.
 *
 * Code-only release. No schema changes and no option/API credential writes.
 */
class Migration_Version_230 extends App_module_migration
{
    public function up()
    {
        // Intentionally empty. Perfex records the migration completion.
    }

    public function down()
    {
        // Upgrade-only; existing data is preserved.
    }
}
