<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_844 extends App_module_migration
{
    public function up()
    {
        // Bridge migration to keep the Perfex module migration sequence continuous.
        // No database changes are required for this version.
        return true;
    }

    public function down()
    {
        // Safe rollback method required by Perfex.
        return true;
    }
}
