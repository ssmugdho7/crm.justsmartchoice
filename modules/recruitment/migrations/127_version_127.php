<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_127 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'rec_applied_jobs';
        if ($CI->db->table_exists($table)) {
            if (!$CI->db->field_exists('activate', $table)) {
                $CI->db->query("ALTER TABLE `".$table."` ADD `activate` VARCHAR(10) NULL DEFAULT '1'");
            } else {
                $CI->db->query("ALTER TABLE `".$table."` MODIFY `activate` VARCHAR(10) NULL DEFAULT '1'");
            }
            $indexes = $CI->db->query("SHOW INDEX FROM `".$table."`")->result_array();
            $indexNames = array_column($indexes, 'Key_name');
            if (!in_array('idx_rec_applied_candidate_campaign', $indexNames, true)) {
                $CI->db->query("ALTER TABLE `".$table."` ADD INDEX `idx_rec_applied_candidate_campaign` (`candidate_id`,`campaign_id`)");
            }
            $CI->db->query("UPDATE `".$table."` SET `activate`='1' WHERE `activate` IS NULL OR `activate`='' ");
        }
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}
