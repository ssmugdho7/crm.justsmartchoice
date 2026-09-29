<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'sc_ai_repair_runs';

        if ($CI->db->table_exists($table) && !$CI->db->field_exists('validation_report', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `validation_report` LONGTEXT NULL AFTER `summary`");
        }

        if (get_option('sc_ai_repair_enforce_migrations') === null) {
            add_option('sc_ai_repair_enforce_migrations', '1');
        }
    }

    public function down()
    {
        // Upgrade only. Preserve validation reports.
    }
}
