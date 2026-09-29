<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_332 extends App_module_migration
{
    public function up()
    {
        // Upgrade-only repair. No destructive schema changes are required.
        update_option('subcontractors_version', '3.3.2');
    }
}
