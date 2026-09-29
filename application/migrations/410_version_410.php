<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_410 extends CI_Migration
{
    public function up()
    {
        // Smart Choice CRM 4.1.0 targeted Stripe checkout and invoice status repair.
        // Code-only release: no schema or data changes required.
    }

    public function down()
    {
        // Upgrade-only migration. Intentionally non-destructive.
    }
}
