<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Database backup of customer e-signature images so a deleted upload
 * folder never loses a signed document's signature.
 */
function signature_backup_table()
{
    static $ready = false;
    $CI    = &get_instance();
    $table = db_prefix() . 'signature_images';
    if ($ready || $CI->db->table_exists($table)) {
        return $ready = $table;
    }
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . $table . '` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `rel_type` VARCHAR(20) NOT NULL,
        `rel_id` INT(11) NOT NULL,
        `file_name` VARCHAR(191) NOT NULL,
        `mime` VARCHAR(50) NOT NULL DEFAULT \'image/png\',
        `data` MEDIUMBLOB NOT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `rel_file` (`rel_type`,`rel_id`,`file_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

    return $ready = $table;
}

/** Run a DB callback without letting a DB failure break the caller. */
function signature_backup_guard(callable $fn, $default = false)
{
    $CI    = &get_instance();
    $debug = $CI->db->db_debug ?? null;
    try {
        $CI->db->db_debug = false;
        $result = $fn($CI, signature_backup_table());
    } catch (Throwable $e) {
        log_message('error', 'Signature backup failed: ' . $e->getMessage());
        $result = $default;
    }
    $CI->db->db_debug = $debug;

    return $result;
}

function signature_backup_path($rel_type, $rel_id, $file_name)
{
    return get_upload_path_by_type($rel_type) . (int) $rel_id . '/' . basename((string) $file_name);
}

/** Store a just-written signature file; rel is derived from {folder}/{id}/. */
function signature_backup_store_file($fullPath)
{
    $map    = ['contracts' => 'contract', 'estimates' => 'estimate', 'proposals' => 'proposal'];
    $dir    = dirname($fullPath);
    $folder = basename(dirname($dir));
    if (!isset($map[$folder]) || !ctype_digit(basename($dir)) || !is_file($fullPath)) {
        return false;
    }

    return signature_backup_store($map[$folder], (int) basename($dir), basename($fullPath), (string) file_get_contents($fullPath));
}

function signature_backup_store($rel_type, $rel_id, $file_name, $data)
{
    if ($data === '') {
        return false;
    }

    return signature_backup_guard(function ($CI, $table) use ($rel_type, $rel_id, $file_name, $data) {
        $mime = 'image/png';
        if (function_exists('finfo_open') && ($finfo = finfo_open(FILEINFO_MIME_TYPE))) {
            $mime = finfo_buffer($finfo, $data) ?: $mime;
        }

        return (bool) $CI->db->replace($table, [
            'rel_type'   => $rel_type,
            'rel_id'     => (int) $rel_id,
            'file_name'  => basename((string) $file_name),
            'mime'       => substr($mime, 0, 50),
            'data'       => $data,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    });
}

function signature_backup_exists($rel_type, $rel_id, $file_name)
{
    return signature_backup_guard(function ($CI, $table) use ($rel_type, $rel_id, $file_name) {
        $CI->db->select('id');
        $CI->db->where(['rel_type' => $rel_type, 'rel_id' => (int) $rel_id, 'file_name' => basename((string) $file_name)]);
        $row = $CI->db->get($table);

        return $row && $row->row() ? true : false;
    });
}

/** Restore a missing signature file from its DB copy; returns whether the file exists. */
function ensure_signature_file($rel_type, $rel_id, $file_name)
{
    if (empty($file_name)) {
        return false;
    }
    $path = signature_backup_path($rel_type, $rel_id, $file_name);
    if (is_file($path)) {
        return true;
    }
    $row = signature_backup_guard(function ($CI, $table) use ($rel_type, $rel_id, $file_name) {
        $CI->db->where(['rel_type' => $rel_type, 'rel_id' => (int) $rel_id, 'file_name' => basename((string) $file_name)]);
        $result = $CI->db->get($table);

        return $result ? $result->row() : null;
    }, null);
    if (!$row || (string) $row->data === '') {
        return false;
    }
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        log_message('error', 'Unable to restore signature directory: ' . $dir);

        return false;
    }
    if (@file_put_contents($path, $row->data, LOCK_EX) === false) {
        log_message('error', 'Unable to restore signature file: ' . $path);

        return false;
    }
    @chmod($path, 0644);

    return is_file($path);
}

/** Delete DB copies for a document (all files when $file_name is null). */
function signature_backup_delete($rel_type, $rel_id, $file_name = null)
{
    return signature_backup_guard(function ($CI, $table) use ($rel_type, $rel_id, $file_name) {
        $CI->db->where(['rel_type' => $rel_type, 'rel_id' => (int) $rel_id]);
        if ($file_name !== null) {
            $CI->db->where('file_name', basename((string) $file_name));
        }

        return (bool) $CI->db->delete($table);
    });
}

/** Text shown when a signed document's image is gone; $neutral for customer-facing output. */
function signature_missing_text($record, $neutral = false)
{
    $name = trim(($record->acceptance_firstname ?? '') . ' ' . ($record->acceptance_lastname ?? ''));
    $date = !empty($record->acceptance_date) ? _dt($record->acceptance_date) : '';
    if ($neutral) {
        return _l('signature_on_file_text', [$name, $date]);
    }

    return _l('signature_image_missing_text', [$name, $date, $record->acceptance_ip ?? '']);
}
