<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_123 extends App_module_migration
{
    public function up()
    {
        update_option('scps_version', '1.3.0');
    }

    public function down()
    {
        update_option('scps_version', '1.2.2');
    }
}
