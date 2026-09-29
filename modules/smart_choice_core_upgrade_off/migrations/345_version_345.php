<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_345 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $exists = $CI->db->where('name', 'smart_choice_enterprise_current_version')->get(db_prefix() . 'options')->row();
        if ($exists) {
            $CI->db->where('name', 'smart_choice_enterprise_current_version')->update(db_prefix() . 'options', ['value' => '3.4.5']);
        } else {
            $CI->db->insert(db_prefix() . 'options', ['name' => 'smart_choice_enterprise_current_version', 'value' => '3.4.5', 'autoload' => 1]);
        }
        $this->addOption('smart_choice_core_last_patch', '3.4.5');
        $this->addOption('smart_choice_enterprise_latest_version', '3.4.5');
        $this->addOption('smart_choice_core_upgrade_version', '3.4.5');
    }

    private function addOption($name, $value)
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
