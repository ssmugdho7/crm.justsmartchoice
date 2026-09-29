<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_104 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'upload_video';

        if ($CI->db->table_exists($table)) {
            if (!$CI->db->field_exists('rel_id', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `rel_id` INT(11) NULL DEFAULT NULL AFTER `description`;");
            }
            if (!$CI->db->field_exists('rel_type', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `rel_type` VARCHAR(100) NOT NULL DEFAULT 'project' AFTER `rel_id`;");
            }
            if (!$CI->db->field_exists('created_by', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `created_by` INT(11) NULL DEFAULT NULL AFTER `rel_type`;");
            }
            if (!$CI->db->field_exists('uploaded_by', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `uploaded_by` INT(11) NULL DEFAULT NULL AFTER `created_by`;");
            }
            if ($CI->db->field_exists('upload_type', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` MODIFY `upload_type` ENUM('file','link','s3') NOT NULL DEFAULT 'file';");
            }
        }

        add_option('vl_show_uploader_card', 'yes');
        add_option('vl_default_rel_type', 'project');
        add_option('vl_default_upload_owner', '0');
        add_option('vl_storage_mode', 'local');
        update_option('is_vl_s3', 'no');
        update_option('is_vl_google_drive', 'no');
    }

    public function down()
    {
        return true;
    }
}
