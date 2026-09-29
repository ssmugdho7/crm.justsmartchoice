<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_119 extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();

        if (get_option('google_meet_client_portal_enabled') === false) {
            add_option('google_meet_client_portal_enabled', '1');
        }

        if (get_option('google_meet_client_portal_title') === false) {
            add_option('google_meet_client_portal_title', 'Meetings');
        }
    }

    public function down()
    {
        // Preserve customer portal settings and all meeting data on rollback.
    }
}
