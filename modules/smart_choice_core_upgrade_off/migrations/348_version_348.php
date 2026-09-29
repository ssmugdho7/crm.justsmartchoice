<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_348 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $this->setOption('smart_choice_core_upgrade_version', '3.4.8');
        $this->setOption('smart_choice_enterprise_current_version', '3.4.8');
        $this->setOption('smart_choice_enterprise_latest_version', '3.4.8');
        $this->setOption('smart_choice_core_latest_version', '3.4.8');
        $this->setOption('smart_choice_latest_version', '3.4.8');
        $this->setOption('smart_choice_core_last_patch', '3.4.8');
        $this->setOption('smart_choice_client_header_reset', '1');

        if ($CI->db->table_exists(db_prefix() . 'modules')) {
            $CI->db->where('module_name', 'smart_choice_core_upgrade')->update(db_prefix() . 'modules', ['installed_version' => '3.4.8']);
        }

        // Defensive: if a custom report table was deleted, recreate it.
        if (!$CI->db->table_exists(db_prefix() . 'smart_choice_reports')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_choice_reports` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `report_key` VARCHAR(191) NOT NULL,
                `report_name` VARCHAR(191) NOT NULL,
                `description` TEXT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `report_key` (`report_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
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
