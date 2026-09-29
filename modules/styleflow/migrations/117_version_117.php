<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * StyleFlow 1.1.7 settings/rendering repair.
 *
 * Adds only optional employee-photo configuration fields. Existing templates,
 * sales documents, settings and active template selections are preserved.
 */
class Migration_Version_117 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $CI->load->helper('styleflow/styleflow');
        styleflow_install_or_repair_schema();

        // Give the bundled Signature template its intended creator-photo
        // behavior only when it still has the newly-added default value.
        $table = styleflow_templates_table();
        if ($CI->db->table_exists($table)) {
            $CI->db->where('slug', 'sc_signature');
            $CI->db->where('staff_photo_mode', 'none');
            $CI->db->update($table, [
                'staff_photo_mode' => 'creator',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        update_option('styleflow_version', '1.1.7');
    }

    public function down()
    {
        // Upgrade only. Do not remove columns or template settings.
    }
}
