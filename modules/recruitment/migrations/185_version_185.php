<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_185 extends App_module_migration
{
    public function up()
    {
        // Runtime/UI repair only. No schema or data changes.
    }

    public function down()
    {
        // Upgrade-only migration.
    }
}
