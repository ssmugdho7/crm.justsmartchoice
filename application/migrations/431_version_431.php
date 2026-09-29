<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_431 extends CI_Migration
{
    public function up()
    {
        update_option('sc_crm_build_version', '4.3.1');
        update_option('smart_choice_crm_build', '4.3.1');
        update_option('smart_choice_crm_current_version', '4.3.1');
    }

    public function down()
    {
        update_option('sc_crm_build_version', '4.3.0');
        update_option('smart_choice_crm_build', '4.3.0');
        update_option('smart_choice_crm_current_version', '4.3.0');
    }
}
