<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        if ($CI->db->table_exists(db_prefix() . 'modules')) {
            $CI->db->where('module_name', 'supplier')->update(db_prefix() . 'modules', ['installed_version' => '1.1.0']);
        }
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
