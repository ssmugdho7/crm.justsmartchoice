<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_428 extends CI_Migration
{
    public function up()
    {
        // Non-destructive release marker. Existing 4.2.7 schema/data are preserved.
        update_option('sc_crm_build_version', '4.2.8');
        update_option('smart_choice_crm_build', '4.2.8');
        update_option('smart_choice_crm_current_version', '4.2.8');

        // Persist long client sessions as a configurable Smart Choice option without altering customer records.
        if (!option_exists('sc_client_session_lifetime')) {
            add_option('sc_client_session_lifetime', 2592000); // 30 days
        }
    }

    public function down()
    {
        // Append-only / non-destructive downgrade marker only.
        update_option('sc_crm_build_version', '4.2.7');
        update_option('smart_choice_crm_build', '4.2.7');
        update_option('smart_choice_crm_current_version', '4.2.7');
    }
}
