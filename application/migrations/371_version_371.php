<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_371 extends CI_Migration
{
    public function up()
    {
        $contracts = db_prefix() . 'contracts';
        if ($this->db->table_exists($contracts) && !$this->db->field_exists('initials', $contracts)) {
            $this->db->query("ALTER TABLE `{$contracts}` ADD `initials` VARCHAR(12) NULL DEFAULT NULL AFTER `signature`");
        }

        $dedupe = db_prefix() . 'sc_email_dedupe';
        if (!$this->db->table_exists($dedupe)) {
            $this->db->query("CREATE TABLE `{$dedupe}` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `dedupe_key` CHAR(40) NOT NULL,
                `template_slug` VARCHAR(100) NOT NULL,
                `rel_type` VARCHAR(50) NULL,
                `rel_id` INT NOT NULL DEFAULT 0,
                `recipient` VARCHAR(191) NOT NULL DEFAULT '',
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `dedupe_key_unique` (`dedupe_key`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        }

        $customFields = [
            ['Customer & Project', 'Project Address', 'input', 10],
            ['Customer & Project', 'Project Scope Summary', 'textarea', 20],
            ['Payment Information', 'Contract Total', 'input', 30],
            ['Payment Information', 'Down Payment', 'input', 40],
            ['Payment Information', 'Partial Payment Schedule', 'textarea', 50],
            ['Payment Information', 'Remaining Balance', 'input', 60],
            ['Payment Information', 'Final Payment', 'input', 70],
            ['Financing', 'Financing Option', 'input', 80],
            ['Financing', 'Financing Terms', 'textarea', 90],
        ];
        $cfTable = db_prefix() . 'customfields';
        if ($this->db->table_exists($cfTable)) {
            foreach ($customFields as $field) {
                $name = $field[1];
                $exists = $this->db->where('fieldto', 'contracts')->where('name', $name)->count_all_results($cfTable);
                if (!$exists) {
                    $row = ['fieldto'=>'contracts','name'=>$name,'required'=>0,'type'=>$field[2],'options'=>'','field_order'=>$field[3],'active'=>1];
                    if ($this->db->field_exists('show_on_table', $cfTable)) $row['show_on_table'] = 0;
                    if ($this->db->field_exists('show_on_client_portal', $cfTable)) $row['show_on_client_portal'] = 1;
                    if ($this->db->field_exists('disalow_client_to_edit', $cfTable)) $row['disalow_client_to_edit'] = 1;
                    if ($this->db->field_exists('only_admin', $cfTable)) $row['only_admin'] = 0;
                    if ($this->db->field_exists('bs_column', $cfTable)) $row['bs_column'] = 6;
                    if ($this->db->field_exists('default_value', $cfTable)) $row['default_value'] = '';
                    $this->db->insert($cfTable, $row);
                }
            }
        }

        update_option('smart_choice_crm_build', '3.7.1 SC Contract & Portal Repair');
        update_option('smart_choice_core_upgrade_applied', '371');
        update_option('sc_contract_required_title_word', 'RESIDENCE');
        update_option('sc_contract_initials_enabled', '1');
        update_option('sc_email_event_deduplication_enabled', '1');
    }

    public function down() {}
}
