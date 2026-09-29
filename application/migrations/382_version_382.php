<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Smart Choice CRM 3.8.2
 * Repairs contract attachment uploads and records the upgrade without
 * modifying existing CRM data, files, gateway settings, or API credentials.
 */
class Migration_Version_382 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_core_upgrade_applied', '382');
        update_option('smart_choice_crm_build', '3.8.2 SC');
    }

    public function down()
    {
        // Upgrade-only migration. No existing data is removed.
    }
}
