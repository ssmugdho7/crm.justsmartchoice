<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_123 extends App_module_migration
{
    public function up()
    {
        // Code-only save-flow repair.
        // No schema, data, settings, uploads, API values, or existing notes are modified.
    }

    public function down()
    {
        // No destructive rollback actions.
    }
}
