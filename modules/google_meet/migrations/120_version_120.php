<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_120 extends App_module_migration
{
    public function up()
    {
        if (get_option('google_meet_client_portal_enabled') === false) {
            add_option('google_meet_client_portal_enabled', '1');
        }
    }

    public function down()
    {
        // Preserve existing settings and customer meeting data on rollback.
    }
}
