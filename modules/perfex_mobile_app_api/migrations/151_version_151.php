<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_151 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (!option_exists('perfex_mobile_app_api_enabled')) {
            add_option('perfex_mobile_app_api_enabled', 1);
        }
        if (!option_exists('perfex_mobile_app_api_require_auth_key')) {
            add_option('perfex_mobile_app_api_require_auth_key', 0);
        }
        if (!option_exists('perfex_mobile_app_api_log_responses')) {
            add_option('perfex_mobile_app_api_log_responses', 0);
        }

        $table = db_prefix() . 'api_logs';
        if (!$CI->db->table_exists($table)) {
            $CI->db->query('CREATE TABLE `' . $table . '` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `url` VARCHAR(500) NOT NULL,
                `data` LONGTEXT NULL,
                `response` LONGTEXT NULL,
                `origin_from` VARCHAR(100) NOT NULL DEFAULT "app",
                `headers` LONGTEXT NULL,
                `ip_address` VARCHAR(100) NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        } else {
            if (!$CI->db->field_exists('headers', $table)) {
                $CI->db->query('ALTER TABLE `' . $table . '` ADD `headers` LONGTEXT NULL AFTER `origin_from`');
            }
            if (!$CI->db->field_exists('ip_address', $table)) {
                $CI->db->query('ALTER TABLE `' . $table . '` ADD `ip_address` VARCHAR(100) NULL AFTER `headers`');
            }
            if (!$CI->db->field_exists('created_at', $table)) {
                $CI->db->query('ALTER TABLE `' . $table . '` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
            }
            $fields = $CI->db->field_data($table);
            $types = [];
            foreach ($fields as $field) {
                $types[$field->name] = strtolower((string) $field->type);
            }
            if (isset($types['data']) && $types['data'] !== 'longtext') {
                $CI->db->query('ALTER TABLE `' . $table . '` MODIFY `data` LONGTEXT NULL');
            }
            if (isset($types['response']) && $types['response'] !== 'longtext') {
                $CI->db->query('ALTER TABLE `' . $table . '` MODIFY `response` LONGTEXT NULL');
            }
        }
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
