<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_434 extends CI_Migration
{
    public function up()
    {
        $CI =& get_instance();
        $table = db_prefix() . 'proposals';
        if ($CI->db->table_exists($table)) {
            if (! $CI->db->field_exists('clientnote', $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD `clientnote` TEXT NULL");
            }
            if (! $CI->db->field_exists('terms', $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD `terms` TEXT NULL");
            }
        }
        update_option('sc_crm_build_version', '4.3.4');
        update_option('smart_choice_crm_build', '4.3.4');
        update_option('smart_choice_crm_current_version', '4.3.4');
    }

    public function down() {}
}
