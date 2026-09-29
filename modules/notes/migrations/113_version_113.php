<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_113 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('notes', 'install.php');
    }

    public function down()
    {
        // Non-destructive rollback. Existing notes remain intact.
    }
}
