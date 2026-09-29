<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_142 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (!$CI->db->table_exists(db_prefix() . 'acc_quickbooks_logs')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "acc_quickbooks_logs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `direction` varchar(30) NOT NULL,
                `file_type` varchar(30) NOT NULL,
                `record_type` varchar(80) NULL,
                `file_name` varchar(255) NULL,
                `total_rows` int(11) NOT NULL DEFAULT 0,
                `valid_rows` int(11) NOT NULL DEFAULT 0,
                `error_rows` int(11) NOT NULL DEFAULT 0,
                `created_by` int(11) NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        add_option('acc_quickbooks_desktop_enabled', '1');
        add_option('acc_quickbooks_iif_encoding', 'UTF-8');
        add_option('acc_quickbooks_default_export_type', 'iif');

        return true;
    }
}
