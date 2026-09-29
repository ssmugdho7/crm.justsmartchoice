<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_108 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        if ($CI->db->table_exists(db_prefix() . 'modules')) {
            $row = $CI->db->where('module_name', 'notes')->get(db_prefix() . 'modules')->row();
            if ($row) {
                $CI->db->where('module_name', 'notes')->update(db_prefix() . 'modules', [
                    'active' => 1,
                    'installed_version' => '1.0.8',
                ]);
            }
        }
    }
}
