<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_135 extends App_module_migration
{
    public function up()
    {
        // Customer-library presentation repair only; no data is changed.
    }

    public function down()
    {
        // No destructive rollback.
    }
}
