<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_126 extends App_module_migration
{
    public function up()
    {
        update_option('google_meet_client_route', 'google_meet/meeting_clients/meetings');
        update_option('google_meet_client_portal_enabled', '1');
    }

    public function down()
    {
        // Non-destructive upgrade. Existing meetings, attendees, credentials,
        // notifications, settings, and portal data are intentionally preserved.
    }
}
