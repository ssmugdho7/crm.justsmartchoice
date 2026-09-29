<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_327 extends App_module_migration
{
    public function up()
    {
        // Routing-only release. No schema changes are required.
        // Existing subcontractors, contracts, templates, signatures, initials,
        // uploads, settings, and public tokens remain untouched.
    }

    public function down()
    {
        // Non-destructive migration.
    }
}
