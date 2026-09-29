<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_112 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'notes';

        if (!$CI->db->table_exists($table)) {
            log_message('error', 'Notes migration 112: native notes table is missing: ' . $table);
            return true;
        }

        $columns = [
            'title'                    => "ALTER TABLE `{$table}` ADD `title` VARCHAR(191) NULL AFTER `id`",
            'note_color'               => "ALTER TABLE `{$table}` ADD `note_color` VARCHAR(20) NULL DEFAULT '#00A651'",
            'priority'                 => "ALTER TABLE `{$table}` ADD `priority` VARCHAR(20) NULL DEFAULT 'medium'",
            'note_visibility'          => "ALTER TABLE `{$table}` ADD `note_visibility` VARCHAR(20) NULL DEFAULT 'normal'",
            'assigned_staff_id'        => "ALTER TABLE `{$table}` ADD `assigned_staff_id` INT(11) NULL DEFAULT NULL",
            'attachment'               => "ALTER TABLE `{$table}` ADD `attachment` VARCHAR(255) NULL DEFAULT NULL",
            'attachment_original_name' => "ALTER TABLE `{$table}` ADD `attachment_original_name` VARCHAR(255) NULL DEFAULT NULL",
        ];

        foreach ($columns as $column => $sql) {
            if (!$CI->db->field_exists($column, $table)) {
                $CI->db->query($sql);
            }
        }

        // Preserve all legacy notes. Add a readable title only when one does not exist.
        if ($CI->db->field_exists('title', $table)) {
            $CI->db->query("UPDATE `{$table}` SET `title` = CONCAT('Legacy Note #', `id`) WHERE `title` IS NULL OR TRIM(`title`) = ''");
        }

        return true;
    }

    public function down()
    {
        // Preserve native notes and all Smart Choice metadata.
    }
}
