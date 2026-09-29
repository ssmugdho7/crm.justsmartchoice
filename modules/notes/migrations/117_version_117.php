<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_117 extends App_module_migration
{
    public function up()
    {
        // Repair/complete the non-destructive Notes schema and settings tables.
        require module_dir_path('notes', 'install.php');
    }

    public function down()
    {
        // Preserve all notes, sources, types, attachments, and settings.
    }
}
