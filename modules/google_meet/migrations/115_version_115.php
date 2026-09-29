<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_115 extends App_module_migration
{
    public function up()
    {
        // Route/controller repair only. Preserve meetings, attendees, links,
        // credentials, notifications, settings, templates, and customer data.
        update_option('google_meet_version', '1.1.5');
        update_option('google_meet_client_login_required', '1');
        update_option('google_meet_client_portal_enabled', '1');
    }

    public function down()
    {
        // Upgrade-only migration. Never remove production data.
    }
}
