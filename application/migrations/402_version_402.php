<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_402 extends CI_Migration
{
    public function up()
    {
        if (get_option('sc_menu_icon_color') === '') {
            add_option('sc_menu_icon_color', '#169179');
        }
        update_option('smart_choice_crm_build', '4.0.2');
        update_option('smart_choice_crm_current_version', '4.0.2');
    }

    public function down()
    {
        // Production migrations are append-only. No destructive downgrade.
    }
}
