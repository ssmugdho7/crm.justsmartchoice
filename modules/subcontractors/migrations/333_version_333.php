<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_333 extends App_module_migration
{
    public function up()
    {
        // Performance-only controller/model upgrade. No schema change is required.
        update_option('subcontractors_version', '3.3.3');
    }
}
