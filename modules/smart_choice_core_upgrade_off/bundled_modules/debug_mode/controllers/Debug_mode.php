<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Debug_mode extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin() && !(function_exists('has_permission') && has_permission('debug_mode', '', 'view'))) {
            access_denied('CRM Utilities');
        }
    }

    public function index()
    {
        $data['title'] = 'CRM Utilities & Debug Tools';
        $data['phpmyadmin_url'] = get_option('debug_mode_phpmyadmin_url');
        $data['error_report'] = $this->build_error_report();
        $data['database_report'] = $this->build_database_report(false);
        $this->load->view('utilities', $data);
    }

    public function settings()
    {
        redirect(admin_url('settings?group=debug_mode'));
    }

    public function health()
    {
        $data['title'] = 'CRM Utilities Health Check';
        $data['error_report'] = $this->build_error_report();
        $data['database_report'] = $this->build_database_report(false);
        $this->load->view('health', $data);
    }

    public function clear_cache()
    {
        if (!is_admin()) {
            access_denied('CRM Utilities');
        }

        $deleted = $this->clear_cache_files();
        update_option('debug_mode_cache_last_cleared', date('Y-m-d H:i:s'));
        set_alert('success', 'Cache cleanup completed. Files removed: ' . $deleted . '. index.html files were preserved.');
        redirect(admin_url('debug_mode'));
    }

    public function toggle_debug($mode = '')
    {
        if (!is_admin()) {
            access_denied('CRM Utilities');
        }

        $enable = $mode === 'on';
        update_option('debug_mode_enabled', $enable ? '1' : '0');
        debug_mode_enable_environment($enable ? 'development' : 'production');
        set_alert('success', $enable ? 'Debug Mode activated.' : 'Debug Mode deactivated.');
        redirect(admin_url('debug_mode'));
    }

    public function toggle_client_portal($mode = '')
    {
        if (!is_admin()) {
            access_denied('CRM Utilities');
        }

        $enable = $mode === 'on';
        update_option('debug_mode_client_portal_enabled', $enable ? '1' : '0');

        // Perfex installations commonly use disable_client_login. Add/update it safely.
        if (function_exists('get_option') && get_option('disable_client_login') !== null) {
            update_option('disable_client_login', $enable ? '0' : '1');
        } else {
            add_option('disable_client_login', $enable ? '0' : '1');
        }

        set_alert('success', $enable ? 'Client portal enabled.' : 'Client portal disabled.');
        redirect(admin_url('debug_mode'));
    }

    public function database_action($action = 'check')
    {
        if (!is_admin()) {
            access_denied('CRM Utilities');
        }

        $allowed = ['check', 'analyze', 'optimize', 'repair'];
        if (!in_array($action, $allowed, true)) {
            set_alert('danger', 'Invalid database action.');
            redirect(admin_url('debug_mode'));
        }

        $report = $this->build_database_report($action);
        $data['title'] = 'Database ' . ucfirst($action) . ' Report';
        $data['database_report'] = $report;
        $data['error_report'] = $this->build_error_report();
        $data['phpmyadmin_url'] = get_option('debug_mode_phpmyadmin_url');
        $this->load->view('utilities', $data);
    }

    private function clear_cache_files()
    {
        $paths = [
            APPPATH . 'cache',
            FCPATH . 'application/cache',
        ];

        $deleted = 0;
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                $basename = $file->getBasename();
                if ($basename === 'index.html' || $basename === '.htaccess') {
                    continue;
                }

                if ($file->isFile()) {
                    if (@unlink($file->getPathname())) {
                        $deleted++;
                    }
                } elseif ($file->isDir()) {
                    @rmdir($file->getPathname());
                }
            }
        }

        return $deleted;
    }

    private function build_error_report()
    {
        $files = [
            APPPATH . 'logs/log-' . date('Y-m-d') . '.php',
            APPPATH . 'logs/log-' . date('Y-m-d', strtotime('-1 day')) . '.php',
        ];

        $rows = [];
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!$lines) {
                continue;
            }

            $lines = array_slice($lines, -80);
            foreach ($lines as $line) {
                if (stripos($line, 'ERROR') !== false || stripos($line, 'Severity:') !== false || stripos($line, 'Exception') !== false) {
                    $rows[] = [
                        'file' => basename($file),
                        'line' => $line,
                    ];
                }
            }
        }

        return array_slice(array_reverse($rows), 0, 50);
    }

    private function build_database_report($action = false)
    {
        $tables = $this->db->list_tables();
        $rows = [];

        foreach ($tables as $table) {
            $safeTable = str_replace('`', '', $table);

            if ($action === false) {
                $rows[] = [
                    'table' => $safeTable,
                    'operation' => 'Inventory',
                    'message' => 'Table found',
                ];
                continue;
            }

            $sqlAction = strtoupper($action);
            $query = $this->db->query($sqlAction . ' TABLE `' . $safeTable . '`');
            if ($query && method_exists($query, 'result_array')) {
                foreach ($query->result_array() as $result) {
                    $rows[] = [
                        'table' => $safeTable,
                        'operation' => $sqlAction,
                        'message' => json_encode($result),
                    ];
                }
            }
        }

        return array_slice($rows, 0, 300);
    }
}
