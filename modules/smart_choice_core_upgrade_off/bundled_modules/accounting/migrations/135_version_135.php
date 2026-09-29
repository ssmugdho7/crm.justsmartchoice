<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_135 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Smart Choice Contractors patch - Version 1.4.0
        // Keeps bank-account creation stable on newer PHP/Perfex installs.
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
    }
}
