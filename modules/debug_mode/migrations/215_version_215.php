<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_215 extends App_module_migration
{
    public function up()
    {
        update_option('debug_mode_version', '2.1.5');
        if (function_exists('debug_mode_smartchoice_add_default_options')) {
            debug_mode_smartchoice_add_default_options();
        }
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
