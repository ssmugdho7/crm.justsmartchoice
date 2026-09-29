<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        update_option('crm_file_explorer_manager_version', '1.0.2');
        if (get_option('crm_file_explorer_manager_root_path') === '') {
            add_option('crm_file_explorer_manager_root_path', FCPATH);
        }
        if (get_option('crm_file_explorer_manager_max_scan_files') === '') {
            add_option('crm_file_explorer_manager_max_scan_files', '5000');
        }
    }
}
