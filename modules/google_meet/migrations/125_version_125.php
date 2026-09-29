<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_125 extends App_module_migration
{
    public function up()
    {
        update_option('google_meet_client_route', 'google_meet/meeting_clients/meetings');
        update_option('google_meet_client_portal_enabled', '1');
    }

    public function down()
    {
        // Upgrade-only migration. Existing settings and meeting data are preserved.
    }
}
