<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_127 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $books = db_prefix() . 'wiki_books';
        $articles = db_prefix() . 'wiki_articles';

        if ($CI->db->table_exists($books) && !$CI->db->field_exists('customer_visible', $books)) {
            $CI->db->query("ALTER TABLE `{$books}` ADD `customer_visible` TINYINT(1) NOT NULL DEFAULT 0 AFTER `short_description`");
        }

        if ($CI->db->table_exists($articles)) {
            $CI->db->query("UPDATE `{$articles}` SET `created_by` = `author_id` WHERE `author_id` IS NOT NULL AND (`created_by` IS NULL OR `created_by` = 0)");
        }
    }
}
