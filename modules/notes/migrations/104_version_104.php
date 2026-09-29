<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_104 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'notes';

        if (!$CI->db->table_exists($table)) {
            return;
        }

        $fields = $CI->db->list_fields($table);

        $columns = [
            'note_color'               => "ALTER TABLE `$table` ADD `note_color` VARCHAR(20) NULL DEFAULT '#00A651'",
            'priority'                 => "ALTER TABLE `$table` ADD `priority` VARCHAR(20) NULL DEFAULT 'medium'",
            'note_visibility'          => "ALTER TABLE `$table` ADD `note_visibility` VARCHAR(20) NULL DEFAULT 'normal'",
            'assigned_staff_id'        => "ALTER TABLE `$table` ADD `assigned_staff_id` INT(11) NULL DEFAULT NULL",
            'attachment'               => "ALTER TABLE `$table` ADD `attachment` VARCHAR(255) NULL DEFAULT NULL",
            'attachment_original_name' => "ALTER TABLE `$table` ADD `attachment_original_name` VARCHAR(255) NULL DEFAULT NULL",
        ];

        foreach ($columns as $column => $sql) {
            if (!in_array($column, $fields)) {
                $CI->db->query($sql);
            }
        }

        if (!is_dir(FCPATH . 'modules/notes/uploads/')) {
            @mkdir(FCPATH . 'modules/notes/uploads/', 0755, true);
        }

        if (!file_exists(FCPATH . 'modules/notes/uploads/index.html')) {
            @file_put_contents(FCPATH . 'modules/notes/uploads/index.html', '');
        }
    }

    public function down()
    {
        // Do not drop columns. This module stores all notes in the original Perfex CRM notes table.
    }
}
