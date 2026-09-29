<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_432 extends CI_Migration
{
    public function up()
    {
        update_option('sc_crm_build_version', '4.3.2');
        update_option('smart_choice_crm_build', '4.3.2');
        update_option('smart_choice_crm_current_version', '4.3.2');
    }

    public function down()
    {
        update_option('sc_crm_build_version', '4.3.1');
        update_option('smart_choice_crm_build', '4.3.1');
        update_option('smart_choice_crm_current_version', '4.3.1');
    }
}
