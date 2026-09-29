<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_103 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'upload_video';

        if ($CI->db->table_exists($table)) {
            if ($CI->db->field_exists('project_id', $table) && !$CI->db->field_exists('rel_id', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` CHANGE `project_id` `rel_id` INT(11) NULL DEFAULT NULL;");
            }

            if (!$CI->db->field_exists('rel_id', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `rel_id` INT(11) NULL DEFAULT NULL AFTER `description`;");
            }

            if (!$CI->db->field_exists('rel_type', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `rel_type` VARCHAR(100) NOT NULL DEFAULT 'project' AFTER `rel_id`;");
            }

            if ($CI->db->field_exists('upload_type', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` MODIFY `upload_type` ENUM('file','link','s3') NOT NULL DEFAULT 'file';");
            }
        }

        add_option('is_vl_s3', 'no');
        add_option('vl_s3_bucket', '');
        add_option('vl_s3_region', '');
        add_option('vl_s3_user', '');
        add_option('vl_s3_access_key', '');
        add_option('vl_s3_secret_key', '');
    }

    public function down()
    {
        return true;
    }
}
