<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_139 extends App_module_migration
{
    public function up()
    {
        // Compatibility shim. Keeps Perfex database upgrade from failing when older installs jump through version 139.
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
