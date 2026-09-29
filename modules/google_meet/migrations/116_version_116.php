<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_116 extends App_module_migration
{
    public function up()
    {
        // URL/UI repair only. Preserve every meeting, attendee, link, setting,
        // API credential, template, notification record, and customer record.
        update_option('google_meet_version', '1.1.6');
        update_option('google_meet_client_login_required', '1');
        update_option('google_meet_client_portal_enabled', '1');
        update_option('google_meet_client_route', 'google_meet/client');
    }

    public function down()
    {
        // Upgrade-only migration. Never remove production data.
    }
}
