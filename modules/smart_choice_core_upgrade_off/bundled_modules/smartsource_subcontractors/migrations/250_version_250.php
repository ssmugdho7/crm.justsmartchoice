<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_250 extends App_module_migration
{
    public function up()
    {
        update_option('smartsource_subcontractors_version', '2.5.0');
    }
}
