<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_220 extends App_module_migration
{
    public function up()
    {
        update_option('purchasing_hub_version', '2.2.0');
        update_option('purchasing_hub_css_scope_repaired', '1');
    }

    public function down()
    {
        // Safe no-op rollback. No purchasing data is removed.
    }
}
