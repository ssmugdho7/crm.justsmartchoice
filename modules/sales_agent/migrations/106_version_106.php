<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_106 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        if ($CI->db->table_exists(db_prefix() . 'sa_commissions')) {
            foreach (['invoice_id','agent_id','staff_id','status'] as $field) {
                // Indexes are created in install.php. No foreign keys are used by design to avoid MySQL errno 150 on mixed/legacy databases.
            }
        }
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
