<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_409 extends CI_Migration
{
    public function up()
    {
        // Smart Choice CRM 4.0.9 targeted sales pricing rollback.
        // Code-only release: no schema or data changes required.
    }

    public function down()
    {
        // Upgrade-only migration. Intentionally non-destructive.
    }
}
