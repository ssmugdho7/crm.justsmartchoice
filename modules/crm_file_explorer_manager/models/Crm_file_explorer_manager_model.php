<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Crm_file_explorer_manager_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_summary()
    {
        $row = $this->db->select('COUNT(*) as total_files, COALESCE(SUM(file_size),0) as total_size')
            ->from(db_prefix() . 'crm_file_explorer_index')
            ->get()->row_array();

        $images = $this->db->where('file_type', 'image')->count_all_results(db_prefix() . 'crm_file_explorer_index');
        $videos = $this->db->where('file_type', 'video')->count_all_results(db_prefix() . 'crm_file_explorer_index');
        $docs   = $this->db->where('file_type', 'document')->count_all_results(db_prefix() . 'crm_file_explorer_index');

        return [
            'total_files' => (int)($row['total_files'] ?? 0),
            'total_size'  => (int)($row['total_size'] ?? 0),
            'images'      => (int)$images,
            'videos'      => (int)$videos,
            'documents'   => (int)$docs,
        ];
    }

    public function run_scan($basePath = null, $scanType = 'full')
    {
        $basePath = $this->normalize_base_path($basePath ?: get_option('crm_file_explorer_manager_root_path'));
        $maxFiles = max(100, (int) get_option('crm_file_explorer_manager_max_scan_files'));

        if (!$basePath || !is_dir($basePath)) {
            return ['success' => false, 'message' => _l('invalid_folder_path')];
        }

        $this->db->insert(db_prefix() . 'crm_file_explorer_scans', [
            'scan_type'   => $scanType,
            'base_path'   => $basePath,
            'total_files' => 0,
            'total_size'  => 0,
            'created_by'  => get_staff_user_id(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $scanId = (int) $this->db->insert_id();

        $count = 0;
        $size = 0;
        $inserted = 0;
        $updated = 0;
        $seenHashes = [];
        $truncated = false;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($count >= $maxFiles) {
                $truncated = true;
                break;
            }
            if (!$file->isFile()) {
                continue;
            }

            $path = $file->getPathname();
            if ($this->should_skip($path)) {
                continue;
            }

            $fileSize = (int) $file->getSize();
            $relative = str_replace('\\', '/', $this->relative_path($path));
            $pathHash = sha1($relative);
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $type = $this->detect_file_type($ext);
            $data = [
                'scan_id'       => $scanId,
                'relative_path' => $relative,
                'path_hash'     => $pathHash,
                'file_name'     => basename($path),
                'extension'     => $ext,
                'mime_type'     => function_exists('mime_content_type') ? (string) @mime_content_type($path) : '',
                'file_type'     => $type,
                'file_size'     => $fileSize,
                'created_time'  => date('Y-m-d H:i:s', (int) @filectime($path)),
                'modified_time' => date('Y-m-d H:i:s', (int) @filemtime($path)),
                'module_guess'  => $this->guess_module($relative),
            ];

            $existingRows = $this->db->select('id')
                ->where('relative_path', $relative)
                ->order_by('id', 'ASC')
                ->get(db_prefix() . 'crm_file_explorer_index')
                ->result_array();

            if (!empty($existingRows)) {
                $keeperId = (int) $existingRows[0]['id'];
                $this->db->where('id', $keeperId)->update(db_prefix() . 'crm_file_explorer_index', $data);
                if (count($existingRows) > 1) {
                    $duplicateIds = array_map('intval', array_column(array_slice($existingRows, 1), 'id'));
                    $this->db->where_in('id', $duplicateIds)->delete(db_prefix() . 'crm_file_explorer_index');
                }
                $updated++;
            } else {
                $this->db->insert(db_prefix() . 'crm_file_explorer_index', $data);
                $inserted++;
            }

            $seenHashes[] = $pathHash;
            $count++;
            $size += $fileSize;
        }

        $deleted = $truncated ? 0 : $this->delete_missing_index_rows($basePath, $seenHashes);

        $this->db->where('id', $scanId)->update(db_prefix() . 'crm_file_explorer_scans', [
            'total_files' => $count,
            'total_size'  => $size,
        ]);

        return [
            'success' => true,
            'scan_id' => $scanId,
            'total_files' => $count,
            'total_size' => $size,
            'inserted' => $inserted,
            'updated' => $updated,
            'deleted' => $deleted,
            'truncated' => $truncated,
        ];
    }

    private function delete_missing_index_rows($basePath, array $seenHashes)
    {
        $baseRelative = str_replace('\\', '/', $this->relative_path(rtrim($basePath, DIRECTORY_SEPARATOR)));
        $this->db->select('id, relative_path, path_hash')->from(db_prefix() . 'crm_file_explorer_index');
        if ($baseRelative !== '' && strpos($baseRelative, ':') === false) {
            $this->db->like('relative_path', trim($baseRelative, '/') . '/', 'after');
        }
        $rows = $this->db->get()->result_array();
        $seen = array_fill_keys($seenHashes, true);
        $deleted = 0;
        foreach ($rows as $row) {
            if (!isset($seen[$row['path_hash']])) {
                $this->db->where('id', (int) $row['id'])->delete(db_prefix() . 'crm_file_explorer_index');
                $deleted++;
            }
        }
        return $deleted;
    }

    public function get_files($type = '', $search = '', $limit = 300)
    {
        $this->db->from(db_prefix() . 'crm_file_explorer_index');
        if ($type !== '') {
            $this->db->where('file_type', $type);
        }
        if ($search !== '') {
            $this->db->group_start()
                ->like('file_name', $search)
                ->or_like('relative_path', $search)
                ->or_like('extension', $search)
                ->or_like('module_guess', $search)
                ->group_end();
        }
        return $this->db->order_by('modified_time', 'DESC')->limit($limit)->get()->result_array();
    }


    public function reset_scan_index()
    {
        if ($this->db->table_exists(db_prefix() . 'crm_file_explorer_index')) {
            $this->db->empty_table(db_prefix() . 'crm_file_explorer_index');
        }
        if ($this->db->table_exists(db_prefix() . 'crm_file_explorer_scans')) {
            $this->db->empty_table(db_prefix() . 'crm_file_explorer_scans');
        }
        return true;
    }

    public function get_file($id)
    {
        return $this->db->where('id', (int)$id)->get(db_prefix() . 'crm_file_explorer_index')->row_array();
    }

    public function find_associations($file)
    {
        $results = [];
        if (!$file) {
            return $results;
        }
        $name = $file['file_name'];
        $path = $file['relative_path'];
        $tables = [
            db_prefix() . 'files' => ['file_name', 'rel_type'],
            db_prefix() . 'project_files' => ['file_name', 'project_id'],
            db_prefix() . 'task_comments' => ['content', 'taskid'],
            db_prefix() . 'notes' => ['description', 'rel_type'],
            db_prefix() . 'contracts' => ['content', 'subject'],
            db_prefix() . 'proposals' => ['content', 'subject'],
            db_prefix() . 'estimates' => ['clientnote', 'id'],
            db_prefix() . 'invoices' => ['clientnote', 'id'],
        ];

        foreach ($tables as $table => $cols) {
            if (!$this->db->table_exists($table)) {
                continue;
            }
            $fields = $this->db->list_fields($table);
            foreach ($cols as $col) {
                if (!in_array($col, $fields, true)) {
                    continue;
                }
                $this->db->from($table);
                $this->db->like($col, $name);
                $count = $this->db->count_all_results();
                if ($count > 0) {
                    $results[] = [
                        'module' => $this->human_module_name($table),
                        'table'  => $table,
                        'field'  => $col,
                        'matches'=> $count,
                    ];
                }
            }
        }

        if (empty($results) && !empty($file['module_guess'])) {
            $results[] = [
                'module' => ucwords(str_replace('_', ' ', $file['module_guess'])),
                'table' => 'Folder path guess',
                'field' => 'Relative Path',
                'matches' => 1,
            ];
        }

        return $results;
    }

    public function health_check()
    {
        $checks = [];
        $tables = ['crm_file_explorer_scans', 'crm_file_explorer_index', 'crm_file_explorer_backups'];
        foreach ($tables as $table) {
            $checks[] = ['name' => _l('health_database_table') . ': ' . $table, 'status' => $this->db->table_exists(db_prefix() . $table) ? _l('health_ok') : _l('health_missing')];
        }
        $root = get_option('crm_file_explorer_manager_root_path');
        $checks[] = ['name' => _l('root_folder'), 'status' => is_dir($root) ? _l('health_ok') : _l('health_missing')];
        $checks[] = ['name' => _l('backup_folder_writable'), 'status' => is_writable(__DIR__ . '/../uploads/backups') ? _l('health_ok') : _l('health_not_writable')];
        $checks[] = ['name' => _l('zip_extension'), 'status' => class_exists('ZipArchive') ? _l('health_ok') : _l('health_missing')];
        return $checks;
    }

    public function create_backup_zip($backupType = 'module_files')
    {
        if (get_option('crm_file_explorer_manager_allow_backup_zip') != '1') {
            return ['success' => false, 'message' => _l('backup_disabled')];
        }
        if (!class_exists('ZipArchive')) {
            return ['success' => false, 'message' => _l('zip_extension_missing')];
        }

        $base = FCPATH;
        $name = 'crm-file-backup-' . $backupType . '-' . date('Ymd-His') . '.zip';
        $backupDir = __DIR__ . '/../uploads/backups/';
        if (!is_dir($backupDir) && !@mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
            return ['success' => false, 'message' => _l('backup_folder_create_failed')];
        }
        $target = $backupDir . $name;
        $zip = new ZipArchive();
        if ($zip->open($target, ZipArchive::CREATE) !== true) {
            return ['success' => false, 'message' => _l('backup_create_failed')];
        }

        $limit = 3000;
        $count = 0;
        $paths = [];
        if ($backupType === 'modules_only') {
            $paths[] = FCPATH . 'modules';
        } elseif ($backupType === 'crm_without_uploads') {
            $paths[] = FCPATH;
        } else {
            $paths[] = FCPATH . 'modules';
            $paths[] = APPPATH;
        }

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
            foreach ($it as $file) {
                if ($count >= $limit || !$file->isFile()) {
                    continue;
                }
                $filePath = $file->getPathname();
                if ($backupType === 'crm_without_uploads' && $this->is_upload_or_media_file($filePath)) {
                    continue;
                }
                if ($this->should_skip($filePath)) {
                    continue;
                }
                $zip->addFile($filePath, $this->relative_path($filePath));
                $count++;
            }
        }
        $zip->close();
        $size = filesize($target);

        $this->db->insert(db_prefix() . 'crm_file_explorer_backups', [
            'backup_name'   => $name,
            'backup_type'   => $backupType,
            'relative_path' => 'modules/crm_file_explorer_manager/uploads/backups/' . $name,
            'file_size'     => $size,
            'created_by'    => get_staff_user_id(),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'file' => $name, 'size' => $size, 'count' => $count];
    }

    public function get_backups()
    {
        return $this->db->order_by('id', 'DESC')->get(db_prefix() . 'crm_file_explorer_backups')->result_array();
    }

    public function delete_backup($id)
    {
        $row = $this->db->where('id', (int) $id)->get(db_prefix() . 'crm_file_explorer_backups')->row_array();
        if (!$row) {
            return ['success' => false, 'message' => _l('backup_not_found')];
        }

        $base = realpath(FCPATH);
        $candidate = FCPATH . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $row['relative_path']), DIRECTORY_SEPARATOR);
        $path = realpath($candidate);

        if ($path && $base && strpos($path, $base) === 0 && is_file($path)) {
            if (!@unlink($path)) {
                log_activity('CRM File Explorer backup delete failed: ' . $row['relative_path']);
                return ['success' => false, 'message' => _l('backup_delete_failed')];
            }
        }

        $this->db->where('id', (int) $id)->delete(db_prefix() . 'crm_file_explorer_backups');
        if ($this->db->affected_rows() < 1) {
            return ['success' => false, 'message' => _l('backup_delete_failed')];
        }

        log_activity('CRM File Explorer backup deleted: ' . $row['backup_name']);
        return ['success' => true, 'message' => _l('backup_deleted_successfully')];
    }

    public function normalize_base_path($path)
    {
        $real = realpath($path);
        if (!$real) {
            return false;
        }
        return rtrim($real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function relative_path($path)
    {
        return ltrim(str_replace(FCPATH, '', $path), DIRECTORY_SEPARATOR);
    }

    private function detect_file_type($ext)
    {
        if (in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg','tiff'], true)) return 'image';
        if (in_array($ext, ['mp4','mov','webm','avi','mkv','m4v'], true)) return 'video';
        if (in_array($ext, ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv','zip','rar'], true)) return 'document';
        if (in_array($ext, ['php','js','css','html','json','xml'], true)) return 'code';
        return 'other';
    }

    private function guess_module($relative)
    {
        $parts = explode('/', str_replace('\\', '/', $relative));
        $key = array_search('modules', $parts, true);
        if ($key !== false && isset($parts[$key + 1])) {
            return $parts[$key + 1];
        }
        if (isset($parts[0]) && $parts[0] === 'uploads' && isset($parts[1])) {
            return 'uploads / ' . $parts[1];
        }
        return '';
    }

    private function human_module_name($table)
    {
        $name = str_replace(db_prefix(), '', $table);
        return ucwords(str_replace('_', ' ', $name));
    }

    private function should_skip($path)
    {
        $skip = ['/.git/', '/node_modules/', '/vendor/bin/', '/application/cache/', '/modules/crm_file_explorer_manager/uploads/backups/'];
        $normalized = str_replace('\\', '/', $path);
        foreach ($skip as $needle) {
            if (strpos($normalized, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    private function is_upload_or_media_file($path)
    {
        $normalized = str_replace('\\', '/', $path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mediaExt = ['jpg','jpeg','png','gif','webp','bmp','svg','mp4','mov','webm','avi','pdf','doc','docx','xls','xlsx','zip','rar'];
        return strpos($normalized, '/uploads/') !== false || in_array($ext, $mediaExt, true);
    }
}
