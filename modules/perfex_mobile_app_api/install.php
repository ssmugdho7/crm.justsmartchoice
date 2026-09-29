<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('perfex_mobile_api_add_csrf_exception')) {
    function perfex_mobile_api_add_csrf_exception(): void
    {
        $filePath = APPPATH . 'config/config.php';
        $markerStart = '/* Smart Choice Perfex Mobile API CSRF exception - start */';
        $markerEnd = '/* Smart Choice Perfex Mobile API CSRF exception - end */';

        if (!is_file($filePath) || !is_writable($filePath)) {
            return;
        }

        $contents = file_get_contents($filePath);
        if ($contents === false || strpos($contents, $markerStart) !== false) {
            return;
        }

        $code = PHP_EOL . $markerStart . PHP_EOL
            . 'if (isset($config["csrf_protection"], $_SERVER["REQUEST_URI"])'
            . ' && $config["csrf_protection"] === true'
            . ' && strpos((string) $_SERVER["REQUEST_URI"], "perfex_mobile_app_api") !== false) {' . PHP_EOL
            . '    $config["csrf_protection"] = false;' . PHP_EOL
            . '}' . PHP_EOL
            . $markerEnd . PHP_EOL;

        file_put_contents($filePath, $code, FILE_APPEND | LOCK_EX);
    }
}

$CI = &get_instance();

if (!option_exists(PERFEX_MOBILE_APP_API . '_enabled')) {
    add_option(PERFEX_MOBILE_APP_API . '_enabled', 1);
}

if (!option_exists(PERFEX_MOBILE_APP_API . '_require_auth_key')) {
    add_option(PERFEX_MOBILE_APP_API . '_require_auth_key', 0);
}

if (!option_exists(PERFEX_MOBILE_APP_API . '_log_responses')) {
    add_option(PERFEX_MOBILE_APP_API . '_log_responses', 0);
}

$CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . 'api_logs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `url` VARCHAR(500) NOT NULL,
    `data` LONGTEXT NULL,
    `response` LONGTEXT NULL,
    `origin_from` VARCHAR(100) NOT NULL DEFAULT "app",
    `headers` LONGTEXT NULL,
    `ip_address` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

perfex_mobile_api_add_csrf_exception();
