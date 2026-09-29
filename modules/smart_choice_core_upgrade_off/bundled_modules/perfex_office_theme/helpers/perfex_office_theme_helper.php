<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (get_option('smart_choice_theme_admin_enabled') !== '0') {
    hooks()->add_action('app_admin_head', 'perfex_office_theme_head_component');
}
if (get_option('smart_choice_theme_admin_enabled') !== '0') {
    hooks()->add_action('app_admin_footer', 'perfex_office_theme_footer_js__component');
}
hooks()->add_action('admin_init', 'office_theme_settings_tab');


// Check if customers theme is enabled
if (get_option('perfex_office_theme_customers') == '1') {
    hooks()->add_action('app_admin_authentication_head', 'perfex_office_theme_customer_head');
    hooks()->add_action('app_external_form_head', 'app_client_office_head_custom_includes');
    hooks()->add_action('app_customers_head', 'perfex_office_theme_includes');
    hooks()->add_action('app_customers_footer', 'perfex_office_theme_customers_footer_js__component');
}


/**
 * [office_theme_settings_tab net menu item in setup->settings]
 *
 * @return void
 */
function office_theme_settings_tab()
{
    $CI = &get_instance();

    // Perfex 3.4.x compatible direct settings sections.
    // This avoids the 404 caused by nested child sections on some installs.
    $CI->app->add_settings_section('admin-theme-settings', [
        'name'     => _l('perfex_office_theme_settings_first'),
        'view'     => 'perfex_office_theme/perfex_office_theme_settings',
        'position' => 48,
    ]);

    $CI->app->add_settings_section('admin-theme-health', [
        'name'     => 'Office Theme Health / Verification',
        'view'     => 'perfex_office_theme/perfex_office_theme_health',
        'position' => 49,
    ]);
}

/**
 * Theme customers login includes
 *
 * @return stylesheet / script
 */
function perfex_office_theme_customer_head()
{
    echo '<link href="' . base_url('modules/perfex_office_theme/assets/css/staff_login_styles.css') . '"  rel="stylesheet" type="text/css" >';
    echo '<script src="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/js/sign_in.js') . '"></script>';
}


/**
 * Theme clients head includes
 *
 * @return stylesheet
 */
function perfex_office_theme_includes()
{
    echo '<link href="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/css/clients/clients.css') . '"  rel="stylesheet" type="text/css" >';
    echo '<link href="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/css/clients/common.css') . '"  rel="stylesheet" type="text/css" >';
    perfex_office_theme_dynamic_colors();
    echo '<script src="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/js/third-party/nanobar.js') . '"></script>';
}

/**
 * Injects theme CSS
 *
 * @return null
 */
function perfex_office_theme_head_component()
{
    echo '<link href="' . base_url('modules/perfex_office_theme/assets/css/theme_styles.css') . '"  rel="stylesheet" type="text/css" >';
    perfex_office_theme_dynamic_colors();
    echo '<script src="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/js/third-party/nanobar.js') . '"></script>';
    echo '<script src="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/js/third-party/waves076.min.js') . '"></script>';
}

function perfex_office_theme_safe_hex($value, $fallback)
{
    $value = is_string($value) ? trim($value) : '';
    return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $fallback;
}

function perfex_office_theme_safe_number($value, $fallback, $min = 10, $max = 24)
{
    $value = is_numeric($value) ? (int) $value : (int) $fallback;
    return max($min, min($max, $value));
}

function perfex_office_theme_safe_css_text_shadow($value, $fallback)
{
    $value = is_string($value) ? trim($value) : '';
    if ($value === '') {
        return $fallback;
    }
    // Keep this setting safe: no brackets, semicolons, or script-like content.
    if (preg_match('/[;{}<>]/', $value)) {
        return $fallback;
    }
    return $value;
}

function perfex_office_theme_dynamic_colors()
{
    $primary = perfex_office_theme_safe_hex(get_option('smart_choice_theme_primary_color'), '#0b5cab');
    $light   = perfex_office_theme_safe_hex(get_option('smart_choice_theme_light_blue_color'), '#2ea8ff');
    $orange  = perfex_office_theme_safe_hex(get_option('smart_choice_theme_orange_color'), '#f7941d');
    $deep_orange = perfex_office_theme_safe_hex(get_option('smart_choice_theme_deep_orange_color'), '#d96f00');
    $green   = perfex_office_theme_safe_hex(get_option('smart_choice_theme_green_color'), '#169179');
    $soft    = perfex_office_theme_safe_hex(get_option('smart_choice_theme_soft_background'), '#f8fafc');
    $trim    = perfex_office_theme_safe_hex(get_option('smart_choice_theme_trim_color'), '#0057b8');
    $grad1   = perfex_office_theme_safe_hex(get_option('smart_choice_theme_gradient_start'), '#0057b8');
    $grad2   = perfex_office_theme_safe_hex(get_option('smart_choice_theme_gradient_end'), '#169179');

    $admin_bg       = perfex_office_theme_safe_hex(get_option('smart_choice_theme_admin_menu_bg'), '#ffffff');
    $admin_text     = perfex_office_theme_safe_hex(get_option('smart_choice_theme_admin_menu_text'), '#263238');
    $admin_hover_bg = perfex_office_theme_safe_hex(get_option('smart_choice_theme_admin_menu_hover_bg'), '#eaf4ff');
    $admin_hover_tx = perfex_office_theme_safe_hex(get_option('smart_choice_theme_admin_menu_hover_text'), '#0b5cab');
    $admin_icon     = perfex_office_theme_safe_hex(get_option('smart_choice_theme_admin_icon_color'), '#0057b8');
    $admin_size     = perfex_office_theme_safe_number(get_option('smart_choice_theme_admin_menu_font_size'), 14, 11, 22);
    $base_size      = perfex_office_theme_safe_number(get_option('smart_choice_theme_base_font_size'), 14, 11, 22);
    $admin_radius   = perfex_office_theme_safe_number(get_option('smart_choice_theme_admin_hover_radius'), 8, 2, 22);
    $card_radius    = perfex_office_theme_safe_number(get_option('smart_choice_theme_card_radius'), 12, 2, 28);
    $button_radius  = perfex_office_theme_safe_number(get_option('smart_choice_theme_button_radius'), 8, 2, 24);
    $admin_shadow_on = get_option('smart_choice_theme_admin_menu_shadow') === '1';
    $admin_shadow   = perfex_office_theme_safe_css_text_shadow(get_option('smart_choice_theme_admin_text_shadow'), 'none');

    $client_bg       = perfex_office_theme_safe_hex(get_option('smart_choice_theme_client_menu_bg'), '#0057b8');
    $client_text     = perfex_office_theme_safe_hex(get_option('smart_choice_theme_client_menu_text'), '#ffffff');
    $client_hover_bg = perfex_office_theme_safe_hex(get_option('smart_choice_theme_client_menu_hover_bg'), '#eaf4ff');
    $client_hover_tx = perfex_office_theme_safe_hex(get_option('smart_choice_theme_client_menu_hover_text'), '#0057b8');
    $client_icon     = perfex_office_theme_safe_hex(get_option('smart_choice_theme_client_icon_color'), '#0057b8');
    $client_size     = perfex_office_theme_safe_number(get_option('smart_choice_theme_client_menu_font_size'), 14, 11, 22);
    $client_shadow_on = get_option('smart_choice_theme_client_menu_shadow') === '1';
    $client_shadow   = perfex_office_theme_safe_css_text_shadow(get_option('smart_choice_theme_client_text_shadow'), 'none');

    $trim_primary = perfex_office_theme_safe_hex(get_option('smart_choice_theme_trim_primary'), '#0057b8');
    $trim_secondary = perfex_office_theme_safe_hex(get_option('smart_choice_theme_trim_secondary'), '#0b5cab');
    $top_admin = perfex_office_theme_safe_hex(get_option('smart_choice_theme_top_bar_admin'), '#0b5cab');
    $sidebar_bar = perfex_office_theme_safe_hex(get_option('smart_choice_theme_sidebar_bar'), '#123b63');
    $component_bar = perfex_office_theme_safe_hex(get_option('smart_choice_theme_component_bar'), '#0057b8');

    $admin_shadow_css = $admin_shadow_on ? $admin_shadow : 'none';
    $client_shadow_css = $client_shadow_on ? $client_shadow : 'none';

    echo '<style id="smart-choice-office-theme-dynamic">\n';
    echo ':root{--sc-blue:' . $primary . ';--sc-light-blue:' . $light . ';--sc-orange:' . $orange . ';--sc-deep-orange:' . $deep_orange . ';--sc-green:' . $green . ';--sc-soft:' . $soft . ';--sc-trim:' . $trim . ';}\n';
    echo 'html,body,form,fieldset,table,tr,td,img,span.menu-text,span.sub-menu-text,a,input,button,select,textarea,option,div{font-family:"Segoe UI",Roboto,Arial,sans-serif!important;font-size:' . $base_size . 'px;}\n';
    echo 'body.admin #header, body.admin .navbar-default{background:' . $top_admin . '!important;border-color:' . $top_admin . '!important;}\n';
    echo 'body.admin #side-menu, body.admin .admin-sidebar{background:' . $admin_bg . '!important;border-top:4px solid ' . $sidebar_bar . '!important;}\n';
    echo 'body.admin #side-menu li a, body.admin #setup-menu li a{font-size:' . $admin_size . 'px!important;color:' . $admin_text . '!important;text-shadow:' . $admin_shadow_css . '!important;}\n';
    echo 'body.admin #side-menu li a i, body.admin #setup-menu li a i, body.admin .menu-icon{color:' . $admin_icon . '!important;text-shadow:' . $admin_shadow_css . '!important;}\n';
    echo 'body.admin #side-menu li a:hover, body.admin #setup-menu li a:hover, body.admin #side-menu li.active>a, body.admin #setup-menu li.active>a{background:' . $admin_hover_bg . '!important;color:' . $admin_hover_tx . '!important;border-radius:' . $admin_radius . 'px!important;box-shadow:0 8px 20px rgba(0,87,184,.12)!important;}\n';
    echo 'body.admin #side-menu li a:hover i, body.admin #setup-menu li a:hover i, body.admin #side-menu li.active>a i, body.admin #setup-menu li.active>a i{color:' . $admin_hover_tx . '!important;}\n';
    echo 'body.admin .panel_s .panel-body, body.admin .panel_s, body.admin .modal-content{border-radius:' . $card_radius . 'px!important;}\n';
    echo 'body.admin .panel_s>.panel-heading, body.admin .modal-header{border-top:3px solid ' . $component_bar . '!important;}\n';
    echo 'body .btn-primary, body .btn-info{background:linear-gradient(135deg,' . $grad1 . ',' . $grad2 . ')!important;border-color:' . $trim_primary . '!important;border-radius:' . $button_radius . 'px!important;box-shadow:0 8px 18px rgba(0,87,184,.14)!important;}\n';
    echo 'body .btn-success{background:' . $green . '!important;border-color:' . $green . '!important;border-radius:' . $button_radius . 'px!important;}\n';
    echo 'body .progress-bar, body .label-info, body .label-primary{background:' . $component_bar . '!important;}\n';
    echo 'body a, body .text-info{color:' . $primary . ';} body a:hover{color:' . $green . ';}\n';
    echo 'body.customers .navbar, body.customers .navbar-default, body.customers .customers-nav{background:' . $client_bg . '!important;border-color:' . $client_bg . '!important;}\n';
    echo 'body.customers .navbar a, body.customers .navbar-nav>li>a, body.customers .customers-nav a{font-size:' . $client_size . 'px!important;color:' . $client_text . '!important;text-shadow:' . $client_shadow_css . '!important;}\n';
    echo 'body.customers .navbar a i, body.customers .customers-nav a i{color:' . $client_icon . '!important;text-shadow:' . $client_shadow_css . '!important;}\n';
    echo 'body.customers .navbar a:hover, body.customers .navbar-nav>li>a:hover, body.customers .customers-nav a:hover{background:' . $client_hover_bg . '!important;color:' . $client_hover_tx . '!important;border-radius:' . $admin_radius . 'px!important;}\n';
    echo 'body.customers .navbar a:hover i, body.customers .customers-nav a:hover i{color:' . $client_hover_tx . '!important;}\n';
    echo 'body .table thead th{border-bottom:2px solid ' . $trim . '!important;} body .panel_s{box-shadow:0 10px 26px rgba(15,23,42,.06)!important;}\n';
    echo 'body.admin #side-menu li,body.admin #setup-menu li{margin:2px 8px!important;}\n';
    echo 'body.admin #side-menu li a,body.admin #setup-menu li a{display:flex!important;align-items:center!important;gap:10px!important;min-height:38px!important;padding:9px 12px!important;line-height:1.25!important;border-radius:' . $admin_radius . 'px!important;white-space:normal!important;overflow:visible!important;}\n';
    echo 'body.admin #side-menu li a i,body.admin #setup-menu li a i{width:19px!important;min-width:19px!important;margin-right:0!important;float:none!important;font-size:15px!important;text-align:center!important;color:' . $admin_icon . '!important;}\n';
    echo 'body.admin #side-menu .collapse,body.admin #setup-menu .collapse,body.admin #side-menu .nav-second-level,body.admin #setup-menu .nav-second-level{position:relative!important;clear:both!important;margin:4px 0 8px 0!important;padding:5px 0 6px 12px!important;background:#f8fafc!important;border-left:3px solid #123b63!important;border-radius:0 12px 12px 0!important;max-height:none!important;overflow:visible!important;}\n';
    echo 'body.admin #wrapper .btn-bottom-toolbar,body.admin .btn-bottom-toolbar{left:auto!important;right:24px!important;bottom:18px!important;width:auto!important;min-width:0!important;max-width:calc(100vw - 300px)!important;padding:8px 10px!important;border-radius:' . ($button_radius + 4) . 'px!important;background:rgba(255,255,255,.96)!important;box-shadow:0 8px 22px rgba(15,23,42,.12)!important;display:flex!important;align-items:center!important;justify-content:flex-end!important;gap:8px!important;}\n';
    echo 'body.admin #wrapper .btn-bottom-toolbar .btn,body.admin .btn-bottom-toolbar .btn{padding:8px 14px!important;border-radius:' . $button_radius . 'px!important;min-height:34px!important;line-height:1.1!important;}\n';
    echo 'body.customers .navbar,body.customers .navbar-default,body.customers .customers-nav{background:linear-gradient(135deg,' . $client_bg . ',' . $green . ')!important;box-shadow:0 8px 28px rgba(22,145,121,.18)!important;}\n';
    echo 'body.customers .navbar-default .navbar-nav>li>a,body.customers .navbar a,body.customers .customers-nav a{display:flex!important;align-items:center!important;gap:8px!important;margin:6px 3px!important;padding:10px 13px!important;border-radius:10px!important;line-height:1.25!important;white-space:normal!important;}\n';
    echo 'body.customers .dropdown-menu{border:0!important;border-radius:14px!important;box-shadow:0 14px 36px rgba(15,23,42,.16)!important;padding:8px!important;} body.customers .dropdown-menu>li>a{color:#263238!important;text-shadow:none!important;}\n';
    echo '</style>';
}

function app_client_office_head_custom_includes()
{
    echo '<link href="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/css/clients/clients.css') . '"  rel="stylesheet" type="text/css" >';
    echo '<link href="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/css/clients/common.css') . '"  rel="stylesheet" type="text/css" >';
    perfex_office_theme_dynamic_colors();
    echo '<script src="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/js/third-party/waves076.min.js') . '"></script>';
}

/**
 * Injects staff theme js components in footer
 *
 * @return null
 */
function perfex_office_theme_footer_js__component()
{
    echo '<script src="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/js/main.js') . '"></script>';
}


/**
 * Injects customer theme js components in footer
 *
 * @return null
 */
function perfex_office_theme_customers_footer_js__component()
{
    echo '<script src="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/js/third-party/waves076.min.js') . '"></script>';
    echo '<script src="' . module_dir_url(PERFEX_OFFICE_THEME, 'assets/js/clients.js') . '"></script>';
}


/**
 * Changes sidebar menu icons
 *
 * @return array
 */
hooks()->add_filter('sidebar_menu_items', 'perfex_office_change_sidebar_icons');

function perfex_office_change_sidebar_icons($items)
{
    foreach ($items as $id => $item) {
        if ($id === 'dashboard') {
            $items[$id]['icon'] = 'fa fa-desktop';
        }
        if ($id === 'customers') {
            $items[$id]['icon'] = 'fa fa-id-card';
        }
        if ($id === 'sales') {
            $items[$id]['icon'] = 'fa fa-university';
        }
        if ($id === 'subscriptions') {
            $items[$id]['icon'] = 'fa-brands fa-cc-mastercard';
        }
        if ($id === 'contracts') {
            $items[$id]['icon'] = 'fa fa-clipboard';
        }
        if ($id === 'projects') {
            $items[$id]['icon'] = 'fa fa-indent';
        }
        if ($id === 'tasks') {
            $items[$id]['icon'] = 'fa fa-th';
        }
        if ($id === 'support') {
            $items[$id]['icon'] = 'fa fa-users';
        }
        if ($id === 'reports') {
            $items[$id]['icon'] = 'fa fa-pie-chart';
        }
        if ($id === 'knowledge-base') {
            $items[$id]['icon'] = 'fa fa-folder-open';
        }
        if ($id === 'leads') {
            $items[$id]['icon'] = 'fa fa-user-secret';
        }
    }
    return $items;
}

/**
 * Changes task filters color and background
 *
 * @return array
 */
hooks()->add_action('app_init', 'initialize_theme_task_filters');

/**
 * Changes system favorite colors
 *
 * @return array
 */
hooks()->add_filter('system_favourite_colors', 'system_colors_theme_hook');

function system_colors_theme_hook($colors)
{
    foreach ($colors as $key => $color) {
        if ($color == '#ff2d42') {
            $colors[$key] = '#ff0019';
        }
        if ($color == '#28B8DA') {
            $colors[$key] = '#5d78ff';
        }
        if ($color == '#03a9f4') {
            $colors[$key] = '#2a44c5';
        }
        if ($color == '#757575') {
            $colors[$key] = '#595959';
        }
        if ($color == '#8e24aa') {
            $colors[$key] = '#0057b8';
        }
        if ($color == '#d81b60') {
            $colors[$key] = '#169179';
        }
        if ($color == '#0288d1') {
            $colors[$key] = '#3333ff';
        }
        if ($color == '#7cb342') {
            $colors[$key] = '#2eb82e';
        }
        if ($color == '#fb8c00') {
            $colors[$key] = '#e67e00';
        }
        if ($color == '#84C529') {
            $colors[$key] = '#71a923';
        }
        if ($color == '#fb3b3b') {
            $colors[$key] = '#fa1e1e';
        }
    }

    return $colors;
}

function initialize_theme_task_filters()
{
    hooks()->add_filter('before_get_task_statuses', 'dashboard_label_task_colors');
}


function dashboard_label_task_colors($statuses)
{
    $CI = &get_instance();

    if (!class_exists('tasks_model', false)) {
        $CI->load->model('tasks_model');
    }

    foreach ($statuses as $key => $status) {
        $id = $status['id'];
        switch ($id) {
            case $id == Tasks_model::STATUS_NOT_STARTED:
                $statuses[$key]['color'] = '#ffb822';
                break;
            case $id == Tasks_model::STATUS_AWAITING_FEEDBACK:
                $statuses[$key]['color'] = '#0abb87';
                break;
            case $id == Tasks_model::STATUS_IN_PROGRESS:
                $statuses[$key]['color'] = '#5d78ff';
                break;
            case $id == Tasks_model::STATUS_COMPLETE:
                $statuses[$key]['color'] = '#84c529';
                break;
        }
    }
    return $statuses;
}

/**
 * Changes project filters color
 *
 * @return array
 */
hooks()->add_action('app_init', 'initialize_theme_project_filters');

function initialize_theme_project_filters()
{
    hooks()->add_filter('before_get_project_statuses', 'dashboard_label_project_colors');
}

function dashboard_label_project_colors($statuses)
{
    foreach ($statuses as $key => $status) {
        $id = $status['id'];

        switch ($id) {
            case $id == 3:
                $statuses[$key]['color'] = '#ffb822';
                break;
            case $id == 4:
                $statuses[$key]['color'] = '#0abb87';
                break;
            case $id == 1:
                $statuses[$key]['color'] = '#777777';
                break;
            case $id == 2:
                $statuses[$key]['color'] = '#5d78ff';
                break;
            case $id == 5:
                $statuses[$key]['color'] = '#fa1e1e';
                break;
        }
    }
    return $statuses;
}

/**
 * Helper function to convert hex colors #333333 to rgba() colors
 *
 * @return array
 */
function hexToRgb($hex, $alpha = false)
{
    $hex = str_replace('#', '', $hex);
    $length = strlen($hex);
    $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
    $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
    $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
    if ($alpha) {
        $rgb['a'] = $alpha;
    }
    return $rgb;
}
