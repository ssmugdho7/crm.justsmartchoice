<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_114 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        require_once module_dir_path('notes', 'install.php');

        // Make legacy records readable without changing their relation data.
        $table = db_prefix() . 'notes';
        if ($CI->db->table_exists($table) && $CI->db->field_exists('title', $table)) {
            $CI->db->query("UPDATE `{$table}` SET `title` = CONCAT('Legacy Note #', `id`) WHERE `title` IS NULL OR TRIM(`title`) = ''");
        }
    }

    public function down()
    {
        // Non-destructive by design.
    }
}
