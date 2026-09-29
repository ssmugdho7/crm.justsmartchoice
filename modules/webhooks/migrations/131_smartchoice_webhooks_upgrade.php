<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Smartchoice_webhooks_upgrade extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();

        add_option('webhooks_enabled', '1');
        add_option('webhooks_log_enabled', '1');
        add_option('webhooks_retry_failed', '1');
        add_option('webhooks_default_timeout', '30');

        $candidateTables = [
            db_prefix() . 'webhooks',
            db_prefix() . 'webhook',
            db_prefix() . 'webhooks_logs',
            db_prefix() . 'webhook_logs',
        ];

        foreach ($candidateTables as $table) {
            if (!$CI->db->table_exists($table)) {
                continue;
            }

            if (!$CI->db->field_exists('created_by', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `created_by` INT(11) NULL DEFAULT NULL");
            }

            if (!$CI->db->field_exists('smartchoice_notes', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `smartchoice_notes` TEXT NULL");
            }

            if (!$CI->db->field_exists('is_active', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `is_active` TINYINT(1) NOT NULL DEFAULT 1");
            }
        }
    }
}
