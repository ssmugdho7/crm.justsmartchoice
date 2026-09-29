<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_117 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $tbl = db_prefix() . 'wiki_articles';
        if ($CI->db->table_exists($tbl) && !$CI->db->field_exists('audience', $tbl)) {
            $CI->db->query("ALTER TABLE `{$tbl}` ADD `audience` VARCHAR(30) NOT NULL DEFAULT 'internal' AFTER `style_preset`");
        }
    }
}
