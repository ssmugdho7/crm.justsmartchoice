<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_100 extends App_module_migration
{
    public function up()
    {
        // Base installer creates the required tables during module activation.
    }

    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }
}
