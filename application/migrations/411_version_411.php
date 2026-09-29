<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_411 extends CI_Migration
{
    public function up()
    {
        // Smart Choice CRM 4.1.1 Stripe key integrity / Checkout repair.
        // Code-only release. Existing Stripe options and credentials are preserved.
        update_option('smart_choice_crm_build', '4.1.1');
    }

    public function down()
    {
        // Upgrade-only migration. Intentionally non-destructive.
    }
}
