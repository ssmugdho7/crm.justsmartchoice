<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_105 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'smart_choice_links';

        if ($CI->db->table_exists($table)) {
            $badUrls = [
                'https://crm.justsmartchoice.com/admin/moduless',
                'https://crm.justsmartchoice.com/admin/modeless',
                '/admin/moduless',
                '/admin/modeless',
                'admin/moduless',
                'admin/modeless',
            ];

            $CI->db->where_in('url', $badUrls);
            $CI->db->update($table, [
                'url' => admin_url('modules'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        update_option('smart_choice_links_version', '1.0.5');

        return true;
    }

    public function down()
    {
        return true;
    }
}
