<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_118 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $tbl = db_prefix() . 'wiki_articles';
        if ($CI->db->table_exists($tbl) && !$CI->db->field_exists('content_kind', $tbl)) {
            $CI->db->query("ALTER TABLE `{$tbl}` ADD `content_kind` VARCHAR(30) NOT NULL DEFAULT 'article' AFTER `audience`");
        }
        if ($CI->db->table_exists($tbl)) {
            $CI->db->where('audience', 'customer_portal')->where('content_kind IS NULL', null, false)->update($tbl, ['content_kind' => 'video']);
        }
    }
}
