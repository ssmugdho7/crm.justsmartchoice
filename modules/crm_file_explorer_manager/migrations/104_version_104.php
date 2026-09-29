<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_104 extends App_module_migration
{
    public function up()
    {
        update_option('crm_file_explorer_manager_version', '1.0.4');
        if (function_exists('log_activity')) {
            log_activity('CRM File Explorer Manager upgraded to 1.0.4');
        }
    }

    public function down()
    {
        // Upgrade only. No files or indexed records are removed.
    }
}
