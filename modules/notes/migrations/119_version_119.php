<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_119 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('notes', 'install.php');
    }

    public function down()
    {
        // Preserve all existing notes, settings, sources, types, and attachments.
    }
}
