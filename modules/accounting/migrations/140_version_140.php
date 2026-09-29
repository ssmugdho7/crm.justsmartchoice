<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_140 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'acc_accounts')) {
            if (!$CI->db->field_exists('bank_name', db_prefix() . 'acc_accounts')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'acc_accounts` ADD COLUMN `bank_name` varchar(255) NULL');
            }
            if (!$CI->db->field_exists('bank_account', db_prefix() . 'acc_accounts')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'acc_accounts` ADD COLUMN `bank_account` TEXT NULL');
            }
            if (!$CI->db->field_exists('bank_routing', db_prefix() . 'acc_accounts')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'acc_accounts` ADD COLUMN `bank_routing` TEXT NULL');
            }
            if (!$CI->db->field_exists('address_line_1', db_prefix() . 'acc_accounts')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'acc_accounts` ADD COLUMN `address_line_1` TEXT NULL');
            }
            if (!$CI->db->field_exists('active', db_prefix() . 'acc_accounts')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . "acc_accounts` ADD COLUMN `active` INT(11) NOT NULL DEFAULT '1'");
            }
            if (!$CI->db->field_exists('default_account', db_prefix() . 'acc_accounts')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . "acc_accounts` ADD COLUMN `default_account` INT(11) NOT NULL DEFAULT '0'");
            }
        }

        if (function_exists('add_option')) {
            add_option('acc_smartchoice_bank_account_fix_applied', '1');
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
