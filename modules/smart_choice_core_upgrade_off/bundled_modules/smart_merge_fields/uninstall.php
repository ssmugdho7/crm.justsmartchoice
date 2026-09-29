<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Non destructive uninstall. Data is preserved for audit and reinstall safety.
if ($CI->db->table_exists(db_prefix() . 'smart_merge_settings')) {
    $CI->db->where('setting_key', 'module_uninstalled_at')->delete(db_prefix() . 'smart_merge_settings');
    $CI->db->insert(db_prefix() . 'smart_merge_settings', [
        'setting_key' => 'module_uninstalled_at',
        'setting_value' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}
