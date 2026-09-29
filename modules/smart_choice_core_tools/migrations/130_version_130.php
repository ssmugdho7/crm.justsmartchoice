<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_130 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        update_option('clients_default_theme', 'smartchoice');

        if ($CI->db->table_exists(db_prefix() . 'modules')) {
            foreach ([
                'smart_choice_core_tools' => '1.3.0',
                'notes' => '1.0.8',
            ] as $moduleName => $version) {
                $row = $CI->db->where('module_name', $moduleName)->get(db_prefix() . 'modules')->row();
                if ($row) {
                    $CI->db->where('module_name', $moduleName)->update(db_prefix() . 'modules', [
                        'active' => 1,
                        'installed_version' => $version,
                    ]);
                }
            }
        }
    }
}
