<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_118 extends App_module_migration
{
    public function up()
    {
        // Upgrade-only repair. No meetings, attendees, links, settings,
        // credentials, templates, notifications, or customer records are removed.
        update_option('google_meet_version', '1.1.8');
        update_option('google_meet_client_login_required', '1');
        update_option('google_meet_client_portal_enabled', '1');
        update_option('google_meet_client_route', 'google_meet/client');
    }

    public function down()
    {
        // Upgrade only. Never remove or roll back production data.
    }
}
