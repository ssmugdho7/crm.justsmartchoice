<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_406 extends CI_Migration
{
    public function up()
    {
        // Targeted UI-only upgrade. Preserve all existing data, options and behavior.
        update_option('smart_choice_crm_build', '4.0.6');
        update_option('smart_choice_crm_current_version', '4.0.6');
    }

    public function down()
    {
        // Upgrade-only migration. No destructive rollback actions.
    }
}
