<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_144 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        add_option('acc_finance_setup_sync_enabled', '1');
        add_option('acc_safe_mode_enabled', '0');
        add_option('acc_backup_last_file', '');
        add_option('acc_backup_last_checked', '');

        if (!$CI->db->table_exists(db_prefix() . 'acc_finance_setup_sync_logs')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "acc_finance_setup_sync_logs` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `sync_type` VARCHAR(100) NOT NULL DEFAULT 'finance_setup',
                `source_table` VARCHAR(191) NULL,
                `source_id` INT(11) NULL,
                `target_table` VARCHAR(191) NULL,
                `target_id` INT(11) NULL,
                `records_created` INT(11) NOT NULL DEFAULT 0,
                `records_updated` INT(11) NOT NULL DEFAULT 0,
                `status` VARCHAR(50) NOT NULL DEFAULT 'success',
                `message` TEXT NULL,
                `datecreated` DATETIME NULL,
                `addedfrom` INT(11) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        if (!$CI->db->table_exists(db_prefix() . 'acc_backup_logs')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "acc_backup_logs` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `backup_file` TEXT NULL,
                `backup_action` VARCHAR(50) NOT NULL DEFAULT 'created',
                `status` VARCHAR(50) NOT NULL DEFAULT 'success',
                `message` TEXT NULL,
                `datecreated` DATETIME NULL,
                `addedfrom` INT(11) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
