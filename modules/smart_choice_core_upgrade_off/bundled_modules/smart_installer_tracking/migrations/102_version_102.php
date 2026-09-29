<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        update_option('smart_installer_tracking_version', '1.0.2');
        update_option('smart_installer_tracking_appointly_sync', '1');
        update_option('smart_installer_tracking_client_portal', '1');
    }
}
