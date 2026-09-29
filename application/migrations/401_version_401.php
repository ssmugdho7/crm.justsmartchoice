<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_401 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_crm_build', '4.0.1');
        update_option('smart_choice_crm_current_version', '4.0.1');
    }

    public function down()
    {
        // Production migrations are append-only.
    }
}
