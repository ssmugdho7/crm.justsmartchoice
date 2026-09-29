<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_177 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        if (function_exists('add_option')) {
            add_option('smart_choice_recruitment_upgrade_177_repair', '1');
        }

        $table = db_prefix() . 'rec_job_position';
        if ($CI->db->table_exists($table)) {
            $fields = $CI->db->list_fields($table);
            $exists = $CI->db->where('position_name', 'Smart Choice Construction Project Manager')->get($table)->row();
            if (!$exists) {
                $insert = ['position_name' => 'Smart Choice Construction Project Manager'];
                $description = 'Sample Smart Choice role for construction project management, customer communication, field coordination, CRM documentation, and jobsite standards.';
                if (in_array('position_description', $fields, true)) { $insert['position_description'] = $description; }
                if (in_array('description', $fields, true)) { $insert['description'] = $description; }
                $insert = array_intersect_key($insert, array_flip($fields));
                if (!empty($insert)) { $CI->db->insert($table, $insert); }
            }
        }
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}
