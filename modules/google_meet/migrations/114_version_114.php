<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_114 extends App_module_migration
{
    public function up()
    {
        // Preserve every meeting, attendee, notification, credential, and existing setting.
        update_option('google_meet_version', '1.1.4');
        update_option('google_meet_client_portal_title', 'Meetings');
        add_option('google_meet_client_portal_subtitle', 'Google Meet');
        add_option('google_meet_client_login_required', '1');
    }

    public function down()
    {
        // Upgrade-only migration. Production customer and meeting data must never be removed.
    }
}
