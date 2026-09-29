<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_108 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (!$CI->db->field_exists('smartchoice_assignee_type', db_prefix() . 'commission')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . "commission`
                ADD COLUMN `smartchoice_assignee_type` VARCHAR(30) NULL DEFAULT NULL AFTER `is_client`,
                ADD COLUMN `smartchoice_role_label` VARCHAR(100) NULL DEFAULT NULL AFTER `smartchoice_assignee_type`,
                ADD COLUMN `smartchoice_commission_mode` VARCHAR(30) NULL DEFAULT NULL AFTER `smartchoice_role_label`,
                ADD COLUMN `smartchoice_commission_rate` DECIMAL(15,2) NULL DEFAULT NULL AFTER `smartchoice_commission_mode`,
                ADD COLUMN `smartchoice_source_type` VARCHAR(50) NULL DEFAULT NULL AFTER `smartchoice_commission_rate`,
                ADD COLUMN `smartchoice_notes` TEXT NULL AFTER `smartchoice_source_type`,
                ADD COLUMN `smartchoice_created_by` INT(11) NULL DEFAULT NULL AFTER `smartchoice_notes`
            ");
        }

        if (!$CI->db->field_exists('paid', db_prefix() . 'commission')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . "commission` ADD COLUMN `paid` INT(11) NOT NULL DEFAULT 0");
        }
    }
}
