<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_346 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $this->setOption('smart_choice_core_upgrade_version', '3.4.6');
        $this->setOption('smart_choice_enterprise_current_version', '3.4.6');
        $this->setOption('smart_choice_enterprise_latest_version', '3.4.6');
        $this->setOption('smart_choice_core_last_patch', '3.4.6');

        if ($CI->db->table_exists(db_prefix() . 'modules')) {
            $CI->db->where('module_name', 'smart_choice_core_upgrade')->update(db_prefix() . 'modules', ['installed_version' => '3.4.6']);
        }
    }

    private function setOption($name, $value)
    {
        $CI = &get_instance();
        if ($CI->db->where('name', $name)->count_all_results(db_prefix() . 'options') == 0) {
            $CI->db->insert(db_prefix() . 'options', ['name' => $name, 'value' => $value, 'autoload' => 1]);
        } else {
            $CI->db->where('name', $name)->update(db_prefix() . 'options', ['value' => $value]);
        }
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
