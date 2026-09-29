<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_001 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('subcontractors', 'install.php');
    }

    public function down()
    {
        // Upgrade-only migration. Existing subcontractor data is intentionally preserved.
    }
}
