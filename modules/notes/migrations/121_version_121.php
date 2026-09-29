<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_121 extends App_module_migration
{
    public function up()
    {
        // Layout-only upgrade. Preserve all existing notes, settings, sources,
        // types, attachments, relations, and module configuration.
        require module_dir_path('notes', 'install.php');
    }

    public function down()
    {
        // No destructive rollback actions.
    }
}
