<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_328 extends App_module_migration
{
    public function up()
    {
        // Routing-only release. No schema change is required.
    }

    public function down()
    {
        // Intentionally non-destructive.
    }
}
