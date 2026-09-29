<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_127 extends App_module_migration
{
    public function up()
    {
        // Non-destructive customer portal routing upgrade.
        update_option('google_meet_client_route', 'google_meet/meeting_clients/meetings');
        update_option('google_meet_client_portal_enabled', '1');
    }

    public function down()
    {
        // Existing meetings, attendees, settings and credentials are preserved.
    }
}
