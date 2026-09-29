<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->database();

require_once __DIR__ . '/helpers/solar_pro_helper.php';
solar_pro_install_schema($CI);
solar_pro_install_options();
solar_pro_seed_defaults($CI);

// Best-effort cache clearing without removing safety files.
$cachePath = APPPATH . 'cache/';
if (is_dir($cachePath) && is_writable($cachePath)) {
    foreach (glob($cachePath . '*') ?: [] as $file) {
        if (is_file($file) && !in_array(basename($file), ['index.html', '.htaccess'], true)) {
            @unlink($file);
        }
    }
}
if (function_exists('opcache_reset')) {
    @opcache_reset();
}
log_activity('Solar Pro v' . SOLAR_PRO_VERSION . ' activated');
