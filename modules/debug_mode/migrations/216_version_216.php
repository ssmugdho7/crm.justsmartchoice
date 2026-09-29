<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_216 extends App_module_migration
{
    public function up()
    {
        require module_dir_path('debug_mode', 'install.php');
        update_option('debug_mode_version', '2.1.6');
    }

    public function down()
    {
        // Preserve diagnostics and monitoring data.
    }
}
