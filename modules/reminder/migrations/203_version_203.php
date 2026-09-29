<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_203 extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();
        $table = db_prefix() . 'reminders';
        if (!$CI->db->table_exists($table)) {
            return;
        }
        if (!$CI->db->field_exists('assignment_type', $table)) {
            $CI->db->query("ALTER TABLE `" . $table . "` ADD `assignment_type` VARCHAR(20) NOT NULL DEFAULT 'self'");
        }
        if (!$CI->db->field_exists('assigned_to', $table)) {
            $CI->db->query("ALTER TABLE `" . $table . "` ADD `assigned_to` INT(11) NULL DEFAULT NULL");
        }
        if (!$CI->db->field_exists('created_by', $table)) {
            $CI->db->query("ALTER TABLE `" . $table . "` ADD `created_by` INT(11) NULL DEFAULT NULL");
        }
        if ($CI->db->field_exists('customer', $table)) {
            $CI->db->query("ALTER TABLE `" . $table . "` MODIFY `customer` INT(11) NULL DEFAULT 0");
        }
        if ($CI->db->field_exists('contact', $table)) {
            $CI->db->query("ALTER TABLE `" . $table . "` MODIFY `contact` INT(11) NULL DEFAULT 0");
        }
        if ($CI->db->field_exists('rel_id', $table)) {
            $CI->db->query("ALTER TABLE `" . $table . "` MODIFY `rel_id` INT(11) NULL DEFAULT 0");
        }
        if ($CI->db->field_exists('rel_type', $table)) {
            $CI->db->query("ALTER TABLE `" . $table . "` MODIFY `rel_type` VARCHAR(40) NULL DEFAULT ''");
        }
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
