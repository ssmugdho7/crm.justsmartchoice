<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Native_settings_integration extends App_module_migration
{
    public function up()
    {
        add_option('debug_mode_enabled', '0');
        add_option('debug_mode_client_visible', '0');
        add_option('debug_mode_log_level', 'basic');
        add_option('debug_mode_allowed_roles', '');
        add_option('debug_mode_allowed_staff', '');
    }
}
