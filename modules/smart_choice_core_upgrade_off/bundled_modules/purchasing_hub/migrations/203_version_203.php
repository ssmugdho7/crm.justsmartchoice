<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_203 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Safe idempotent install/repair. Do not overwrite or delete existing purchasing records.
        if (!$CI->db->table_exists(db_prefix() . 'pur_unit')) {
            require_once(module_dir_path('purchasing_hub', 'install.php'));
        }

        update_option('purchasing_hub_module_version', '2.0.4');
        add_option('purchasing_hub_primary_color', '#169179');
        add_option('purchasing_hub_accent_color', '#f47c20');
    }

    public function down()
    {
        // Safe rollback placeholder. Do not drop purchasing data automatically.
        return true;
    }
}
