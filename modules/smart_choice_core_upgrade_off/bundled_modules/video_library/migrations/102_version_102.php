<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'upload_video';
        if (!$CI->db->field_exists('upload_video_thumbnail', $table)) {
            $CI->db->query("ALTER TABLE `" . $table . "` ADD `upload_video_thumbnail` text NULL DEFAULT NULL;");
        }
    }
}