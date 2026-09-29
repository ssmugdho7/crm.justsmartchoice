<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_426 extends CI_Migration
{
    public function up()
    {
        update_option('sc_crm_build_version', '4.2.6');
        update_option('smart_choice_crm_build', '4.2.6');
        update_option('smart_choice_crm_current_version', '4.2.6');
    }

    public function down()
    {
        update_option('sc_crm_build_version', '4.2.5');
        update_option('smart_choice_crm_build', '4.2.5');
        update_option('smart_choice_crm_current_version', '4.2.5');
    }
}
