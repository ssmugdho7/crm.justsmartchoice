<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_100 extends App_module_migration
{
    public function up()
    {
        require_once APP_MODULES_PATH . 'smart_choice_enterprise_core/libraries/Enterprise_schema.php';
        Enterprise_schema::install();
    }
    public function down()
    {
        // Enterprise data is preserved intentionally.
    }
}
