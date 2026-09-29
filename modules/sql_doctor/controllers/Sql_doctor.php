<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sql_doctor extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin()) {
            access_denied('SQL Doctor');
        }
    }

    public function index()
    {
        $data['title'] = _l('sql_doctor');
        $data['health'] = $this->collect_health();
        $this->load->view('dashboard', $data);
    }

    public function clear_cache()
    {
        $count = $this->clean_folder(APPPATH.'cache');
        set_alert('success', _l('sql_doctor_cache_cleared').' '.$count);
        redirect(admin_url('sql_doctor'));
    }

    public function clear_temp()
    {
        $count = $this->clean_folder(FCPATH.'temp');
        set_alert('success', _l('sql_doctor_temp_cleared').' '.$count);
        redirect(admin_url('sql_doctor'));
    }

    public function create_index_files()
    {
        $created = 0;
        $roots = [APPPATH.'cache', FCPATH.'temp', module_dir_path('sql_doctor')];
        foreach ($roots as $root) {
            if (is_dir($root)) {
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
                foreach ($it as $file) {
                    if ($file->isDir()) {
                        $index = $file->getPathname().DIRECTORY_SEPARATOR.'index.html';
                        if (!file_exists($index)) {
                            @file_put_contents($index, '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>');
                            $created++;
                        }
                    }
                }
            }
        }
        set_alert('success', _l('sql_doctor_index_files_created').' '.$created);
        redirect(admin_url('sql_doctor'));
    }

    public function logs()
    {
        $data['title'] = _l('sql_doctor_logs');
        $data['logs'] = $this->latest_logs();
        $this->load->view('logs', $data);
    }

    public function backup_database()
    {
        $db = $this->db->database;
        $backup_dir = FCPATH.'uploads/sql_doctor_backups/';
        if (!is_dir($backup_dir)) {
            @mkdir($backup_dir, 0755, true);
        }
        $file = 'backup_'.$db.'_'.date('Ymd_His').'.sql';
        $path = $backup_dir.$file;

        $tables = $this->db->list_tables();
        $out = "-- SQL Doctor Backup\n-- Database: {$db}\n-- Created: ".date('Y-m-d H:i:s')."\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";
        foreach ($tables as $table) {
            $create = $this->db->query('SHOW CREATE TABLE `'.$table.'`')->row_array();
            $create_sql = isset($create['Create Table']) ? $create['Create Table'] : array_values($create)[1];
            $out .= "DROP TABLE IF EXISTS `{$table}`;\n{$create_sql};\n\n";
            $rows = $this->db->get($table)->result_array();
            foreach ($rows as $row) {
                $cols = array_map(function($c){ return '`'.$c.'`'; }, array_keys($row));
                $vals = array_map(function($v){ return is_null($v) ? 'NULL' : get_instance()->db->escape($v); }, array_values($row));
                $out .= 'INSERT INTO `'.$table.'` ('.implode(',', $cols).') VALUES ('.implode(',', $vals).');' . "\n";
            }
            $out .= "\n";
        }
        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        @file_put_contents($path, $out);

        if (file_exists($path)) {
            $this->db->insert(db_prefix().'sql_doctor_backups', [
                'file_name' => $file,
                'file_path' => $path,
                'file_size' => filesize($path),
                'created_by' => get_staff_user_id(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            set_alert('success', _l('sql_doctor_backup_created'));
        } else {
            set_alert('danger', _l('sql_doctor_backup_failed'));
        }
        redirect(admin_url('sql_doctor'));
    }

    public function download_backup($id)
    {
        $backup = $this->db->where('id', (int)$id)->get(db_prefix().'sql_doctor_backups')->row();
        if (!$backup || !file_exists($backup->file_path)) {
            show_404();
        }
        $this->load->helper('download');
        force_download($backup->file_name, file_get_contents($backup->file_path));
    }

    private function clean_folder($path)
    {
        $count = 0;
        if (!is_dir($path)) {
            return 0;
        }
        foreach (scandir($path) as $file) {
            if ($file === '.' || $file === '..' || $file === 'index.html' || $file === '.htaccess') {
                continue;
            }
            $full = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;
            if (is_file($full)) {
                @unlink($full); $count++;
            }
        }
        return $count;
    }

    private function collect_health()
    {
        return [
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->db->query('SELECT VERSION() as v')->row()->v,
            'cache_writable' => is_writable(APPPATH.'cache') ? 'Yes' : 'No',
            'temp_writable' => is_writable(FCPATH.'temp') ? 'Yes' : 'No',
            'modules_count' => $this->db->count_all(db_prefix().'modules'),
            'active_modules' => $this->db->where('active',1)->count_all_results(db_prefix().'modules'),
            'backups' => $this->db->order_by('id','DESC')->get(db_prefix().'sql_doctor_backups', 10)->result(),
        ];
    }

    private function latest_logs()
    {
        $dir = APPPATH.'logs/';
        $files = glob($dir.'log-*.php');
        rsort($files);
        $logs = [];
        foreach (array_slice($files,0,3) as $file) {
            $content = @file_get_contents($file);
            $logs[] = ['file'=>basename($file), 'content'=>substr($content, -20000)];
        }
        return $logs;
    }
}
