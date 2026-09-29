<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Compatibility bridge migration.
 * Intentionally non-destructive; preserves all existing subcontractor data.
 */
class Migration_Version_302 extends App_module_migration
{
    public function up()
    {
        return true;
    }

    public function down()
    {
        return true;
    }
}
