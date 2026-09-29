<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_114 extends App_module_migration
{
    public function up()
    {
        if (get_option('smart_choice_links_default_open_behavior') === false) {
            add_option('smart_choice_links_default_open_behavior', '_self');
        }

        // New Smart Choice standard: CRM/admin links open in the same tab unless a link is explicitly changed later.
        $CI = &get_instance();
        if ($CI->db->table_exists(db_prefix() . 'smart_choice_links')) {
            $CI->db->group_start();
            $CI->db->like('url', '/admin/', 'both');
            $CI->db->or_like('url', 'admin/', 'after');
            $CI->db->group_end();
            $CI->db->update(db_prefix() . 'smart_choice_links', ['target' => '_self', 'rel' => 'noopener noreferrer']);
        }
    }

    public function down()
    {
        // Non-destructive rollback. The option can remain safely.
    }
}
