<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_104 extends App_module_migration
{
    public function up()
    {
        if (get_option('sc_ai_repair_require_approval') === null) {
            add_option('sc_ai_repair_require_approval', '1');
        }
        update_option('sc_ai_repair_last_upgrade', '1.0.4');
    }

    public function down()
    {
        // Upgrade only. Preserve settings, repair history, and backups.
    }
}
