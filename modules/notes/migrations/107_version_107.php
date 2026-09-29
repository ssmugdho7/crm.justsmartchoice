<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_107 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'notes';
        if ($CI->db->table_exists($table) && !$CI->db->field_exists('title', $table)) {
            $CI->db->query('ALTER TABLE `' . $table . '` ADD `title` VARCHAR(191) NULL AFTER `id`');
        }
    }
}
