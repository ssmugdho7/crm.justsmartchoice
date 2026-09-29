<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_161 extends App_module_migration
{
    public function up()
    {
        // Final migration marker for Smart Choice Contractors v1.6.1.
        // Keeps the module revision above the previous failing 160 target.
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
