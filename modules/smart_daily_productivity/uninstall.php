<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

update_option('smart_daily_productivity_enabled', '0');
update_option('smart_daily_productivity_version', '1.0.1');

if ($CI->db->table_exists(db_prefix() . 'smart_daily_productivity_logs')) {
    $CI->db->insert(db_prefix() . 'smart_daily_productivity_logs', [
        'staff_id'   => (int) get_staff_user_id(),
        'action'     => 'module_uninstalled',
        'message'    => 'Module was uninstalled. Data tables were preserved by design.',
        'ip_address' => $CI->input->ip_address(),
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}
