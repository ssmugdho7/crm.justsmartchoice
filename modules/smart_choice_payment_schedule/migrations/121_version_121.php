<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_121 extends App_module_migration
{
    public function up()
    {
        update_option('scps_version', '1.2.1');
    }
    public function down()
    {
        update_option('scps_version', '1.2.0');
    }
}
