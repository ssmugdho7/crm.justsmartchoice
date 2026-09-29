<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $tables = [
            db_prefix() . 'si_todos',
            db_prefix() . 'si_todos_category',
            db_prefix() . 'si_todos_settings',
        ];
        foreach ($tables as $table) {
            if ($CI->db->table_exists($table)) {
                $CI->db->query('ALTER TABLE `' . $table . '` ENGINE=InnoDB');
            }
        }
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
