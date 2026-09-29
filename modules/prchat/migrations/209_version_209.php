<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_209 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        if ($CI->db->table_exists(db_prefix().'chatgroups')) {
            if (!$CI->db->field_exists('smart_choice_admin_editable', db_prefix().'chatgroups')) {
                $CI->db->query('ALTER TABLE `'.db_prefix().'chatgroups` ADD `smart_choice_admin_editable` TINYINT(1) NOT NULL DEFAULT 1 AFTER `created_by_id`');
            }
            $CI->db->query('UPDATE `'.db_prefix().'chatgroups` SET `smart_choice_admin_editable` = 1');
        }
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
