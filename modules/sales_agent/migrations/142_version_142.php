<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_142 extends App_module_migration
{
    public function up()
    {
        // Smart Choice compatibility shim. Keeps Perfex module migration path continuous.
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
