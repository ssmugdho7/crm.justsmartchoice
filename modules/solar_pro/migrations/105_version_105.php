<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Solar Pro v1.0.5 HMVC/core-migration isolation repair.
 *
 * No Solar Pro data is removed or rewritten. The module migration marker is
 * advanced only after the compatibility bridge files have been deployed.
 */
class Migration_Version_105 extends App_module_migration
{
    public function up()
    {
        // No destructive schema change required.
    }

    public function down()
    {
        // Non-destructive rollback marker.
    }
}
