<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Restores the proven 3.7.7 customer-facing invoice, estimate, and proposal
 * presentation while preserving every later database and application repair.
 */
class Migration_Version_380 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_core_upgrade_applied', '380');
        update_option('smart_choice_crm_build', '3.8.0 SC');
    }

    public function down()
    {
        // Upgrade-only migration. Existing CRM data is preserved.
    }
}
