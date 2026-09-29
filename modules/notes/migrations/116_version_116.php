<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_116 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('notes', 'install.php');
    }

    public function down()
    {
        // Preserve all notes, settings, sources, types, and attachments.
    }
}
