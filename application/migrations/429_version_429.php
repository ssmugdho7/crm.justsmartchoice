<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_429 extends CI_Migration
{
    public function up()
    {
        // Non-destructive marker for the targeted Media Manager and Call Center upgrade repair.
        update_option('sc_crm_build_version', '4.2.9');
        update_option('smart_choice_crm_build', '4.2.9');
        update_option('smart_choice_crm_current_version', '4.2.9');
    }

    public function down()
    {
        update_option('sc_crm_build_version', '4.2.8');
        update_option('smart_choice_crm_build', '4.2.8');
        update_option('smart_choice_crm_current_version', '4.2.8');
    }
}
