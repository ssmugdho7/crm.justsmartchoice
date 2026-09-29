<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_148 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_command_center_cards')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_command_center_cards` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `card_title` VARCHAR(191) NOT NULL,
                `card_area` VARCHAR(100) NOT NULL DEFAULT 'dashboard',
                `card_description` MEDIUMTEXT NULL,
                `card_status` VARCHAR(50) NOT NULL DEFAULT 'active',
                `sort_order` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_card_area` (`card_area`),
                KEY `idx_card_status` (`card_status`)
            ) " . $charset);
        }

        update_option('usi_smartchoice_seo_version', '1.4.8');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'usi_ai_command_center_cards')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_command_center_cards`'); }

    }
}
