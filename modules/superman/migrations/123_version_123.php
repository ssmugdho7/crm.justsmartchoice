<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_123 extends App_module_migration
{
    public function up()
    {
        // Version marker for Superman 1.2.3 migration sequence repair.
        return true;
    }

    public function down()
    {
        return true;
    }
}
