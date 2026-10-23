<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: CRM Utilities & Debug Tools
Description: Quality ★★★★★.  CRM Utilities & Debug Tools gives administrators a safer control center for troubleshooting Perfex CRM. It includes debug mode controls, cache cleanup, error log reports, client portal toggle, phpMyAdmin quick link, safe database check/repair tools, and a help guide. Designed for PHP 8.5, MySQL 5.7, and Perfex CRM 3.4.x.
Version: 2.4.0
Requires at least: 3.0.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

defined('DEBUG_MODE_MODULE') or define('DEBUG_MODE_MODULE', 'debug_mode');

register_activation_hook(DEBUG_MODE_MODULE, 'debug_mode_smartchoice_activation_hook');
register_deactivation_hook(DEBUG_MODE_MODULE, 'debug_mode_smartchoice_deactivation_hook');
register_language_files(DEBUG_MODE_MODULE, [DEBUG_MODE_MODULE]);

hooks()->add_action('admin_init', 'debug_mode_smartchoice_admin_init');
hooks()->add_action('app_admin_head', 'debug_mode_smartchoice_assets');
hooks()->add_action('app_admin_head', 'debug_mode_smartchoice_capture_assets', 1);
hooks()->add_action('app_customers_head', 'debug_mode_smartchoice_client_capture_assets', 1);
hooks()->add_action('after_body_start', 'debug_mode_smartchoice_footer_notice');
hooks()->add_action('after_body_start', 'debug_mode_smartchoice_module_console');
hooks()->add_filter('before_settings_updated', 'debug_mode_smartchoice_before_settings_updated');

function debug_mode_smartchoice_activation_hook()
{
    require_once(__DIR__ . '/install.php');
    debug_mode_smartchoice_add_default_options();
}

function debug_mode_smartchoice_deactivation_hook()
{
    // Do not delete logs or settings. Only place the CRM back in production mode.
    if (debug_mode_enable_environment('production')) {
        update_option('debug_mode_enabled', '0');
    } else {
        set_alert('danger', 'Production mode could not be applied. Check index.php permissions and the environment declaration.');
    }
}

function debug_mode_smartchoice_add_default_options()
{
    $defaults = [
        'debug_mode_enabled' => '0',
        'debug_mode_client_capture' => '1',
        'debug_mode_client_visible' => '0',
        'debug_mode_log_level' => 'basic',
        'debug_mode_allowed_roles' => '',
        'debug_mode_allowed_staff' => '',
        'debug_mode_phpmyadmin_url' => 'https://s111.bluehost.com:2083/',
        'debug_mode_client_portal_enabled' => '1',
        'debug_mode_show_admin_banner' => '1',
        'debug_mode_cache_last_cleared' => '',
        'debug_mode_version' => '2.4.0',
        'debug_mode_access_control_enabled' => '0',
    ];
    $rows = get_instance()->db->select('name')->where_in('name', array_keys($defaults))
        ->get(db_prefix() . 'options')->result_array();
    $existing = array_fill_keys(array_column($rows, 'name'), true);
    foreach ($defaults as $name => $value) {
        if (!isset($existing[$name])) { add_option($name, $value); }
    }
}

function debug_mode_smartchoice_admin_init()
{
    $CI =& get_instance();
    debug_mode_smartchoice_add_default_options();

    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities('debug_mode', [
            'capabilities' => [
                'view_own' => 'View (Own)',
                'view'     => 'View (Global)',
                'create' => 'Create',
                'edit'   => 'Edit',
                'delete' => 'Delete',
            ],
        ], 'CRM Utilities & Debug Tools');
    }

    if (isset($CI->app) && method_exists($CI->app, 'add_settings_section')) {
        $CI->app->add_settings_section('debug_mode', [
            'name'     => 'CRM Utilities & Debug Tools',
            'view'     => 'debug_mode/settings',
            'position' => 65,
        ]);
    } elseif (isset($CI->app_tabs) && method_exists($CI->app_tabs, 'add_settings_tab')) {
        $CI->app_tabs->add_settings_tab('debug_mode', [
            'name'     => 'CRM Utilities & Debug Tools',
            'view'     => 'debug_mode/settings',
            'position' => 65,
        ]);
    }

    // Add a direct admin menu link when the menu object exists.
    if (isset($CI->app_menu) && method_exists($CI->app_menu, 'add_sidebar_menu_item')) {
        $CI->app_menu->add_sidebar_menu_item('debug-mode-utilities', [
            'name'     => 'CRM Utilities',
            'href'     => admin_url('debug_mode'),
            'position' => 58,
            'icon'     => 'fa fa-wrench',
        ]);
    }
}

function debug_mode_smartchoice_assets()
{
    $CI =& get_instance();
    $uri = isset($CI->uri) ? (string) $CI->uri->uri_string() : '';
    $isModulePage = strpos($uri, 'admin/debug_mode') === 0;
    $isSettingsPage = strpos($uri, 'admin/settings') === 0 && isset($_GET['group']) && $_GET['group'] === 'debug_mode';
    if (!$isModulePage && !$isSettingsPage) {
        return;
    }
    echo '<link href="' . module_dir_url('debug_mode', 'assets/css/debug_mode_smartchoice.css') . '?v=220" rel="stylesheet" type="text/css" />';
    echo '<script src="' . module_dir_url('debug_mode', 'assets/js/debug_mode_smartchoice.js') . '?v=220" defer></script>';
}

function debug_mode_smartchoice_footer_notice()
{
    if (!debug_mode_is_enabled() || get_option('debug_mode_show_admin_banner') !== '1') {
        return;
    }

    $formOpen = function_exists('form_open')
        ? form_open(admin_url('debug_mode/toggle_debug/off'), ['class' => 'sc-debug-production-form'])
        : '<form class="sc-debug-production-form" method="post" action="' . admin_url('debug_mode/toggle_debug/off') . '">';
    echo '<aside class="sc-debug-coverage-banner" role="status" aria-label="Debug Mode diagnostic coverage">'
        . '<span class="sc-debug-coverage-copy"><strong>Debug Mode active.</strong> <strong>Diagnostic coverage:</strong> PHP and module errors share the CRM log. Debug Mode adds notices, deprecations and browser errors on administrator pages as they are visited. The report includes the last 7 days, up to 500 events and the last 1 MiB per log source. Use <a href="' . admin_url('debug_mode/automatic_checks') . '">Automatic Checks</a> to scan module PHP and probe reviewed pages, including unvisited routes. Authenticated client-portal browser capture is separately switchable there.</span>'
        . $formOpen . '<button type="submit">Switch to Production Mode</button></form>'
        . '</aside>';
}

/** Return a source change only for a single supported, executable ENVIRONMENT declaration. */
function debug_mode_environment_source($content, $desired_env)
{
    if (!in_array($desired_env, ['development', 'production'], true)) {
        return false;
    }

    $tokens = [];
    $offset = 0;
    foreach (token_get_all($content) as $token) {
        $text = is_array($token) ? $token[1] : $token;
        if (!is_array($token) || !in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            $tokens[] = ['text' => $text, 'offset' => $offset, 'type' => is_array($token) ? $token[0] : null];
        }
        $offset += strlen($text);
    }

    $changes = [];
    foreach ($tokens as $i => $token) {
        if ($token['type'] !== T_STRING || strtolower($token['text']) !== 'define'
            || ($tokens[$i + 1]['text'] ?? '') !== '('
            || !in_array($tokens[$i + 2]['text'] ?? '', ["'ENVIRONMENT'", '"ENVIRONMENT"'], true)
            || ($tokens[$i + 3]['text'] ?? '') !== ',') {
            continue;
        }
        // Never edit a method call or a function declaration with the same name.
        if (in_array($tokens[$i - 1]['text'] ?? '', ['->', '?->', '::', 'function'], true)) {
            continue;
        }
        $start = $tokens[$i + 4]['offset'] ?? strlen($content);
        for ($j = $i + 4; isset($tokens[$j]); $j++) {
            if ($tokens[$j]['text'] === ')') {
                break;
            }
        }
        if (!isset($tokens[$j]) || ($tokens[$j + 1]['text'] ?? '') !== ';') {
            return false;
        }
        $end = $tokens[$j]['offset'];
        $expression = trim(substr($content, $start, $end - $start));
        // Support the current CI_ENV fallback and the older literal environment declaration.
        if (!preg_match('~^(?:\$_SERVER\[\s*([\'"])CI_ENV\1\s*\]\s*\?\?\s*)?([\'"])(production|development|testing)\2$~', $expression)) {
            return false;
        }
        $changes[] = [$start, $end];
    }
    if (count($changes) !== 1) {
        return false;
    }
    [$start, $end] = $changes[0];
    return substr($content, 0, $start) . "'" . $desired_env . "'" . substr($content, $end);
}

function debug_mode_enable_environment($desired_env)
{
    $indexFile = FCPATH . 'index.php';
    if (!is_file($indexFile) || !is_writable($indexFile)) {
        return false;
    }
    $handle = @fopen($indexFile, 'r');
    if (!$handle) {
        return false;
    }
    $temporaryFile = false;
    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }
        $content = stream_get_contents($handle);
        $newContent = $content === false ? false : debug_mode_environment_source($content, $desired_env);
        if ($newContent === false) {
            return false;
        }
        // Another switch may have replaced the file while this request waited for its lock.
        if (@file_get_contents($indexFile) !== $content) {
            return false;
        }
        if ($newContent === $content) {
            return true;
        }
        // Replace atomically so a web request never reads a partially written PHP entry point.
        $temporaryFile = @tempnam(dirname($indexFile), '.debug-env-');
        if (!$temporaryFile || realpath(dirname($temporaryFile)) !== realpath(dirname($indexFile))
            || @file_put_contents($temporaryFile, $newContent) !== strlen($newContent)
            || !@chmod($temporaryFile, fileperms($indexFile) & 0777)
            || !@rename($temporaryFile, $indexFile)) {
            return false;
        }
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($indexFile, true);
        }
        return true;
    } finally {
        if ($temporaryFile && is_file($temporaryFile)) {
            @unlink($temporaryFile);
        }
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function debug_mode_is_enabled()
{
    // A stale database preference must not misrepresent the environment of this request.
    return defined('ENVIRONMENT') && ENVIRONMENT === 'development';
}

function debug_mode_smartchoice_before_settings_updated($data)
{
    if (!isset($data['settings']) || !array_key_exists('debug_mode_enabled', $data['settings'])) {
        return $data;
    }
    $value = $data['settings']['debug_mode_enabled'];
    if (!is_admin() || !in_array($value, ['0', '1'], true)) {
        unset($data['settings']['debug_mode_enabled']);
        set_alert('danger', 'Only an administrator can change Debug Mode using a valid on/off setting.');
    } elseif (!debug_mode_enable_environment($value === '1' ? 'development' : 'production')) {
        unset($data['settings']['debug_mode_enabled']);
        set_alert('danger', 'Debug Mode was not changed. Check index.php permissions and the environment declaration.');
    }
    return $data;
}

function debug_mode_user_allowed()
{
    if (function_exists('is_admin') && is_admin()) {
        return true;
    }

    if (function_exists('has_permission') && has_permission('debug_mode', '', 'view')) {
        return true;
    }

    $allowedStaff = get_option('debug_mode_allowed_staff');
    if (function_exists('get_staff_user_id') && $allowedStaff) {
        $ids = array_map('trim', explode(',', (string) $allowedStaff));
        return in_array((string) get_staff_user_id(), $ids, true);
    }

    return false;
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

/** Resolve only a real installed module from the current administrator route. */
function debug_mode_current_admin_module()
{
    $CI =& get_instance();
    $module = isset($CI->router) && method_exists($CI->router, 'fetch_module')
        ? (string) $CI->router->fetch_module()
        : '';
    if ($module === '' && isset($CI->uri)) {
        $uri = (string) $CI->uri->uri_string();
        if (preg_match('~^admin/([a-zA-Z0-9_-]+)(?:/|$)~', $uri, $match)) { $module = $match[1]; }
    }
    if ($module === '' || !preg_match('/^[a-zA-Z0-9_-]+$/D', $module)
        || !isset($CI->app_modules) || !$CI->app_modules->get($module)) {
        return null;
    }
    return $module;
}

/** Resolve a stable, non-record-specific scope for every administrator page. */
function debug_mode_admin_scope_from_uri($uri)
{
    $path = parse_url('/' . ltrim($uri, '/'), PHP_URL_PATH) ?: '/admin';
    $parts = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));
    if (($parts[0] ?? '') !== 'admin') { return null; }
    $parts = array_slice($parts, 0, 3);
    foreach ($parts as $part) {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $part)) { return null; }
    }
    $key = $parts[1] ?? 'dashboard';
    return ['key' => $key, 'route' => '/' . implode('/', $parts)];
}

function debug_mode_current_admin_scope()
{
    $CI =& get_instance();
    return debug_mode_admin_scope_from_uri(isset($CI->uri) ? (string) $CI->uri->uri_string() : '');
}

function debug_mode_smartchoice_capture_assets()
{
    if (!debug_mode_is_enabled() || !is_admin()) { return; }
    $CI =& get_instance();
    $token = $CI->session->userdata('debug_mode_capture_token');
    if (!is_string($token) || strlen($token) !== 64) {
        $token = bin2hex(random_bytes(32));
        $CI->session->set_userdata('debug_mode_capture_token', $token);
    }
    echo '<script src="' . module_dir_url('debug_mode', 'assets/js/debug_mode_capture.js') . '?v=231" data-token="' . html_escape($token) . '" data-endpoint="' . html_escape(admin_url('debug_mode/browser_error')) . '"></script>';
    echo '<link href="' . module_dir_url('debug_mode', 'assets/css/debug_mode_console.css') . '?v=240" rel="stylesheet" type="text/css">';
    $scope = debug_mode_current_admin_scope();
    if ($scope) {
        $requestStarted = isset($_SERVER['REQUEST_TIME']) ? (int) $_SERVER['REQUEST_TIME'] : time();
        echo '<script src="' . module_dir_url('debug_mode', 'assets/js/debug_mode_console.js') . '?v=240" defer'
            . ' data-scope="' . html_escape($scope['key']) . '" data-route="' . html_escape($scope['route']) . '" data-since="' . ($requestStarted - 5) . '"'
            . ' data-token="' . html_escape($token) . '" data-endpoint="' . html_escape(admin_url('debug_mode/module_console')) . '"></script>';
    }
}

/** Collision-inspired drawer for the current administrator page; output remains escaped by the client renderer. */
function debug_mode_smartchoice_module_console()
{
    if (!debug_mode_is_enabled() || !is_admin()) { return; }
    $scope = debug_mode_current_admin_scope();
    if (!$scope) { return; }
    ?>
    <section id="scDebugConsole" aria-label="<?php echo html_escape($scope['key']); ?> page diagnostics">
        <div class="sc-debug-console-bar">
            <span class="sc-debug-console-lights" aria-hidden="true"><span></span><span></span><span></span></span>
            <span class="sc-debug-console-title">Debug · <?php echo html_escape($scope['key']); ?></span>
            <span data-console-status role="status" aria-live="polite">Checking page errors…</span>
            <span class="sc-debug-console-spacer"></span>
            <span class="sc-debug-console-count" data-console-count hidden>0</span>
            <button type="button" data-console-refresh><i class="fa fa-refresh" aria-hidden="true"></i> Refresh</button>
            <button type="button" data-console-clear>Clear view</button>
            <button type="button" data-console-toggle aria-expanded="false" aria-controls="scDebugConsoleBody"><i class="fa fa-chevron-up" aria-hidden="true"></i> <span data-console-toggle-label>Open</span></button>
        </div>
        <div id="scDebugConsoleBody" class="sc-debug-console-body" data-console-body hidden>
            <div class="sc-debug-console-events" data-console-events></div>
        </div>
    </section>
    <?php
}

/** Client capture is authenticated, separately switchable and tied to this contact session. */
function debug_mode_smartchoice_client_capture_assets()
{
    if (!debug_mode_is_enabled() || get_option('debug_mode_client_capture') !== '1' || !is_client_logged_in() || !get_contact_user_id()) { return; }
    $ci = &get_instance();
    $token = $ci->session->userdata('debug_mode_capture_token');
    if (!is_string($token) || strlen($token) !== 64) {
        $token = bin2hex(random_bytes(32));
        $ci->session->set_userdata('debug_mode_capture_token', $token);
    }
    echo '<script src="' . module_dir_url('debug_mode', 'assets/js/debug_mode_capture.js') . '?v=230" data-portal="client" data-token="' . html_escape($token) . '" data-endpoint="' . html_escape(site_url('debug_mode/client_diagnostics/collect')) . '"></script>';
}
