<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_103 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'notes';

        if (!$CI->db->table_exists($table)) {
            return;
        }

        $fields = $CI->db->list_fields($table);

        if (!in_array('note_color', $fields)) {
            $CI->db->query("ALTER TABLE `$table` ADD `note_color` VARCHAR(20) NULL DEFAULT '#00A651'");
        }

        if (!in_array('priority', $fields)) {
            $CI->db->query("ALTER TABLE `$table` ADD `priority` VARCHAR(20) NULL DEFAULT 'medium'");
        }

        if (!in_array('note_visibility', $fields)) {
            $CI->db->query("ALTER TABLE `$table` ADD `note_visibility` VARCHAR(20) NULL DEFAULT 'normal'");
        }

        if (!in_array('assigned_staff_id', $fields)) {
            $CI->db->query("ALTER TABLE `$table` ADD `assigned_staff_id` INT(11) NULL DEFAULT NULL");
        }

        if (!in_array('attachment', $fields)) {
            $CI->db->query("ALTER TABLE `$table` ADD `attachment` VARCHAR(255) NULL DEFAULT NULL");
        }

        if (!in_array('attachment_original_name', $fields)) {
            $CI->db->query("ALTER TABLE `$table` ADD `attachment_original_name` VARCHAR(255) NULL DEFAULT NULL");
        }

        if (!is_dir(FCPATH . 'modules/notes/uploads/')) {
            @mkdir(FCPATH . 'modules/notes/uploads/', 0755, true);
        }
    }

    public function down()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'notes';

        if (!$CI->db->table_exists($table)) {
            return;
        }

        foreach (['note_color', 'priority', 'note_visibility', 'assigned_staff_id', 'attachment', 'attachment_original_name'] as $column) {
            if ($CI->db->field_exists($column, $table)) {
                $CI->db->query("ALTER TABLE `$table` DROP COLUMN `$column`");
            }
        }
    }
}
