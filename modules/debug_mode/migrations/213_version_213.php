<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_213 extends App_module_migration
{
    public function up()
    {
        update_option('debug_mode_version', '2.1.3');
        update_option('debug_mode_cache_last_cleared', get_option('debug_mode_cache_last_cleared'));
        return true;
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
