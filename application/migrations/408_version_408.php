<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_408 extends CI_Migration
{
    public function up()
    {
        // Code-only repair: sales link editor inputs are AJAX-only and must not be posted as invoice/estimate/proposal columns.
        update_option('smart_choice_crm_build', '4.0.8');
        update_option('smart_choice_crm_current_version', '4.0.8');
    }

    public function down()
    {
        // Upgrade-only migration. No schema or user data is removed.
    }
}
