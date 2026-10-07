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
        $data['broken_links'] = [];
        $data['orphan_files'] = [];
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
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $deleted = $this->clear_cache_files();
        update_option('debug_mode_cache_last_cleared', date('Y-m-d H:i:s'));
        set_alert('success', 'Cache cleanup completed. Files removed: ' . $deleted . '. index.html files were preserved.');
        redirect(admin_url('debug_mode'));
    }

    public function toggle_debug($mode = '')
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        if (!in_array($mode, ['on', 'off'], true)) {
            set_alert('danger', 'Invalid Debug Mode action.');
            redirect(admin_url('debug_mode'));
            return;
        }
        $enable = $mode === 'on';
        if (!debug_mode_enable_environment($enable ? 'development' : 'production')) {
            set_alert('danger', 'Debug Mode was not changed. Check index.php permissions and the environment declaration.');
            redirect(admin_url('debug_mode'));
            return;
        }
        update_option('debug_mode_enabled', $enable ? '1' : '0');
        set_alert('success', $enable ? 'Debug Mode activated.' : 'Debug Mode deactivated.');
        redirect(admin_url('debug_mode'));
    }

    public function toggle_client_portal($mode = '')
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $enable = $mode === 'on';
        update_option('debug_mode_client_portal_enabled', $enable ? '1' : '0');
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
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $allowed = ['check', 'analyze', 'optimize', 'repair'];
        if (!in_array($action, $allowed, true)) {
            set_alert('danger', 'Invalid database action.');
            redirect(admin_url('debug_mode'));
        }
        $data['title'] = 'Database ' . ucfirst($action) . ' Report';
        $data['database_report'] = $this->build_database_report($action);
        $data['error_report'] = $this->build_error_report();
        $data['phpmyadmin_url'] = get_option('debug_mode_phpmyadmin_url');
        $data['broken_links'] = [];
        $data['orphan_files'] = [];
        $this->load->view('utilities', $data);
    }

    public function database_backup()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $file = $this->create_database_backup_file();
        if ($file && is_file($file)) {
            set_alert('success', 'Database backup created: ' . basename($file));
        } else {
            set_alert('danger', 'Database backup could not be created. Check folder permissions.');
        }
        redirect(admin_url('debug_mode'));
    }

    public function export_staff()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $fields = $this->db->list_fields(db_prefix() . 'staff');
        $rows = $this->db->get(db_prefix() . 'staff')->result_array();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="staff_export_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $fields);
        foreach ($rows as $row) {
            $line = [];
            foreach ($fields as $field) { $line[] = isset($row[$field]) ? $row[$field] : ''; }
            fputcsv($out, $line);
        }
        fclose($out);
        exit;
    }

    public function staff_import_help()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $data['title'] = 'Staff Import / Export';
        $this->load->view('staff_import_help', $data);
    }

    public function broken_links()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $data['title'] = 'Broken Links Report';
        $data['phpmyadmin_url'] = get_option('debug_mode_phpmyadmin_url');
        $data['error_report'] = $this->build_error_report();
        $data['database_report'] = $this->build_database_report(false);
        $data['broken_links'] = $this->scan_broken_links();
        $data['orphan_files'] = [];
        $this->load->view('utilities', $data);
    }

    public function orphan_files()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $data['title'] = 'Orphan Files Report';
        $data['phpmyadmin_url'] = get_option('debug_mode_phpmyadmin_url');
        $data['error_report'] = $this->build_error_report();
        $data['database_report'] = $this->build_database_report(false);
        $data['broken_links'] = [];
        $data['orphan_files'] = $this->scan_orphan_files();
        $this->load->view('utilities', $data);
    }

    public function crm_errors()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $data['title'] = 'CRM Error Report';
        $data['error_report'] = $this->build_error_report(500);
        $this->load->view('crm_errors', $data);
    }

    public function export_errors()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="crm_errors_' . date('Ymd_His') . '.txt"');
        echo $this->build_error_report_text(500);
        exit;
    }

    public function crm_errors_json()
    {
        if (!is_admin()) { ajax_access_denied(); }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'errors' => $this->build_error_report(500), 'text' => $this->build_error_report_text(500)]);
        exit;
    }

    public function file_structure()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $data['title'] = 'CRM Folder Structure';
        $data['tree'] = $this->build_folder_tree(FCPATH, 0, 6);
        $data['base_path'] = FCPATH;
        $this->load->view('file_structure', $data);
    }

    public function export_structure_html()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $tree = $this->build_folder_tree(FCPATH, 0, 7);
        $html = '<!doctype html><html><head><meta charset="utf-8"><title>CRM Folder Structure</title><style>body{font-family:Arial,sans-serif;background:#f5f7f8;color:#263238;padding:25px}h1{color:#169179}li{margin:5px 0}.folder{background:#fff;border-left:4px solid #f47c20;border-radius:8px;padding:6px 10px;display:inline-block}</style></head><body><h1>CRM Folder Structure</h1><p>Generated ' . date('Y-m-d H:i:s') . '</p>' . $this->tree_to_html($tree) . '</body></html>';
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="crm_folder_structure_' . date('Ymd_His') . '.html"');
        echo $html;
        exit;
    }


    public function network_tools()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $data['title'] = 'Network Tools';
        $data['server_info'] = $this->build_network_info();
        $this->load->view('network_tools', $data);
    }

    public function internet_check_json()
    {
        if (!is_admin()) { ajax_access_denied(); }
        $start = microtime(true);
        $status = false;
        $message = 'Internet check failed.';
        $targets = ['https://www.google.com/generate_204', 'https://www.cloudflare.com/cdn-cgi/trace'];
        foreach ($targets as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_NOBODY, false);
            @curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (PHP_VERSION_ID < 80500) { @curl_close($ch); }
            if ($code >= 200 && $code < 400) {
                $status = true;
                $message = 'Internet is reachable from the CRM server.';
                break;
            }
        }
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'online' => $status,
            'message' => $message,
            'latency_ms' => round((microtime(true) - $start) * 1000),
            'checked_at' => date('Y-m-d H:i:s'),
        ]);
        exit;
    }

    public function speed_payload()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $size = 1024 * 512; // 512 KB, safe for shared hosting
        header('Content-Type: application/octet-stream');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        echo str_repeat('S', $size);
        exit;
    }

    public function auto_repair()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $report = [];
        $dirs = [
            FCPATH . 'uploads/debug_mode_backups/',
            APPPATH . 'cache/',
        ];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
            $report[] = (is_dir($dir) ? 'OK: ' : 'FAILED: ') . str_replace(FCPATH, '', $dir);
            $index = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . 'index.html';
            if (!is_file($index)) { @file_put_contents($index, '<html><body></body></html>'); }
        }
        debug_mode_smartchoice_add_default_options();
        update_option('debug_mode_last_auto_repair', date('Y-m-d H:i:s'));
        set_alert('success', 'Safe Auto Repair completed: ' . implode(' | ', $report));
        redirect(admin_url('debug_mode'));
    }


    public function monitoring()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $data['title'] = 'Site Monitoring And CRM Access';
        $data['monitors'] = $this->db->table_exists(db_prefix() . 'debug_mode_monitors')
            ? $this->db->order_by('name', 'asc')->get(db_prefix() . 'debug_mode_monitors')->result_array()
            : [];
        $data['ip_rules'] = $this->db->table_exists(db_prefix() . 'debug_mode_ip_rules')
            ? $this->db->order_by('label', 'asc')->get(db_prefix() . 'debug_mode_ip_rules')->result_array()
            : [];
        $this->load->view('monitoring', $data);
    }

    public function save_monitor()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $name = trim((string) $this->input->post('name'));
        $url = trim((string) $this->input->post('url'));
        if ($name === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            set_alert('danger', 'Enter a valid monitor name and URL.');
            redirect(admin_url('debug_mode/monitoring'));
        }
        $this->db->insert(db_prefix() . 'debug_mode_monitors', [
            'name' => $name,
            'url' => $url,
            'expected_code' => (int) ($this->input->post('expected_code') ?: 200),
            'is_active' => 1,
        ]);
        set_alert('success', 'Site monitor added.');
        redirect(admin_url('debug_mode/monitoring'));
    }

    public function delete_monitor($id = 0)
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $this->db->where('id', (int) $id)->delete(db_prefix() . 'debug_mode_monitors');
        set_alert('success', 'Site monitor deleted.');
        redirect(admin_url('debug_mode/monitoring'));
    }

    public function run_monitor_checks()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        if (!$this->db->table_exists(db_prefix() . 'debug_mode_monitors')) {
            set_alert('danger', 'Monitoring table is missing. Run Upgrade Database.');
            redirect(admin_url('debug_mode/monitoring'));
        }
        $rows = $this->db->where('is_active', 1)->get(db_prefix() . 'debug_mode_monitors')->result_array();
        foreach ($rows as $row) {
            $start = microtime(true);
            $code = 0;
            $status = 'offline';
            if (function_exists('curl_init')) {
                $ch = curl_init($row['url']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                @curl_exec($ch);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (PHP_VERSION_ID < 80500) { @curl_close($ch); }
                if ($code >= 200 && $code < 500) { $status = 'online'; }
            }
            $this->db->where('id', (int) $row['id'])->update(db_prefix() . 'debug_mode_monitors', [
                'last_status' => $status,
                'last_code' => $code,
                'last_latency_ms' => round((microtime(true) - $start) * 1000, 2),
                'last_checked_at' => date('Y-m-d H:i:s'),
            ]);
        }
        set_alert('success', 'Site monitoring checks completed.');
        redirect(admin_url('debug_mode/monitoring'));
    }

    public function save_ip_rule()
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $label = trim((string) $this->input->post('label'));
        $ip = trim((string) $this->input->post('ip_address'));
        if ($label === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            set_alert('danger', 'Enter a valid label and IP address.');
            redirect(admin_url('debug_mode/monitoring'));
        }
        $days = $this->input->post('days');
        $this->db->insert(db_prefix() . 'debug_mode_ip_rules', [
            'label' => $label,
            'ip_address' => $ip,
            'access_from' => $this->input->post('access_from') ?: null,
            'access_until' => $this->input->post('access_until') ?: null,
            'days' => is_array($days) ? implode(',', array_map('intval', $days)) : '',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        set_alert('success', 'CRM IP access rule saved. This controls CRM access only, not the WOW router.');
        redirect(admin_url('debug_mode/monitoring'));
    }

    public function delete_ip_rule($id = 0)
    {
        if (!is_admin()) { access_denied('CRM Utilities'); }
        $this->db->where('id', (int) $id)->delete(db_prefix() . 'debug_mode_ip_rules');
        set_alert('success', 'CRM IP access rule deleted.');
        redirect(admin_url('debug_mode/monitoring'));
    }

    private function build_network_info()
    {
        $info = [];
        $info['CRM Base URL'] = function_exists('base_url') ? base_url() : '';
        $info['Server Name'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        $info['Server Address'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $info['Remote Address'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $info['HTTP Host'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $info['Server Software'] = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
        $info['PHP SAPI'] = php_sapi_name();
        $info['DNS Flush Note'] = 'DNS flush must run on the Windows computer, not the web server. Use: ipconfig /flushdns';
        if (function_exists('shell_exec')) {
            $route = @shell_exec('ip route 2>/dev/null | head -20');
            if ($route) { $info['Server Route Info'] = trim($route); }
            $dns = @shell_exec('cat /etc/resolv.conf 2>/dev/null | head -20');
            if ($dns) { $info['Server DNS Info'] = trim($dns); }
        }
        return $info;
    }

    private function tree_to_html($items)
    {
        if (empty($items)) { return ''; }
        $out = '<ul>';
        foreach ($items as $item) {
            $out .= '<li><span class="folder">' . html_escape($item['name']) . '</span>' . $this->tree_to_html($item['children']) . '</li>';
        }
        return $out . '</ul>';
    }

    private function create_database_backup_file()
    {
        $dir = FCPATH . 'uploads/debug_mode_backups/';
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        if (!is_dir($dir) || !is_writable($dir)) { return false; }
        $file = $dir . 'crm_db_backup_' . date('Ymd_His') . '.sql';
        $fh = fopen($file, 'w');
        if (!$fh) { return false; }
        fwrite($fh, '-- Smart Choice CRM database backup generated ' . date('Y-m-d H:i:s') . "\n\n");
        foreach ($this->db->list_tables() as $table) {
            $create = $this->db->query('SHOW CREATE TABLE `' . str_replace('`', '', $table) . '`')->row_array();
            fwrite($fh, "\n\n-- Table `$table`\n");
            if (isset($create['Create Table'])) {
                fwrite($fh, 'DROP TABLE IF EXISTS `' . $table . "`;\n" . $create['Create Table'] . ";\n");
            }
            $rows = $this->db->get($table, 5000)->result_array();
            foreach ($rows as $row) {
                $cols = array_map(function($c){ return '`' . str_replace('`', '', $c) . '`'; }, array_keys($row));
                $vals = array_map(function($v){ return $v === null ? 'NULL' : $this->db->escape($v); }, array_values($row));
                fwrite($fh, 'INSERT INTO `' . $table . '` (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ");\n");
            }
        }
        fclose($fh);
        return $file;
    }

    private function scan_broken_links()
    {
        $rows = [];
        $tables = $this->db->list_tables();
        foreach ($tables as $table) {
            $safeTable = str_replace('`', '', (string) $table);

            // Skip temporary/backup/system tables that may appear in table inventory but not be queryable
            // through the current DB user, or that were created only for one-time repairs.
            if ($safeTable === ''
                || stripos($safeTable, 'backup_') === 0
                || stripos($safeTable, db_prefix() . 'backup_') === 0
                || stripos($safeTable, 'tblbackup_') === 0
                || stripos($safeTable, 'tmp_') === 0
            ) {
                continue;
            }

            if (!$this->db->table_exists($safeTable)) {
                continue;
            }

            try {
                $fields = $this->db->list_fields($safeTable);
            } catch (Throwable $e) {
                $rows[] = ['table' => $safeTable, 'field' => '', 'value' => $e->getMessage(), 'issue' => 'Skipped table - cannot read fields'];
                continue;
            }

            foreach ($fields as $field) {
                if (stripos($field, 'url') === false && stripos($field, 'link') === false && stripos($field, 'href') === false) {
                    continue;
                }

                try {
                    $query = $this->db->select('`' . str_replace('`', '', $field) . '`', false)
                        ->from('`' . $safeTable . '`', false)
                        ->where('`' . str_replace('`', '', $field) . '` IS NOT NULL', null, false)
                        ->limit(200)
                        ->get();
                } catch (Throwable $e) {
                    $rows[] = ['table' => $safeTable, 'field' => $field, 'value' => $e->getMessage(), 'issue' => 'Skipped field - query failed'];
                    continue;
                }

                foreach ($query->result_array() as $r) {
                    $value = trim((string) ($r[$field] ?? ''));
                    if ($value === '') {
                        continue;
                    }
                    if (preg_match('/^(ttps:\/\/|https:\/\/\/|http:\/\/\/|\/\/admin|.*adminmodules.*|.*moduless.*|.*modeless.*)/i', $value)) {
                        $rows[] = ['table' => $safeTable, 'field' => $field, 'value' => $value, 'issue' => 'Bad URL pattern'];
                    }
                }
            }
        }
        return array_slice($rows, 0, 300);
    }

    private function scan_orphan_files()
    {
        $roots = [FCPATH . 'uploads', FCPATH . 'modules'];
        $rows = [];
        $knownModuleFolders = [];
        if ($this->db->table_exists(db_prefix() . 'modules')) {
            foreach ($this->db->select('module_name')->get(db_prefix() . 'modules')->result_array() as $m) {
                $knownModuleFolders[] = $m['module_name'];
            }
        }
        foreach ($roots as $root) {
            if (!is_dir($root)) { continue; }
            $dirs = @scandir($root);
            if (!is_array($dirs)) { continue; }
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..' || $dir === 'index.html') { continue; }
                $full = rtrim($root, '/') . '/' . $dir;
                if (!is_dir($full)) { continue; }
                $relative = str_replace(FCPATH, '', $full);
                $status = 'Review';
                $reason = 'Folder exists under uploads/modules and should be reviewed before deletion.';
                if (basename($root) === 'modules' && !in_array($dir, $knownModuleFolders, true)) {
                    $status = 'Possible orphan';
                    $reason = 'Folder is not listed in tblmodules.';
                }
                $rows[] = ['path' => $relative, 'status' => $status, 'reason' => $reason];
            }
        }
        return array_slice($rows, 0, 400);
    }

    private function build_error_report_text($limit = 500)
    {
        $rows = $this->build_error_report($limit);
        if (empty($rows)) { return 'No recent CRM error lines found.'; }
        $out = [];
        foreach ($rows as $row) { $out[] = '[' . $row['file'] . '] ' . $row['line']; }
        return implode("\n", $out);
    }

    private function build_folder_tree($path, $depth = 0, $maxDepth = 6)
    {
        $items = [];
        if ($depth > $maxDepth || !is_dir($path) || !is_readable($path)) { return $items; }
        $skip = ['.', '..', '.git', 'node_modules', 'vendor/bin', 'cache', 'logs'];
        $dirs = @scandir($path);
        if (!is_array($dirs)) { return $items; }
        foreach ($dirs as $dir) {
            if (in_array($dir, $skip, true)) { continue; }
            $full = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($full)) { continue; }
            $items[] = ['name' => $dir, 'path' => str_replace(FCPATH, '', $full), 'children' => $this->build_folder_tree($full, $depth + 1, $maxDepth)];
        }
        usort($items, static function ($a, $b) { return strcasecmp($a['name'], $b['name']); });
        return $items;
    }

    private function clear_cache_files()
    {
        $paths = [APPPATH . 'cache', FCPATH . 'application/cache'];
        $deleted = 0;
        foreach ($paths as $path) {
            if (!is_dir($path)) { continue; }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $file) {
                $basename = $file->getBasename();
                if ($basename === 'index.html' || $basename === '.htaccess') { continue; }
                if ($file->isFile()) { if (@unlink($file->getPathname())) { $deleted++; } }
                elseif ($file->isDir()) { @rmdir($file->getPathname()); }
            }
        }
        return $deleted;
    }

    private function build_error_report($limit = 50)
    {
        $files = [APPPATH . 'logs/log-' . date('Y-m-d') . '.php', APPPATH . 'logs/log-' . date('Y-m-d', strtotime('-1 day')) . '.php', APPPATH . 'logs/log-' . date('Y-m-d', strtotime('-2 day')) . '.php'];
        $rows = [];
        foreach ($files as $file) {
            if (!is_file($file)) { continue; }
            $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!$lines) { continue; }
            $lines = array_slice($lines, -220);
            foreach ($lines as $line) {
                if (stripos($line, 'ERROR') !== false || stripos($line, 'Severity:') !== false || stripos($line, 'Exception') !== false || stripos($line, 'ParseError') !== false) {
                    $rows[] = ['file' => basename($file), 'line' => $line];
                }
            }
        }
        return array_slice(array_reverse($rows), 0, (int) $limit);
    }

    private function build_database_report($action = false)
    {
        $tables = $this->db->list_tables();
        $rows = [];
        foreach ($tables as $table) {
            $safeTable = str_replace('`', '', $table);
            if ($action === false) {
                $rows[] = ['table' => $safeTable, 'operation' => 'Inventory', 'message' => 'Table found'];
                continue;
            }
            $sqlAction = strtoupper($action);
            $query = $this->db->query($sqlAction . ' TABLE `' . $safeTable . '`');
            if ($query && method_exists($query, 'result_array')) {
                foreach ($query->result_array() as $result) {
                    $rows[] = ['table' => $safeTable, 'operation' => $sqlAction, 'message' => json_encode($result)];
                }
            }
        }
        return array_slice($rows, 0, 300);
    }
}
