<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        require_once(__DIR__ . '/../install.php');
        update_option('smart_network_manager_version', '1.1.0');
        add_option('smart_network_manager_agent_ip_whitelist', '');
        add_option('smart_network_manager_allow_cache_cleanup', '1');
        add_option('smart_network_manager_allow_database_tools', '1');
    }
}
