<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_112 extends App_module_migration
{
    public function up()
    {
        // Compatibility migration for module version 1.1.2.
        // No schema changes required.
    }

    public function down()
    {
        // Intentionally left blank to allow safe rollback checks.
    }
}
