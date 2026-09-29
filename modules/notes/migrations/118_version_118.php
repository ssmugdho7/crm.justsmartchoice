<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_118 extends App_module_migration
{
    public function up()
    {
        // Non-destructive repair release. Existing tblnotes rows remain untouched.
        require module_dir_path('notes', 'install.php');
    }

    public function down()
    {
        // Preserve all existing notes and settings.
    }
}
