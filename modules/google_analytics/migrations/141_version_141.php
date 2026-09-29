<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_141 extends App_module_migration
{
    public function up()
    {
        // Compatibility bridge for the original 1.0.0 installation.
        // Intentionally no database work here.
        return true;
    }

    public function down()
    {
        // Non-destructive rollback.
        return true;
    }
}
