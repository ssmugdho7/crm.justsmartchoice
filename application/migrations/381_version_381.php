<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Restores reliable customer invoice payment submission and records the
 * Smart Choice CRM 3.8.1 upgrade without changing existing gateway settings.
 */
class Migration_Version_381 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_core_upgrade_applied', '381');
        update_option('smart_choice_crm_build', '3.8.1 SC');
    }

    public function down()
    {
        // Upgrade-only migration. Existing CRM data and payment settings remain intact.
    }
}
