<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_128 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $CI->load->helper('cabinet_maker/cabinet_maker');

        // Page-repair release: only normalize missing legacy schema safely.
        if (function_exists('cabinet_maker_sync_legacy_schema')) {
            cabinet_maker_sync_legacy_schema();
        }

        update_option('cabinet_maker_version', '1.2.8');
        update_option('cabinet_maker_enabled', '1');
    }

    public function down()
    {
        // Non-destructive rollback by design.
    }
}
