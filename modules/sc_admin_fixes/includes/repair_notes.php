<?php
defined('BASEPATH') or exit('No direct script access allowed');
hooks()->add_action('app_admin_footer', function () {
    $CI = &get_instance();
    if ($CI->router->fetch_class() !== 'development' || $CI->router->fetch_method() !== 'index') { return; }
    foreach (['css/repair-notes.css' => 'css', 'js/repair-notes.js' => 'js'] as $asset => $kind) {
        $url = module_dir_url('sc_admin_fixes', 'assets/' . $asset) . '?v=' . filemtime(dirname(__DIR__) . '/assets/' . $asset);
        echo $kind === 'css' ? '<link rel="stylesheet" href="' . html_escape($url) . '">' : '<script src="' . html_escape($url) . '"></script>';
    }
});
