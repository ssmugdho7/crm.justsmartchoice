<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_216 extends App_module_migration
{
    public function up()
    {
        update_option('purchasing_hub_version', '2.1.9');
        update_option('purchasing_hub_use_crm_items', '1');
    }

    public function down()
    {
        // Safe no-op rollback. Do not remove purchasing data.
    }
}
