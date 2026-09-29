<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_150 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_core_search_index')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_core_search_index` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `source_table` VARCHAR(100) NOT NULL,
                `source_id` INT(11) NOT NULL DEFAULT 0,
                `source_area` VARCHAR(100) NOT NULL DEFAULT 'core',
                `record_title` VARCHAR(191) NOT NULL,
                `record_summary` MEDIUMTEXT NULL,
                `search_text` LONGTEXT NULL,
                `record_url` VARCHAR(500) NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'active',
                `last_indexed_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_source` (`source_table`, `source_id`),
                KEY `idx_source_area` (`source_area`),
                KEY `idx_status` (`status`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_core_diagnostics')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_core_diagnostics` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `diagnostic_area` VARCHAR(100) NOT NULL,
                `diagnostic_title` VARCHAR(191) NOT NULL,
                `diagnostic_status` VARCHAR(50) NOT NULL DEFAULT 'ok',
                `diagnostic_message` MEDIUMTEXT NULL,
                `checked_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_area` (`diagnostic_area`),
                KEY `idx_status` (`diagnostic_status`)
            ) " . $charset);
        }

        update_option('usi_smartchoice_seo_version', '1.5.0');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'usi_ai_core_diagnostics')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_core_diagnostics`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_core_search_index')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_core_search_index`'); }
    }
}
