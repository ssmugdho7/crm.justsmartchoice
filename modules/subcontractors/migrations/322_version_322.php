<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_322 extends App_module_migration
{
    public function up()
    {
        // Non-destructive repair: preserves all records, settings, uploads, tokens, and signatures.
        require module_dir_path('subcontractors', 'install.php');
        log_activity('Smartsource Subcontractors Module Upgraded to 3.2.2');
    }

    public function down()
    {
        // Upgrade-only migration. No data or schema is removed.
    }
}
