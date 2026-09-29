<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_core_upgrade_model extends App_Model
{
    public function version_info()
    {
        $current = get_option('smart_choice_enterprise_current_version') ?: '3.1.2';
        $latest  = get_option('smart_choice_enterprise_latest_version') ?: $current;
        $status  = version_compare($latest, $current, '>') ? 'update_available' : 'current';
        update_option('smart_choice_enterprise_update_status', $status);

        return [
            'current' => $current,
            'latest' => $latest,
            'status' => $status,
            'channel' => get_option('smart_choice_enterprise_update_channel') ?: 'stable',
            'last_check' => get_option('smart_choice_enterprise_update_last_check'),
            'windows_timezone' => get_option('smart_choice_windows_timezone_label') ?: 'Eastern Time (US & Canada)',
            'php_timezone' => get_option('default_timezone') ?: 'America/New_York',
        ];
    }

    public function embedded_modules()
    {
        $modules = [
            'accounting' => 'Accounting Hub',
            'purchasing_hub' => 'Purchasing Hub',
            'sales_center' => 'Sales Hub',
            'prchat' => 'Smart Choice Office Stream',
            'training_manual' => 'Training Manual',
            'custom_pdf' => 'Custom PDF',
            'google_meet' => 'Google Meet',
            'favorite_links' => 'Favorite Links',
            'video_library' => 'Video Library',
            'smart_choice_field_connector' => 'Smart Choice Field Connector',
            'si_todo' => 'Smart Choice To Do',
        ];
        $rows = [];
        foreach ($modules as $folder => $name) {
            $rows[] = [
                'folder' => $folder,
                'name' => $name,
                'installed' => is_dir(FCPATH . 'modules/' . $folder),
                'main_file' => file_exists(FCPATH . 'modules/' . $folder . '/' . $folder . '.php'),
                'settings' => $this->module_has_settings($folder),
            ];
        }
        return $rows;
    }

    public function module_has_settings($folder)
    {
        $paths = [
            FCPATH . 'modules/' . $folder . '/views/settings.php',
            FCPATH . 'modules/' . $folder . '/views/admin/settings.php',
            FCPATH . 'modules/' . $folder . '/settings.php',
        ];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return true;
            }
        }
        return false;
    }

    public function reports()
    {
        if (!$this->db->table_exists(db_prefix() . 'smart_choice_reports')) {
            return [];
        }
        return $this->db->order_by('report_group', 'ASC')->order_by('report_name', 'ASC')->get(db_prefix() . 'smart_choice_reports')->result();
    }

    public function health_report()
    {
        $checks = [];
        $tables = [
            db_prefix() . 'options',
            db_prefix() . 'migrations',
            db_prefix() . 'taxes',
            db_prefix() . 'smart_choice_core_logs',
            db_prefix() . 'smart_choice_upgrade_history',
            db_prefix() . 'smart_choice_reports',
        ];
        foreach ($tables as $table) {
            $checks[] = [
                'name' => 'Database table: ' . $table,
                'status' => $this->db->table_exists($table) ? 'ok' : 'missing',
                'message' => $this->db->table_exists($table) ? 'Table exists.' : 'Table is missing. Run module activation or upgrade database.',
            ];
        }

        $options = [
            'default_timezone' => 'America/New_York',
            'dateformat' => 'm/d/Y',
            'time_format' => '12',
            'active_language' => 'english',
            'smart_choice_enterprise_current_version' => '3.1.2',
        ];
        foreach ($options as $option => $expected) {
            $actual = get_option($option);
            $checks[] = [
                'name' => 'Option: ' . $option,
                'status' => ((string)$actual === (string)$expected) ? 'ok' : 'warning',
                'message' => 'Current: ' . (string)$actual . ' | Expected: ' . (string)$expected,
            ];
        }

        foreach ($this->embedded_modules() as $module) {
            $checks[] = [
                'name' => 'Embedded module: ' . $module['name'],
                'status' => $module['installed'] ? 'ok' : 'missing',
                'message' => $module['installed'] ? 'Folder exists in /modules.' : 'Folder missing. Re-upload this package or copy bundled module manually.',
            ];
        }

        return $checks;
    }

    public function apply_core_defaults()
    {
        update_option('default_timezone', 'America/New_York');
        update_option('dateformat', 'm/d/Y');
        update_option('time_format', '12');
        update_option('active_language', 'english');
        update_option('smart_choice_windows_timezone_label', 'Eastern Time (US & Canada)');
        update_option('smart_choice_php_timezone', 'America/New_York');
        update_option('smart_choice_enterprise_current_version', '3.1.2');
        update_option('smart_choice_enterprise_latest_version', '3.1.2');
        update_option('smart_choice_core_theme_name', 'Smart Choice');
        update_option('smart_choice_core_theme_enabled', '1');
        update_option('smart_choice_core_table_tools', '1');
        update_option('smart_choice_quickbooks_desktop_enabled', '1');
        update_option('smart_choice_reports_enabled', '1');
        update_option('company_vat', get_option('company_vat') ?: 'EIN');
        return true;
    }

    public function log($level, $eventType, $message, $data = null)
    {
        if (!$this->db->table_exists(db_prefix() . 'smart_choice_core_logs')) {
            return false;
        }
        return $this->db->insert(db_prefix() . 'smart_choice_core_logs', [
            'level' => $level,
            'event_type' => $eventType,
            'message' => $message,
            'data' => $data === null ? null : json_encode($data),
            'staff_id' => function_exists('get_staff_user_id') ? get_staff_user_id() : null,
            'datecreated' => date('Y-m-d H:i:s'),
        ]);
    }
}
