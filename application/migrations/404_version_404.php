<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_404 extends CI_Migration
{
    public function up()
    {
        // UI-only targeted repair. Preserve all production data and settings.
        update_option('smart_choice_crm_build', '4.0.4');
        update_option('smart_choice_crm_current_version', '4.0.4');
    }

    public function down()
    {
        // Production migrations are append-only. No destructive downgrade.
    }
}
