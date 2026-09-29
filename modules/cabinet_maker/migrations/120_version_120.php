<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_120 extends App_module_migration
{
    public function up()
    {
        // Compatibility bridge. Historical schema work is consolidated in
        // migration 126 so upgrades never repeat installers or catalog seeds.
        update_option('cabinet_maker_migration_checkpoint', '120');
    }

    public function down()
    {
        // Intentionally non-destructive.
    }
}
