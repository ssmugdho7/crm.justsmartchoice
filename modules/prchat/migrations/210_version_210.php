<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_210 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        if ($CI->db->table_exists(db_prefix() . 'chatgroups') && !$CI->db->field_exists('group_image', db_prefix() . 'chatgroups')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . "chatgroups` ADD `group_image` VARCHAR(255) NULL DEFAULT NULL AFTER `group_name`");
        }
        update_option('prchat_version', '2.1.0');
    }

    public function down()
    {
        // Preserve production data and group photos.
    }
}
