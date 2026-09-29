<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_214 extends App_module_migration
{
    public function up()
    {
        update_option('debug_mode_module_version', '2.1.4');
        update_option('debug_mode_phpmyadmin_url', 'https://s111.bluehost.com:2083/');
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}
