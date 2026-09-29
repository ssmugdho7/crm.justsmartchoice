<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_127 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $previousDebug = $CI->db->db_debug;
        $CI->db->db_debug = false;

        try {
            $CI->load->helper('cabinet_maker/cabinet_maker');
            cabinet_maker_run_schema();
            update_option('cabinet_maker_database_status', 'ready');
        } catch (Throwable $e) {
            // Keep the module active and expose the issue through Health Check.
            update_option('cabinet_maker_database_status', 'repair_required');
            log_message('error', 'Cabinet Maker migration 127: ' . $e->getMessage());
        }

        $CI->db->db_debug = $previousDebug;
        update_option('cabinet_maker_version', '1.2.7');
        update_option('cabinet_maker_migration_checkpoint', '127');
        update_option('cabinet_maker_activation_status', 'active');
    }

    public function down()
    {
        // Intentionally non-destructive. Existing cabinet data is preserved.
    }
}
