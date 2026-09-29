<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        update_option('crm_file_explorer_manager_version', '1.0.1');
    }
}
