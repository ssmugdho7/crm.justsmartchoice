<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_392 extends App_migration
{
    public function up()
    {
        $CI = &get_instance();
        update_option('smart_choice_crm_build', '3.9.2');
        update_option('smart_choice_crm_current_version', '3.9.2');

        foreach (['estimates', 'proposals', 'invoices'] as $entity) {
            $table = db_prefix() . $entity;
            if ($CI->db->table_exists($table) && !$CI->db->field_exists('sc_custom_status', $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD `sc_custom_status` VARCHAR(100) NULL DEFAULT NULL");
            }
        }

        $statuses = db_prefix() . 'sc_sales_statuses';
        if (!$CI->db->table_exists($statuses)) {
            $CI->db->query("CREATE TABLE `{$statuses}` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `document_type` VARCHAR(20) NOT NULL,
                `name` VARCHAR(100) NOT NULL,
                `color` VARCHAR(20) NOT NULL DEFAULT '#169179',
                `sort_order` INT NOT NULL DEFAULT 0,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_sc_sales_status` (`document_type`,`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }
    }

    public function down()
    {
        // Upgrade-only migration. Existing sales data is intentionally preserved.
    }
}
