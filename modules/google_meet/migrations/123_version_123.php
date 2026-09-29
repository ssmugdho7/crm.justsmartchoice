<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_123 extends App_module_migration
{
    public function up()
    {
        // Customer portal redirect-loop repair. No destructive schema changes.
    }

    public function down()
    {
        // Intentionally non-destructive.
    }
}
