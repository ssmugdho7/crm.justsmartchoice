<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_281 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $table = $prefix . 'smartsource_subcontractor_contract_comments';
        if ($CI->db->table_exists($table)) {
            $fields = $CI->db->list_fields($table);
            if (!in_array('is_internal', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $table . '` ADD `is_internal` TINYINT(1) NOT NULL DEFAULT 1 AFTER `comment`');
            }
            if (!in_array('customer_name', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $table . '` ADD `customer_name` VARCHAR(191) NULL AFTER `is_internal`');
            }
            if (!in_array('customer_email', $fields, true)) {
                $CI->db->query('ALTER TABLE `' . $table . '` ADD `customer_email` VARCHAR(191) NULL AFTER `customer_name`');
            }
        }
        update_option('smartsource_subcontractors_module_version', '2.8.1');
        update_option('smartsource_crm_client_base_url', 'https://crm.justsmartchoice.com/');
        return true;
    }

    public function down()
    {
        return true;
    }
}
