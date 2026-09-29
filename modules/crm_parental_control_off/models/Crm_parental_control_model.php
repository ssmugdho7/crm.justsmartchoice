<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Crm_parental_control_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function health()
    {
        $checks = [];
        $requiredTables = ['roles','staff','modules','options','crm_pc_runs','crm_pc_departments','crm_pc_role_templates','crm_pc_backups','crm_pc_audit_log'];
        foreach ($requiredTables as $table) {
            $checks[] = ['name' => db_prefix() . $table, 'status' => $this->db->table_exists(db_prefix() . $table) ? 'ok' : 'missing'];
        }

        $checks[] = ['name' => 'Role Permission Storage', 'status' => $this->role_permission_storage_type() !== 'none' ? 'ok' : 'warning'];
        $checks[] = ['name' => 'Staff Permission Storage', 'status' => $this->staff_permission_storage_exists() ? 'ok' : 'not used'];

        $backupDir = $this->backup_dir();
        $checks[] = ['name' => 'Backup Folder Writable', 'status' => is_dir($backupDir) && is_writable($backupDir) ? 'ok' : 'warning'];
        $checks[] = ['name' => 'ZipArchive Available', 'status' => class_exists('ZipArchive') ? 'ok' : 'warning'];
        $checks[] = ['name' => 'Module Version', 'status' => CRM_PARENTAL_CONTROL_VERSION];
        return $checks;
    }

    private function role_permission_storage_type()
    {
        if ($this->db->table_exists(db_prefix() . 'rolepermissions')) { return 'rolepermissions_table'; }
        if ($this->db->table_exists(db_prefix() . 'role_permissions')) { return 'role_permissions_table'; }
        if ($this->db->table_exists(db_prefix() . 'roles') && $this->db->field_exists('permissions', db_prefix() . 'roles')) { return 'roles_permissions_field'; }
        return 'none';
    }

    private function staff_permission_storage_exists()
    {
        return $this->db->table_exists(db_prefix() . 'staffpermissions')
            || $this->db->table_exists(db_prefix() . 'staff_permissions')
            || ($this->db->table_exists(db_prefix() . 'staff') && $this->db->field_exists('permissions', db_prefix() . 'staff'));
    }

    public function backup_dir()
    {
        $dir = FCPATH . 'uploads/crm_parental_control_backups/';
        if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
        if (!file_exists($dir . 'index.html')) { @file_put_contents($dir . 'index.html', ''); }
        return $dir;
    }

    public function log_run($runType, $status, $summary, $snapshot = null)
    {
        $this->db->insert(db_prefix() . 'crm_pc_runs', [
            'run_type' => $runType,
            'status' => $status,
            'summary' => is_array($summary) ? json_encode($summary) : $summary,
            'snapshot_json' => $snapshot === null ? null : json_encode($snapshot),
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    public function audit($eventType, $details)
    {
        $this->db->insert(db_prefix() . 'crm_pc_audit_log', [
            'event_type' => $eventType,
            'details' => is_array($details) ? json_encode($details) : $details,
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function create_snapshot($type = 'manual')
    {
        $snapshot = [];
        foreach (['roles','rolepermissions','role_permissions','staffpermissions','staff_permissions','staff','crm_pc_departments','crm_pc_role_templates'] as $table) {
            if ($this->db->table_exists(db_prefix() . $table)) {
                $snapshot[$table] = $this->db->get(db_prefix() . $table)->result_array();
            }
        }
        $id = $this->log_run($type, 'snapshot', 'Snapshot created before changes.', $snapshot);
        return ['id' => $id, 'snapshot' => $snapshot];
    }

    public function create_database_backup()
    {
        $this->load->dbutil();
        $filename = 'database_backup_' . date('Ymd_His') . '.sql.gz';
        $path = $this->backup_dir() . $filename;
        $prefs = ['format' => 'gzip', 'filename' => 'smart_choice_crm_' . date('Ymd_His') . '.sql'];
        $backup = $this->dbutil->backup($prefs);
        @file_put_contents($path, $backup);
        $size = file_exists($path) ? filesize($path) : 0;
        $status = $size > 0 ? 'created' : 'failed';
        $message = $size > 0 ? 'Database backup created.' : 'Database backup failed or produced an empty file.';
        $this->record_backup('database', $path, $size, $status, $message);
        return ['path' => $path, 'size' => $size, 'status' => $status, 'message' => $message];
    }

    public function create_crm_backup()
    {
        if (!class_exists('ZipArchive')) {
            return ['status' => 'failed', 'message' => 'ZipArchive is not available on this server.'];
        }

        @set_time_limit(25);
        $filename = 'crm_safe_package_' . date('Ymd_His') . '.zip';
        $path = $this->backup_dir() . $filename;
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return ['status' => 'failed', 'message' => 'Cannot create CRM safe package.'];
        }

        $maxFiles = 2500;
        $maxBytes = 45 * 1024 * 1024;
        $addedFiles = 0;
        $addedBytes = 0;
        $truncated = false;
        $manifest = [
            'created_at' => date('Y-m-d H:i:s'),
            'module_version' => CRM_PARENTAL_CONTROL_VERSION,
            'backup_type' => 'shared_hosting_safe_package',
            'note' => 'This package excludes uploads, cache, vendor, backups, and oversized files to avoid shared-hosting 503 errors. Use cPanel File Manager or hosting backup for a complete full-site archive.',
            'included_roots' => ['modules', 'application/config', 'application/helpers', 'application/language', 'application/views'],
            'excluded' => ['uploads', 'vendor', 'cache', 'logs', 'node_modules', '.git', 'crm_parental_control_backups'],
        ];
        $zip->addFromString('CRM_SAFE_BACKUP_README.json', json_encode($manifest, JSON_PRETTY_PRINT));

        foreach ($manifest['included_roots'] as $folder) {
            $source = FCPATH . $folder;
            if (!file_exists($source)) { continue; }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($source), RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if (!$file->isFile()) { continue; }
                $filePath = $file->getRealPath();
                if ($this->is_excluded_backup_path($filePath)) { continue; }
                $fileSize = $file->getSize();
                if ($fileSize > 5 * 1024 * 1024) { continue; }
                if ($addedFiles >= $maxFiles || ($addedBytes + $fileSize) > $maxBytes) { $truncated = true; break 2; }
                $relativePath = $folder . '/' . substr($filePath, strlen(realpath($source)) + 1);
                $zip->addFile($filePath, $relativePath);
                $addedFiles++;
                $addedBytes += $fileSize;
            }
        }
        $zip->addFromString('CRM_SAFE_BACKUP_SUMMARY.json', json_encode(['added_files' => $addedFiles, 'added_bytes' => $addedBytes, 'truncated' => $truncated], JSON_PRETTY_PRINT));
        $zip->close();

        $size = file_exists($path) ? filesize($path) : 0;
        $status = $size > 0 ? ($truncated ? 'partial' : 'created') : 'failed';
        $message = $status === 'partial'
            ? 'CRM safe package created partially to avoid shared hosting timeout. Use cPanel for a full CRM file backup.'
            : ($status === 'created' ? 'CRM safe package created.' : 'CRM safe package failed or produced an empty file.');
        $this->record_backup('crm_safe_package', $path, $size, $status, $message);
        return ['path' => $path, 'size' => $size, 'status' => $status, 'message' => $message, 'files' => $addedFiles];
    }

    private function is_excluded_backup_path($filePath)
    {
        $normalized = str_replace('\\', '/', $filePath);
        foreach (['/uploads/', '/vendor/', '/cache/', '/logs/', '/node_modules/', '/.git/', '/crm_parental_control_backups/'] as $blocked) {
            if (strpos($normalized, $blocked) !== false) { return true; }
        }
        return false;
    }

    private function record_backup($type, $path, $size, $status, $message)
    {
        $this->db->insert(db_prefix() . 'crm_pc_backups', [
            'backup_type' => $type,
            'file_path' => $path,
            'file_size' => $size,
            'status' => $status,
            'message' => $message,
            'created_by' => get_staff_user_id(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function create_module_zip()
    {
        if (!class_exists('ZipArchive')) { return ['status' => 'failed', 'message' => 'ZipArchive is not available on this server.']; }
        $source = realpath(__DIR__ . '/..');
        if (!$source || !is_dir($source)) { return ['status' => 'failed', 'message' => 'Module folder was not found.']; }
        $filename = 'crm_parental_control_module_' . CRM_PARENTAL_CONTROL_VERSION . '_' . date('Ymd_His') . '.zip';
        $path = $this->backup_dir() . $filename;
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { return ['status' => 'failed', 'message' => 'Cannot create module Zip.']; }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile()) { continue; }
            $filePath = $file->getRealPath();
            $relativePath = CRM_PARENTAL_CONTROL_MODULE_NAME . '/' . substr($filePath, strlen($source) + 1);
            $zip->addFile($filePath, $relativePath);
        }
        $zip->close();
        $size = file_exists($path) ? filesize($path) : 0;
        $status = $size > 0 ? 'created' : 'failed';
        $message = $size > 0 ? 'Module Zip created.' : 'Module Zip failed or produced an empty file.';
        $this->record_backup('module_zip', $path, $size, $status, $message);
        return ['path' => $path, 'size' => $size, 'status' => $status, 'message' => $message];
    }

    public function standard_departments()
    {
        return $this->db->get(db_prefix() . 'crm_pc_departments')->result_array();
    }

    public function role_templates()
    {
        return $this->db->order_by('security_level', 'DESC')->get(db_prefix() . 'crm_pc_role_templates')->result_array();
    }

    public function current_roles()
    {
        return $this->db->table_exists(db_prefix() . 'roles') ? $this->db->get(db_prefix() . 'roles')->result_array() : [];
    }

    public function current_staff()
    {
        return $this->db->select('staffid, firstname, lastname, email, role, active, admin')->get(db_prefix() . 'staff')->result_array();
    }

    public function preview_company_standard()
    {
        $roles = $this->current_roles();
        $roleNames = array_map(function($r){ return trim($r['name'] ?? ''); }, $roles);
        $templates = $this->role_templates();
        $missingRoles = [];
        foreach ($templates as $template) {
            if (!in_array($template['role_name'], $roleNames)) { $missingRoles[] = $template['role_name']; }
        }
        return [
            'missing_roles' => $missingRoles,
            'existing_roles' => $roleNames,
            'delete_permission_rows_found' => $this->count_delete_permissions(),
            'permission_storage' => $this->role_permission_storage_type(),
            'staff_count' => count($this->current_staff()),
            'departments' => $this->standard_departments(),
        ];
    }

    private function count_delete_permissions()
    {
        $type = $this->role_permission_storage_type();
        if ($type === 'rolepermissions_table' && $this->db->field_exists('capability', db_prefix() . 'rolepermissions')) {
            $this->db->group_start()->where('capability', 'delete')->or_where('capability', 'permission_delete')->group_end();
            return $this->db->count_all_results(db_prefix() . 'rolepermissions');
        }
        if ($type === 'role_permissions_table' && $this->db->field_exists('capability', db_prefix() . 'role_permissions')) {
            $this->db->group_start()->where('capability', 'delete')->or_where('capability', 'permission_delete')->group_end();
            return $this->db->count_all_results(db_prefix() . 'role_permissions');
        }
        if ($type === 'roles_permissions_field') {
            $count = 0;
            foreach ($this->current_roles() as $role) {
                $raw = $role['permissions'] ?? '';
                if (stripos($raw, 'delete') !== false || stripos($raw, 'permission_delete') !== false) { $count++; }
            }
            return $count;
        }
        return 0;
    }

    public function apply_company_standard()
    {
        $snapshot = $this->create_snapshot('apply_company_standard');
        $createdRoles = [];
        foreach ($this->role_templates() as $template) {
            $existing = $this->db->where('name', $template['role_name'])->get(db_prefix() . 'roles')->row_array();
            if (!$existing && $this->db->table_exists(db_prefix() . 'roles')) {
                $this->db->insert(db_prefix() . 'roles', ['name' => $template['role_name']]);
                $createdRoles[] = $template['role_name'];
            }
        }
        $removedDelete = $this->remove_non_owner_delete_permissions();
        $summary = ['created_roles' => $createdRoles, 'removed_non_owner_delete_permissions' => $removedDelete, 'snapshot_id' => $snapshot['id'], 'permission_storage' => $this->role_permission_storage_type()];
        $this->log_run('apply_company_standard', 'applied', $summary, null);
        $this->audit('company_standard_applied', $summary);
        return $summary;
    }

    private function remove_non_owner_delete_permissions()
    {
        $removed = 0;
        $type = $this->role_permission_storage_type();
        if ($type !== 'rolepermissions_table' && $type !== 'role_permissions_table') { return 0; }
        $table = $type === 'rolepermissions_table' ? 'rolepermissions' : 'role_permissions';
        $full = db_prefix() . $table;
        if (!$this->db->field_exists('capability', $full)) { return 0; }
        $ownerRows = $this->db->where('name', 'Owner')->get(db_prefix() . 'roles')->result_array();
        $ownerRoleIds = array_map(function($r){ return $r['roleid']; }, $ownerRows);
        $this->db->group_start()->where('capability', 'delete')->or_where('capability', 'permission_delete')->group_end();
        if (!empty($ownerRoleIds) && $this->db->field_exists('roleid', $full)) { $this->db->where_not_in('roleid', $ownerRoleIds); }
        $rows = $this->db->get($full)->result_array();
        foreach ($rows as $row) {
            if (isset($row['id'])) { $this->db->where('id', $row['id'])->delete($full); $removed++; }
        }
        return $removed;
    }

    public function rollback_last_apply()
    {
        $run = $this->db->where('run_type', 'apply_company_standard')->where('status', 'snapshot')->order_by('id', 'DESC')->get(db_prefix() . 'crm_pc_runs')->row_array();
        if (!$run || empty($run['snapshot_json'])) { return ['status' => 'failed', 'message' => 'No rollback snapshot found.']; }
        $snapshot = json_decode($run['snapshot_json'], true);
        if (!is_array($snapshot)) { return ['status' => 'failed', 'message' => 'Rollback snapshot is invalid.']; }
        foreach (['roles','rolepermissions','role_permissions','staffpermissions','staff_permissions'] as $table) {
            if (isset($snapshot[$table]) && $this->db->table_exists(db_prefix() . $table)) {
                $this->db->truncate(db_prefix() . $table);
                foreach ($snapshot[$table] as $row) { $this->db->insert(db_prefix() . $table, $row); }
            }
        }
        $this->log_run('rollback', 'completed', 'Restored latest permission snapshot.', null);
        $this->audit('rollback_completed', ['snapshot_run_id' => $run['id']]);
        return ['status' => 'completed', 'message' => 'Latest permission snapshot restored.'];
    }
}
