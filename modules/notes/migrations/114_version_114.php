<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_114 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        require_once module_dir_path('notes', 'install.php');

        // Leave blank titles blank; the notes UI derives a readable display
        // title from the note content without inventing a Legacy Note label.
    }

    public function down()
    {
        // Non-destructive by design.
    }
}
