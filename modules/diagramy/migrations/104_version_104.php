<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_104 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        if ($CI->db->table_exists(db_prefix().'diagramy')) {
            if ($CI->db->field_exists('diagramy_content', db_prefix().'diagramy')) {
                $CI->db->query('ALTER TABLE `'.db_prefix().'diagramy` MODIFY `diagramy_content` LONGTEXT NULL');
            }
            if (!$CI->db->field_exists('diagramy_xml', db_prefix().'diagramy')) {
                $CI->db->query('ALTER TABLE `'.db_prefix().'diagramy` ADD `diagramy_xml` LONGTEXT NULL AFTER `diagramy_content`');
            } else {
                $CI->db->query('ALTER TABLE `'.db_prefix().'diagramy` MODIFY `diagramy_xml` LONGTEXT NULL');
            }
        }
    }
}
