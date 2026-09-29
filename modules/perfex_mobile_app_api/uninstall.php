<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('perfex_mobile_api_remove_csrf_exception')) {
    function perfex_mobile_api_remove_csrf_exception(): void
    {
        $filePath = APPPATH . 'config/config.php';
        $markerStart = '/* Smart Choice Perfex Mobile API CSRF exception - start */';
        $markerEnd = '/* Smart Choice Perfex Mobile API CSRF exception - end */';

        if (!is_file($filePath) || !is_writable($filePath)) {
            return;
        }

        $contents = file_get_contents($filePath);
        if ($contents === false) {
            return;
        }

        $pattern = '/' . preg_quote($markerStart, '/') . '.*?' . preg_quote($markerEnd, '/') . '\s*/s';
        $updated = preg_replace($pattern, '', $contents);

        if ($updated !== null && $updated !== $contents) {
            file_put_contents($filePath, $updated, LOCK_EX);
        }
    }
}

$CI = &get_instance();

delete_option(PERFEX_MOBILE_APP_API . '_enabled');
delete_option(PERFEX_MOBILE_APP_API . '_require_auth_key');
delete_option(PERFEX_MOBILE_APP_API . '_log_responses');

if (table_exists(db_prefix() . 'api_logs')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'api_logs`');
}

perfex_mobile_api_remove_csrf_exception();
