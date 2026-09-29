<?php

defined('BASEPATH') or exit('No direct script access allowed');

function smart_module_manager_title_case($text)
{
    $text = str_replace(['_', '-'], ' ', (string) $text);
    $text = preg_replace('/\s+/', ' ', trim($text));
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');
    }
    return ucwords(strtolower($text));
}

function smart_module_manager_is_safe_slug($slug)
{
    return is_string($slug) && preg_match('/^[a-zA-Z0-9_\-]+$/', $slug);
}

function smart_module_manager_log($moduleName, $action, $message = '', $filePath = '')
{
    $CI = &get_instance();
    if (!$CI->db->table_exists(db_prefix() . 'smart_module_manager_logs')) {
        return false;
    }
    $CI->db->insert(db_prefix() . 'smart_module_manager_logs', [
        'staff_id'    => function_exists('get_staff_user_id') ? get_staff_user_id() : null,
        'module_name' => (string) $moduleName,
        'action'      => (string) $action,
        'message'     => (string) $message,
        'file_path'   => (string) $filePath,
        'created_at'  => date('Y-m-d H:i:s'),
    ]);
    return true;
}

function smart_module_manager_delete_dir_safe($dir)
{
    if (!is_dir($dir) || is_link($dir)) {
        return false;
    }

    $real = realpath($dir);
    $modulesReal = realpath(FCPATH . 'modules');
    if (!$real || !$modulesReal || strpos($real, $modulesReal) !== 0) {
        return false;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir() && !$item->isLink()) {
            @rmdir($item->getPathname());
        } else {
            @chmod($item->getPathname(), 0644);
            @unlink($item->getPathname());
        }
    }

    return @rmdir($real);
}
