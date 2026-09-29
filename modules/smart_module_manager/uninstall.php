<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

delete_option('smart_module_manager_enabled');
delete_option('smart_module_manager_allow_unload');
delete_option('smart_module_manager_backup_before_unload');
delete_option('smart_module_manager_allow_database_cleanup');
delete_option('smart_module_manager_export_path');

// Logs are preserved by default for audit history.
