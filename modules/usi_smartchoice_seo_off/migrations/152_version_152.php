<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_152 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_estimate_intelligence_runs')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_estimate_intelligence_runs` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `run_title` VARCHAR(191) NOT NULL,
                `related_estimate_id` INT(11) NOT NULL DEFAULT 0,
                `related_customer_id` INT(11) NOT NULL DEFAULT 0,
                `service_type` VARCHAR(100) NOT NULL DEFAULT 'general',
                `city` VARCHAR(100) NULL,
                `county` VARCHAR(100) NULL,
                `scope_summary` MEDIUMTEXT NULL,
                `historical_match_count` INT(11) NOT NULL DEFAULT 0,
                `confidence_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `tax_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `overhead_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `profit_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `grand_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `status` VARCHAR(50) NOT NULL DEFAULT 'draft',
                `created_by` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_related_estimate` (`related_estimate_id`),
                KEY `idx_customer` (`related_customer_id`),
                KEY `idx_service` (`service_type`),
                KEY `idx_status` (`status`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_estimate_intelligence_lines')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_estimate_intelligence_lines` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `run_id` INT(11) NOT NULL DEFAULT 0,
                `item_name` VARCHAR(191) NOT NULL,
                `item_description` MEDIUMTEXT NULL,
                `qty` DECIMAL(15,4) NOT NULL DEFAULT 1.0000,
                `unit` VARCHAR(50) NULL,
                `unit_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `line_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `source_type` VARCHAR(100) NOT NULL DEFAULT 'manual',
                `source_id` INT(11) NOT NULL DEFAULT 0,
                `confidence_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_run` (`run_id`),
                KEY `idx_source` (`source_type`, `source_id`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_estimate_price_patterns')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_estimate_price_patterns` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `pattern_name` VARCHAR(191) NOT NULL,
                `service_type` VARCHAR(100) NOT NULL DEFAULT 'general',
                `city` VARCHAR(100) NULL,
                `county` VARCHAR(100) NULL,
                `sample_count` INT(11) NOT NULL DEFAULT 0,
                `average_unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `average_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `low_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `high_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `notes` MEDIUMTEXT NULL,
                `last_rebuilt_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_service` (`service_type`),
                KEY `idx_location` (`city`, `county`)
            ) " . $charset);
        }

        update_option('usi_smartchoice_seo_version', '1.5.2');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'usi_ai_estimate_price_patterns')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_estimate_price_patterns`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_estimate_intelligence_lines')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_estimate_intelligence_lines`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_estimate_intelligence_runs')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_estimate_intelligence_runs`'); }
    }
}
