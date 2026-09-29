<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_141 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'acc_accounts')) {
            $columns = [
                'bank_name'       => 'varchar(255) NULL',
                'bank_account'    => 'TEXT NULL',
                'bank_routing'    => 'TEXT NULL',
                'address_line_1'  => 'TEXT NULL',
                'active'          => "INT(11) NOT NULL DEFAULT '1'",
                'default_account' => "INT(11) NOT NULL DEFAULT '0'",
            ];

            foreach ($columns as $column => $definition) {
                if (!$CI->db->field_exists($column, db_prefix() . 'acc_accounts')) {
                    $CI->db->query('ALTER TABLE `' . db_prefix() . 'acc_accounts` ADD COLUMN `' . $column . '` ' . $definition);
                }
            }
        }

        add_option('acc_smartchoice_quickbooks_style_enabled', '1');
        update_option('acc_smartchoice_bank_account_fix_applied', '1');
        update_option('acc_smartchoice_version', '1.4.1');

        return true;
    }

    public function down()
    {
        return true;
    }
}
