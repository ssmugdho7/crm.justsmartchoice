<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_270 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'sales_center_commissions';
        if ($CI->db->table_exists($table)) {
            if (!$CI->db->field_exists('issue_notes', $table)) {
                $CI->db->query('ALTER TABLE `' . $table . '` ADD `issue_notes` text NULL');
            }
            if (!$CI->db->field_exists('manager_id', $table)) {
                $CI->db->query('ALTER TABLE `' . $table . '` ADD `manager_id` int(11) NULL');
            }
            if (!$CI->db->field_exists('department_id', $table)) {
                $CI->db->query('ALTER TABLE `' . $table . '` ADD `department_id` int(11) NULL');
            }
        }
    }
}
