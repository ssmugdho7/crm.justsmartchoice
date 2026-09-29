<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_124 extends App_module_migration
{
    public function up()
    {
        // Customer portal routing now follows Perfex native HMVC/Appointly pattern.
        // No destructive schema change is required; existing meetings and assignments remain intact.
        if (get_option('google_meet_client_portal_enabled') === '') {
            add_option('google_meet_client_portal_enabled', '1');
        }

        update_option('google_meet_client_route', 'google_meet/meeting_clients/meetings');
    }

    public function down()
    {
        // Upgrade-only migration. Existing customer portal setting is intentionally preserved.
    }
}
