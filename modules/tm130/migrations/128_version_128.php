<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_128 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $articles = db_prefix() . 'wiki_articles';
        if ($CI->db->table_exists($articles)) {
            $CI->db->query("UPDATE `{$articles}` SET `created_by` = `author_id` WHERE `author_id` IS NOT NULL AND (`created_by` IS NULL OR `created_by` = 0)");
        }
    }
}
