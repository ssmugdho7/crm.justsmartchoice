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
        'name'     => 'Office Theme',
        'view'     => 'perfex_office_theme/perfex_office_theme_settings',
        'position' => 48,
    ]);

    $CI->app->add_settings_section('admin-theme-health', [
        'name'     => 'Office Theme Health',
        'view'     => 'perfex_office_theme/perfex_office_theme_health',
        'position' => 49,
    ]);


    if (function_exists('add_setup_menu_item')) {
        add_setup_menu_item('perfex-office-theme-settings', [
            'name'     => 'Office Theme',
            'href'     => admin_url('perfex_office_theme/settings'),
            'position' => 48,
        ]);
        add_setup_menu_item('perfex-office-theme-health', [
            'name'     => 'Office Theme Health',
            'href'     => admin_url('perfex_office_theme/health'),
            'position' => 49,
        ]);
    }
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
    $primary = perfex_office_theme_safe_hex(get_option('smart_choice_theme_primary_color'), '#0077CC');
    $light   = perfex_office_theme_safe_hex(get_option('smart_choice_theme_light_blue_color'), '#2CA8FF');
    $orange  = perfex_office_theme_safe_hex(get_option('smart_choice_theme_orange_color'), '#F96302');
    $deep_orange = perfex_office_theme_safe_hex(get_option('smart_choice_theme_deep_orange_color'), '#E59A00');
    $green   = perfex_office_theme_safe_hex(get_option('smart_choice_theme_green_color'), '#00A651');
    $soft    = perfex_office_theme_safe_hex(get_option('smart_choice_theme_soft_background'), '#F5F5F5');
    $trim    = perfex_office_theme_safe_hex(get_option('smart_choice_theme_trim_color'), '#0077CC');
    $grad1   = perfex_office_theme_safe_hex(get_option('smart_choice_theme_gradient_start'), '#0077CC');
    $grad2   = perfex_office_theme_safe_hex(get_option('smart_choice_theme_gradient_end'), '#00A651');

    $admin_bg       = perfex_office_theme_safe_hex(get_option('smart_choice_theme_admin_menu_bg'), '#ffffff');
    $admin_text     = perfex_office_theme_safe_hex(get_option('smart_choice_theme_admin_menu_text'), '#111111');
    $admin_hover_bg = perfex_office_theme_safe_hex(get_option('smart_choice_theme_admin_menu_hover_bg'), '#EAF7FF');
    $admin_hover_tx = perfex_office_theme_safe_hex(get_option('smart_choice_theme_admin_menu_hover_text'), '#0077CC');
    $admin_icon     = perfex_office_theme_safe_hex(get_option('smart_choice_theme_admin_icon_color'), '#0077CC');
    $admin_size     = perfex_office_theme_safe_number(get_option('smart_choice_theme_admin_menu_font_size'), 14, 11, 22);
    $base_size      = perfex_office_theme_safe_number(get_option('smart_choice_theme_base_font_size'), 14, 11, 22);
    $admin_radius   = perfex_office_theme_safe_number(get_option('smart_choice_theme_admin_hover_radius'), 8, 2, 22);
    $card_radius    = perfex_office_theme_safe_number(get_option('smart_choice_theme_card_radius'), 12, 2, 28);
    $button_radius  = perfex_office_theme_safe_number(get_option('smart_choice_theme_button_radius'), 8, 2, 24);
    $admin_shadow_on = get_option('smart_choice_theme_admin_menu_shadow') === '1';
    $admin_shadow   = perfex_office_theme_safe_css_text_shadow(get_option('smart_choice_theme_admin_text_shadow'), 'none');

    $client_bg       = perfex_office_theme_safe_hex(get_option('smart_choice_theme_client_menu_bg'), '#0077CC');
    $client_text     = perfex_office_theme_safe_hex(get_option('smart_choice_theme_client_menu_text'), '#ffffff');
    $client_hover_bg = perfex_office_theme_safe_hex(get_option('smart_choice_theme_client_menu_hover_bg'), '#EAF7FF');
    $client_hover_tx = perfex_office_theme_safe_hex(get_option('smart_choice_theme_client_menu_hover_text'), '#0077CC');
    $client_icon     = perfex_office_theme_safe_hex(get_option('smart_choice_theme_client_icon_color'), '#0077CC');
    $client_size     = perfex_office_theme_safe_number(get_option('smart_choice_theme_client_menu_font_size'), 14, 11, 22);
    $client_shadow_on = get_option('smart_choice_theme_client_menu_shadow') === '1';
    $client_shadow   = perfex_office_theme_safe_css_text_shadow(get_option('smart_choice_theme_client_text_shadow'), 'none');

    $trim_primary = perfex_office_theme_safe_hex(get_option('smart_choice_theme_trim_primary'), '#0077CC');
    $trim_secondary = perfex_office_theme_safe_hex(get_option('smart_choice_theme_trim_secondary'), '#0077CC');
    $top_admin = perfex_office_theme_safe_hex(get_option('smart_choice_theme_top_bar_admin'), '#0077CC');
    $sidebar_bar = perfex_office_theme_safe_hex(get_option('smart_choice_theme_sidebar_bar'), '#005FAF');
    $component_bar = perfex_office_theme_safe_hex(get_option('smart_choice_theme_component_bar'), '#0077CC');
    $compact_mode = get_option('smart_choice_theme_compact_mode') !== '0';
    $force_white_hamburger = get_option('smart_choice_theme_force_white_hamburger') !== '0';
    $login_button_width = perfex_office_theme_safe_number(get_option('smart_choice_theme_login_button_width'), 190, 120, 320);
    $progress_height = perfex_office_theme_safe_number(get_option('smart_choice_theme_progress_height'), 8, 4, 20);
    $terms_link_color = perfex_office_theme_safe_hex(get_option('smart_choice_theme_terms_link_color'), '#0077CC');
    $dev_bg = perfex_office_theme_safe_hex(get_option('smart_choice_theme_development_notice_bg'), '#FFF8E1');
    $dev_text = perfex_office_theme_safe_hex(get_option('smart_choice_theme_development_notice_text'), '#8A4B00');
    $logo_height = perfex_office_theme_safe_number(get_option('smart_choice_theme_nav_logo_height'), 46, 24, 90);
    $topbar_height = perfex_office_theme_safe_number(get_option('smart_choice_theme_topbar_height'), 62, 46, 96);
    $hamburger_size = perfex_office_theme_safe_number(get_option('smart_choice_theme_hamburger_size'), 22, 16, 42);
    $dropdown_max_height = perfex_office_theme_safe_number(get_option('smart_choice_theme_dropdown_max_height'), 360, 180, 720);
    $select_dropdown_max_height = perfex_office_theme_safe_number(get_option('smart_choice_theme_select_dropdown_max_height'), 320, 160, 640);
    $table_fullname_width = perfex_office_theme_safe_number(get_option('smart_choice_theme_table_fullname_width'), 170, 100, 320);
    $table_email_width = perfex_office_theme_safe_number(get_option('smart_choice_theme_table_email_width'), 180, 90, 360);
    $mobile_nav_height = perfex_office_theme_safe_number(get_option('smart_choice_theme_mobile_nav_height'), 64, 46, 96);
    $client_login_button_height = perfex_office_theme_safe_number(get_option('smart_choice_theme_client_login_button_height'), 36, 26, 60);
    $force_gradient_white_text = get_option('smart_choice_theme_force_gradient_white_text') !== '0';
    $smooth_dropdowns = get_option('smart_choice_theme_remove_nested_scrollbars') !== '0';

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
    echo "\n/* Smart Choice Office Theme v1.4.4 hard overrides */\n";
    if ($force_white_hamburger) {
        echo 'body.admin #header .fa-bars,body.admin #header .fa-navicon,body.admin #header .navbar-toggle,body.admin #header .navbar-toggle i,body.admin #header .navbar-toggle span,body.admin #header button i.fa-bars,body.admin #header button[aria-label*="menu"] i,body.admin #menu-toggle i,body.admin .navbar-header .fa-bars,body.admin .navbar-header .icon-bar,body.customers .navbar-toggle .icon-bar,body.customers .navbar .fa-bars,body.customers #mobile-menu-toggle i,body.authentication .navbar-toggle .icon-bar{color:#fff!important;background-color:#fff!important;border-color:#fff!important;}\n';
        echo 'body.admin #header .navbar-toggle .icon-bar,body.admin #header button.navbar-toggle span.icon-bar,body.customers .navbar-toggle .icon-bar{display:block!important;width:22px!important;height:2px!important;margin:4px 0!important;border-radius:2px!important;background:#fff!important;}\n';
    }
    if ($compact_mode) {
        echo 'body.admin .btn,body.customers .btn,body.authentication .btn{padding:6px 12px!important;min-height:30px!important;line-height:1.2!important;border-radius:' . $button_radius . 'px!important;font-size:13px!important;box-shadow:0 4px 12px rgba(15,23,42,.08)!important;}\n';
        echo 'body.admin .btn-sm,body.customers .btn-sm{padding:4px 9px!important;min-height:26px!important;font-size:12px!important;border-radius:7px!important;}\n';
        echo 'body.admin .modules .btn,body.admin [class*="module"] .btn,body.admin a[href*="mods"] .btn,body.admin .btn[data-toggle="modal"]{padding:5px 10px!important;min-height:28px!important;font-size:12px!important;}\n';
        echo 'body.admin .fc-button,body.admin .fc-state-default,body.admin .dt-button,body.admin .dataTables_wrapper .btn,body.admin .dataTables_length select{padding:4px 9px!important;min-height:26px!important;height:auto!important;font-size:12px!important;border-radius:7px!important;}\n';
        echo 'body.admin .fc-toolbar h2{font-size:18px!important;line-height:1.25!important;} body.admin .fc-toolbar{gap:8px!important;flex-wrap:wrap!important;}\n';
        echo 'body.admin .progress{height:' . $progress_height . 'px!important;border-radius:999px!important;box-shadow:inset 0 1px 2px rgba(15,23,42,.08)!important;background:#edf3f7!important;} body.admin .progress-bar{height:' . $progress_height . 'px!important;border-radius:999px!important;}\n';
        echo 'body.admin .quick-stats .progress,body.admin .home-summary .progress,body.admin .invoice-overview .progress,body.admin .leads-overview .progress{height:' . $progress_height . 'px!important;}\n';
        echo 'body.admin .dashboard_stat_circle,body.admin .circle-tile,body.admin .c100,body.admin .percent-circle{transform:scale(.82)!important;transform-origin:center!important;}\n';
        echo 'body.admin .nav-tabs>li>a{padding:8px 11px!important;font-size:13px!important;border-radius:8px 8px 0 0!important;} body.admin .nav-tabs{display:flex!important;flex-wrap:wrap!important;gap:2px!important;}\n';
    }
    echo 'body.admin .btn-primary,body.admin .btn-info,body.admin .btn-success,body.admin .btn-danger,body.admin .btn-warning,body.admin .btn-default:hover,body.admin .buttons-collection{color:#fff!important;} body.admin .btn-primary *,body.admin .btn-info *,body.admin .btn-success *,body.admin .buttons-collection *{color:#fff!important;}\n';
    echo 'body.admin .btn-default{color:#263238!important;background:#fff!important;border:1px solid #d7e3e6!important;} body.admin .btn-default:hover{background:' . $primary . '!important;border-color:' . $primary . '!important;}\n';
    echo 'body.admin .alert-warning,body.admin .system-popup-warning,body.admin .environment-warning,body.admin [class*="development"]{background:' . $dev_bg . '!important;color:' . $dev_text . '!important;border:1px solid #ffd08a!important;border-left:5px solid ' . $orange . '!important;border-radius:12px!important;box-shadow:0 6px 18px rgba(247,148,29,.14)!important;}\n';
    echo 'body.admin a,body.admin .text-purple,body.admin [style*="purple"],body.admin [style*="#8e24aa"]{color:' . $primary . '!important;} body.customers a[href*="terms"],body.authentication a[href*="terms"],body.customers .terms-and-conditions a{color:' . $terms_link_color . '!important;}\n';
    echo 'body.authentication .btn,body.customers .login-form .btn,body.customers .register-form .btn,body.customers form[action*="login"] .btn,body.customers form[action*="register"] .btn{width:' . $login_button_width . 'px!important;max-width:100%!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;margin:8px auto!important;padding:9px 18px!important;border-radius:9px!important;}\n';
    echo 'body.authentication .btn-primary:hover,body.customers .btn-primary:hover{background:' . $orange . '!important;border-color:' . $orange . '!important;color:#111!important;} body.authentication .btn-primary:hover i,body.customers .btn-primary:hover i{color:#111!important;}\n';
    echo 'body.admin .table-responsive{width:100%!important;max-width:100%!important;overflow-x:auto!important;overflow-y:visible!important;border:0!important;} body.admin .dataTables_wrapper{width:100%!important;max-width:100%!important;overflow:visible!important;} body.admin .panel-body{max-width:100%!important;} body.admin table.table{width:100%!important;max-width:100%!important;}\n';
    echo 'body.admin .staff-profile-image-small,body.admin .staff-profile-image-thumb{max-width:38px!important;max-height:38px!important;border-radius:10px!important;} body.admin table.staff .btn,body.admin table [href*="staff"] .btn{padding:4px 8px!important;}\n';
    echo 'body.admin .modal-body .table-responsive,body.admin .tab-content .table-responsive,body.admin .panel-body .table-responsive{overflow-x:auto!important;overflow-y:visible!important;} body.admin .modal-body,body.admin .tab-content,body.admin .panel-body{scrollbar-width:auto;}\n';
    echo 'body.admin .sorting:after,body.admin .sorting_asc:after,body.admin .sorting_desc:after{right:6px!important;top:50%!important;transform:translateY(-50%)!important;line-height:1!important;} body.admin table.dataTable thead>tr>th{padding-right:24px!important;vertical-align:middle!important;}\n';
    echo 'body.admin .commission-table,body.admin [class*="commission"] table{border-radius:14px!important;overflow:hidden!important;box-shadow:0 10px 24px rgba(15,23,42,.08)!important;} body.admin [class*="commission"] .panel_s{border-top:4px solid ' . $green . '!important;}\n';

    echo "
/* Smart Choice CRM Sleek Control Layer v1.4.5 */
";
    echo 'body.admin #header,body.admin #header nav.navbar,body.admin #header .navbar,body.admin .navbar-default{min-height:' . $topbar_height . 'px!important;}
';
    echo 'body.admin #header .navbar-brand,body.admin #header .logo,body.admin #header .navbar-header{min-height:' . $topbar_height . 'px!important;display:flex!important;align-items:center!important;}
';
    echo 'body.admin #header img.img-responsive,body.admin #header .navbar-brand img,body.admin #header img[src*="uploads/company"],body.customers .navbar-brand img,body.authentication .navbar-brand img{height:' . $logo_height . 'px!important;max-height:' . $logo_height . 'px!important;width:auto!important;max-width:260px!important;object-fit:contain!important;}
';
    echo 'body.admin #header button.hide-menu,body.admin button.hide-menu{color:#fff!important;background:rgba(255,255,255,.12)!important;border:1px solid rgba(255,255,255,.18)!important;border-radius:10px!important;min-width:' . ($hamburger_size + 16) . 'px!important;min-height:' . ($hamburger_size + 16) . 'px!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;}
';
    echo 'body.admin #header button.hide-menu svg,body.admin button.hide-menu svg{width:' . $hamburger_size . 'px!important;height:' . $hamburger_size . 'px!important;color:#fff!important;stroke:#fff!important;}
';
    echo 'body.admin #header button.hide-menu svg path,body.admin button.hide-menu svg path{stroke:#fff!important;}
';
    echo 'body.admin .mobile-menu-toggle,body.admin .navbar-toggle,body.customers .navbar-toggle{color:#fff!important;} body.admin .mobile-menu-toggle *,body.admin .navbar-toggle *,body.customers .navbar-toggle *{color:#fff!important;border-color:#fff!important;}
';
    echo 'body.admin .dropdown-menu,body.admin #header .dropdown-menu,body.admin .smart-choice-links-dropdown{max-height:' . $dropdown_max_height . 'px!important;overflow-y:auto!important;overflow-x:hidden!important;border-radius:14px!important;box-shadow:0 18px 44px rgba(15,23,42,.18)!important;}
';
    if ($smooth_dropdowns) { echo 'body.admin .bootstrap-select .dropdown-menu.open{overflow:hidden!important;max-height:' . $select_dropdown_max_height . 'px!important;min-height:0!important;} body.admin .bootstrap-select .dropdown-menu.open .inner.open{overflow-y:auto!important;overflow-x:hidden!important;max-height:' . ($select_dropdown_max_height - 82) . 'px!important;min-height:0!important;} body.admin .bootstrap-select .dropdown-menu.inner{overflow:visible!important;max-height:none!important;}
'; }
    echo 'body.admin .bootstrap-select .bs-searchbox .form-control{height:34px!important;border-radius:9px!important;} body.admin .bootstrap-select .bs-actionsbox .btn{height:28px!important;padding:4px 8px!important;font-size:12px!important;}
';
    echo 'body.admin table.dataTable thead th,body.admin table.table thead th{white-space:nowrap!important;font-size:12px!important;line-height:1.2!important;padding:9px 22px 9px 8px!important;}
';
    echo 'body.admin table.dataTable tbody td,body.admin table.table tbody td{font-size:12px!important;line-height:1.25!important;vertical-align:middle!important;word-break:normal!important;}
';
    echo 'body.admin table.dataTable th[aria-label*="Full name"]{width:' . $table_fullname_width . 'px!important;max-width:' . $table_fullname_width . 'px!important;}
';
    echo 'body.admin table.dataTable td:nth-child(2),body.admin table.table td:nth-child(2){max-width:' . $table_fullname_width . 'px!important;white-space:normal!important;overflow:hidden!important;text-overflow:ellipsis!important;}
';
    echo 'body.admin table.dataTable th[aria-label*="Email"],body.admin table.table th.email,body.admin table.dataTable td a[href^="mailto:"]{max-width:' . $table_email_width . 'px!important;} body.admin table.dataTable td a[href^="mailto:"]{display:inline-block!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;vertical-align:middle!important;}
';
    echo 'body.authentication .btn,body.customers .login-form .btn,body.customers form[action*="login"] .btn{min-height:' . $client_login_button_height . 'px!important;height:' . $client_login_button_height . 'px!important;padding:5px 14px!important;font-size:13px!important;}
';
    echo '@media(max-width:767px){body.admin #header,body.admin #header nav.navbar,body.customers .navbar,body.customers .navbar-default{min-height:' . $mobile_nav_height . 'px!important;} body.admin #header img.img-responsive,body.admin #header img[src*="uploads/company"],body.customers .navbar-brand img{height:' . max(28, min($logo_height, $mobile_nav_height - 12)) . 'px!important;max-height:' . max(28, min($logo_height, $mobile_nav_height - 12)) . 'px!important;} body.admin #side-menu li a,body.admin #setup-menu li a,body.customers .navbar-nav>li>a{font-size:' . max($admin_size, 15) . 'px!important;min-height:42px!important;} body.admin .navbar-collapse,body.customers .navbar-collapse{max-height:calc(100vh - ' . $mobile_nav_height . 'px)!important;overflow-y:auto!important;} }
';
    if ($force_gradient_white_text) { echo 'body.admin [style*="gradient"],body.admin .btn-primary,body.admin .btn-info,body.admin .buttons-collection,body.admin .smart-choice-settings-hero,body.admin .smart-choice-badge{color:#fff!important;} body.admin [style*="gradient"] *,body.admin .btn-primary *,body.admin .btn-info *,body.admin .buttons-collection *{color:#fff!important;} body.admin [style*="gradient"]:hover,body.admin .btn-primary:hover,body.admin .btn-info:hover{color:#111!important;} body.admin [style*="gradient"]:hover *,body.admin .btn-primary:hover *,body.admin .btn-info:hover *{color:#111!important;}
'; }

    echo '\n/* Smart Choice Contractors USA Global Polish v1.4.6 */\n';
    echo 'body.admin,body.customers,body.authentication{font-size:' . $base_size . 'px!important;background:' . $soft . '!important;}\n';
    echo 'body.admin .btn,body.customers .btn,body.authentication .btn,body.admin button,body.customers button{display:inline-flex!important;align-items:center!important;justify-content:center!important;text-align:center!important;vertical-align:middle!important;line-height:1.2!important;white-space:nowrap!important;}\n';
    echo 'body.admin input[type="file"] + .btn,body.admin .input-group-btn .btn,body.admin .btn[type="submit"],body.admin button[type="submit"]{align-items:center!important;justify-content:center!important;text-align:center!important;}\n';
    echo 'body.admin #header{background:linear-gradient(90deg,' . $primary . ',' . $green . ')!important;border-bottom:3px solid ' . $orange . '!important;}\n';
    echo 'body.admin #header .navbar-brand,body.admin #header .logo{height:' . $topbar_height . 'px!important;padding:2px 12px!important;}\n';
    echo 'body.admin #header img[src*="uploads/company/7758fd32c6529f973149654d5b900015"],body.admin #header img[src*="uploads/company"],body.customers .navbar-brand img,body.authentication .navbar-brand img{height:calc(' . $topbar_height . 'px - 6px)!important;max-height:calc(' . $topbar_height . 'px - 6px)!important;width:auto!important;max-width:360px!important;object-fit:contain!important;}\n';
    echo 'body.customers .navbar,body.authentication .navbar{min-height:' . $topbar_height . 'px!important;background:linear-gradient(90deg,#fff,' . $soft . ')!important;border-bottom:3px solid ' . $orange . '!important;}\n';
    echo 'body.customers .navbar-brand,body.authentication .navbar-brand{height:' . $topbar_height . 'px!important;display:flex!important;align-items:center!important;padding:2px 14px!important;}\n';
    echo 'body.customers .navbar-nav,body.authentication .navbar-nav{display:flex!important;align-items:center!important;justify-content:center!important;gap:6px!important;}\n';
    echo 'body.customers .navbar-nav>li>a,body.authentication .navbar-nav>li>a{min-height:38px!important;display:flex!important;align-items:center!important;justify-content:center!important;border-radius:999px!important;padding:8px 14px!important;color:' . $primary . '!important;font-weight:700!important;}\n';
    echo 'body.customers .navbar-nav>li>a:hover,body.authentication .navbar-nav>li>a:hover,body.admin .dropdown-menu>li>a:hover,body.admin #side-menu li a:hover{background:' . $client_hover_bg . '!important;color:' . $client_hover_tx . '!important;}\n';
    echo 'body.customers .navbar-nav>li:last-child>a,body.authentication .navbar-nav>li:last-child>a{background:' . $orange . '!important;color:#111!important;border:1px solid ' . $orange . '!important;}\n';
    echo 'body.customers.logged-in .navbar .navbar-right,body.customers .navbar .navbar-right{margin-left:auto!important;display:flex!important;align-items:center!important;justify-content:flex-end!important;height:' . $topbar_height . 'px!important;}\n';
    echo 'body.customers .navbar .navbar-right img,body.customers .client-profile-image-small,body.customers .staff-profile-image-small{width:38px!important;height:38px!important;border-radius:999px!important;object-fit:cover!important;border:2px solid ' . $orange . '!important;}\n';
    echo 'body.customers footer,body.authentication footer,body.customers .footer,body.authentication .footer{background:linear-gradient(90deg,' . $primary . ',' . $green . ')!important;color:#fff!important;border-top:3px solid ' . $orange . '!important;}\n';
    echo 'body.customers footer a,body.authentication footer a,body.customers .footer a,body.authentication .footer a{color:#fff!important;text-decoration:underline!important;}\n';
    echo 'body.customers .kb-search,body.customers .knowledge-base,body.customers [class*="knowledge"] .panel_s,body.customers [class*="kb"] .panel_s{border-radius:18px!important;box-shadow:0 14px 34px rgba(15,23,42,.10)!important;border-top:5px solid ' . $green . '!important;overflow:hidden!important;}\n';
    echo 'body.customers .knowledge-base a,body.customers [class*="kb"] a{font-weight:700!important;color:' . $primary . '!important;}\n';
    echo 'body.customers .knowledge-base .panel-heading,body.customers [class*="kb"] .panel-heading{background:linear-gradient(90deg,' . $primary . ',' . $light . ')!important;color:#fff!important;font-weight:800!important;}\n';
    echo 'body.customers .knowledge-base .list-group-item,body.customers [class*="kb"] .list-group-item{border-left:4px solid transparent!important;transition:all .18s ease!important;}\n';
    echo 'body.customers .knowledge-base .list-group-item:hover,body.customers [class*="kb"] .list-group-item:hover{border-left-color:' . $orange . '!important;background:#fff8e1!important;transform:translateX(2px)!important;}\n';
    echo 'body.admin table.table,body.admin table.dataTable{table-layout:auto!important;border-collapse:separate!important;border-spacing:0!important;}\n';
    echo 'body.admin table.table thead th,body.admin table.dataTable thead th{position:relative!important;resize:horizontal!important;overflow:auto!important;min-width:60px!important;}\n';
    echo 'body.admin .dataTables_wrapper .dt-buttons .btn,body.admin .dataTables_wrapper .btn{margin:2px!important;}\n';
    echo 'body.admin .panel_s,body.customers .panel_s,body.admin .modal-content{border-radius:' . $card_radius . 'px!important;box-shadow:0 10px 30px rgba(15,23,42,.08)!important;}\n';
    echo '@media(max-width:767px){body.admin .content,body.customers .container,body.customers .content,body.authentication .container{width:100%!important;max-width:100%!important;padding-left:12px!important;padding-right:12px!important;} body.admin .panel_s,body.customers .panel_s{margin-left:0!important;margin-right:0!important;} body.admin table,body.customers table{font-size:14px!important;} body.customers .navbar-header,body.authentication .navbar-header{display:flex!important;align-items:center!important;justify-content:space-between!important;width:100%!important;} body.customers .navbar-brand img,body.authentication .navbar-brand img{height:calc(' . $mobile_nav_height . 'px - 8px)!important;max-width:260px!important;} body.customers .navbar-collapse,body.authentication .navbar-collapse{background:#fff!important;border-radius:0 0 14px 14px!important;box-shadow:0 12px 30px rgba(15,23,42,.14)!important;} body.customers .navbar-nav,body.authentication .navbar-nav{display:block!important;width:100%!important;} body.customers .navbar-nav>li>a,body.authentication .navbar-nav>li>a{justify-content:flex-start!important;margin:4px 8px!important;} }\n';

    echo "\n/* Smart Choice CRM Client/Admin Repair Layer v1.4.8 */\n";
    echo 'body.admin .btn,body.customers .btn,body.authentication .btn,body.admin button,body.customers button,body.authentication button{display:inline-flex!important;align-items:center!important;justify-content:center!important;text-align:center!important;line-height:1.2!important;}\n';
    echo 'body.admin .btn input,body.customers .btn input{margin:auto!important;}\n';
    echo 'body.admin a.btn[href*="perfex_office_theme/settings"],body.admin a.btn[href*="perfex_office_theme/health"],body.admin a.btn[href*="settings?group=admin-theme-settings"],body.admin a.btn[href*="settings?group=admin-theme-health"],body.admin a[href*="perfex_office_theme/settings"],body.admin a[href*="perfex_office_theme/health"],body.admin a[href*="settings?group=admin-theme-settings"],body.admin a[href*="settings?group=admin-theme-health"]{background:' . $primary . '!important;color:#fff!important;border-color:' . $primary . '!important;border-radius:10px!important;font-weight:700!important;}\n';
    echo 'body.admin a[href*="perfex_office_theme/health"],body.admin a[href*="settings?group=admin-theme-health"]{background:' . $green . '!important;border-color:' . $green . '!important;}\n';
    echo 'body.authentication .navbar,body.customers .navbar,body.customers .navbar-default{display:flex!important;align-items:center!important;min-height:' . $topbar_height . 'px!important;padding:0 14px!important;background:#fff!important;border-bottom:3px solid ' . $orange . '!important;}\n';
    echo 'body.authentication .navbar .container,body.customers .navbar .container,body.customers .navbar .container-fluid{display:flex!important;align-items:center!important;width:100%!important;max-width:100%!important;}\n';
    echo 'body.authentication .navbar-header,body.customers .navbar-header{display:flex!important;align-items:center!important;flex:0 0 auto!important;height:' . $topbar_height . 'px!important;}\n';
    echo 'body.authentication .navbar-brand,body.customers .navbar-brand{display:flex!important;align-items:center:center!important;align-items:center!important;height:' . $topbar_height . 'px!important;padding:0 12px!important;margin:0!important;}\n';
    echo 'body.authentication .navbar-brand img,body.customers .navbar-brand img{height:calc(' . $topbar_height . 'px - 8px)!important;max-height:calc(' . $topbar_height . 'px - 8px)!important;width:auto!important;max-width:380px!important;object-fit:contain!important;}\n';
    echo 'body.authentication .navbar-collapse,body.customers .navbar-collapse{display:flex!important;align-items:center!important;justify-content:space-between!important;flex:1 1 auto!important;padding:0!important;}\n';
    echo 'body.authentication .navbar-nav,body.customers .navbar-nav{display:flex!important;align-items:center!important;justify-content:center!important;gap:6px!important;margin:0 auto!important;float:none!important;}\n';
    echo 'body.authentication .navbar-nav>li,body.customers .navbar-nav>li{display:flex!important;align-items:center!important;float:none!important;}\n';
    echo 'body.authentication .navbar-nav>li>a,body.customers .navbar-nav>li>a{height:38px!important;min-height:38px!important;display:flex!important;align-items:center!important;justify-content:center!important;padding:7px 13px!important;border-radius:999px!important;line-height:1.1!important;color:' . $primary . '!important;font-weight:700!important;white-space:nowrap!important;}\n';
    echo 'body.authentication .navbar-nav>li>a:hover,body.customers .navbar-nav>li>a:hover{background:' . $client_hover_bg . '!important;color:' . $client_hover_tx . '!important;}\n';
    echo 'body.authentication .navbar-nav>li:last-child>a,body.customers .navbar-nav>li:last-child>a{background:' . $orange . '!important;color:#111!important;border:1px solid ' . $orange . '!important;}\n';
    echo 'body.customers .navbar-right{margin-left:auto!important;margin-right:0!important;display:flex!important;align-items:center!important;justify-content:flex-end!important;height:' . $topbar_height . 'px!important;}\n';
    echo 'body.customers .navbar-right>li,body.customers .navbar-right>li>a{display:flex!important;align-items:center!important;justify-content:center!important;height:' . $topbar_height . 'px!important;}\n';
    echo 'body.authentication form .form-control,body.authentication form .btn,body.authentication .bootstrap-select,body.authentication .bootstrap-select>.dropdown-toggle,body.customers form[action*="login"] .form-control,body.customers form[action*="login"] .btn,body.customers .login-form .form-control,body.customers .login-form .btn{width:100%!important;max-width:100%!important;box-sizing:border-box!important;}\n';
    echo 'body.authentication .g-recaptcha,body.customers .g-recaptcha{transform:scale(.96)!important;transform-origin:left top!important;max-width:100%!important;overflow:hidden!important;}\n';
    echo 'body.customers input[name="quantity"],body.customers input.product-quantity,body.customers .product input[type="number"],body.customers table input[type="number"],body.customers .qty,body.customers .quantity{max-width:86px!important;width:86px!important;min-width:62px!important;text-align:center!important;padding-left:6px!important;padding-right:6px!important;}\n';
    echo 'body.customers .knowledge-base .panel_s,body.customers [class*="knowledge"] .panel_s,body.customers [class*="kb"] .panel_s{background:linear-gradient(180deg,#fff,' . $soft . ')!important;border:1px solid #e5edf2!important;border-radius:20px!important;box-shadow:0 18px 42px rgba(15,23,42,.10)!important;}\n';
    echo 'body.customers .knowledge-base .panel-heading,body.customers [class*="knowledge"] .panel-heading,body.customers [class*="kb"] .panel-heading{background:linear-gradient(90deg,' . $primary . ',' . $green . ')!important;color:#fff!important;border:0!important;}\n';
    echo 'body.customers .knowledge-base .list-group-item,body.customers [class*="knowledge"] .list-group-item,body.customers [class*="kb"] .list-group-item{border-left:5px solid ' . $light . '!important;margin-bottom:6px!important;border-radius:12px!important;background:#fff!important;box-shadow:0 6px 16px rgba(15,23,42,.05)!important;}\n';
    echo 'body.customers .knowledge-base .list-group-item:hover,body.customers [class*="knowledge"] .list-group-item:hover,body.customers [class*="kb"] .list-group-item:hover{border-left-color:' . $orange . '!important;background:#fff8e1!important;transform:translateX(3px)!important;}\n';
    echo '@media(max-width:767px){body.authentication .navbar,body.customers .navbar{display:block!important;padding:0!important;} body.authentication .navbar .container,body.customers .navbar .container,body.customers .navbar .container-fluid{display:block!important;padding-left:12px!important;padding-right:12px!important;} body.authentication .navbar-header,body.customers .navbar-header{width:100%!important;display:flex!important;justify-content:space-between!important;} body.authentication .navbar-toggle,body.customers .navbar-toggle{background:' . $primary . '!important;border-color:' . $primary . '!important;margin-top:14px!important;} body.authentication .navbar-toggle .icon-bar,body.customers .navbar-toggle .icon-bar{background:#fff!important;} body.authentication .navbar-collapse,body.customers .navbar-collapse{display:block!important;background:#fff!important;border-radius:0 0 18px 18px!important;box-shadow:0 18px 42px rgba(15,23,42,.14)!important;padding:8px!important;} body.authentication .navbar-nav,body.customers .navbar-nav{display:block!important;width:100%!important;margin:0!important;} body.authentication .navbar-nav>li,body.customers .navbar-nav>li{display:block!important;width:100%!important;} body.authentication .navbar-nav>li>a,body.customers .navbar-nav>li>a{justify-content:flex-start!important;width:100%!important;margin:4px 0!important;height:44px!important;} body.authentication .navbar-brand img,body.customers .navbar-brand img{height:calc(' . $mobile_nav_height . 'px - 8px)!important;max-width:280px!important;} body.authentication .g-recaptcha,body.customers .g-recaptcha{transform:scale(.90)!important;} }\n';


    echo "\n/* Smart Choice CRM Hard Override Layer v1.4.8 */\n";
    echo 'body.admin #header,body.admin #header nav,body.admin #header .navbar,body.admin .navbar-default{background:linear-gradient(90deg,' . $primary . ',' . $green . ')!important;border:0!important;min-height:' . $topbar_height . 'px!important;box-shadow:0 8px 24px rgba(0,119,204,.18)!important;}\n';
    echo 'body.admin #header .navbar-brand img,body.admin #header img[src*="uploads/company"],body.admin #header img[src*="7758fd32c6529f973149654d5b900015"]{height:calc(' . $topbar_height . 'px - 4px)!important;max-height:calc(' . $topbar_height . 'px - 4px)!important;width:auto!important;max-width:440px!important;object-fit:contain!important;}\n';
    echo 'body.admin #side-menu li>a:hover,body.admin #setup-menu li>a:hover,body.admin .dropdown-menu>li>a:hover,body.admin .dropdown-menu>li>a:focus{background:' . $client_hover_bg . '!important;color:' . $primary . '!important;border-radius:9px!important;}\n';
    echo 'body.admin .panel_s>.panel-heading,body.admin .panel-heading,body.admin .table thead th,body.admin table.dataTable thead th{background:linear-gradient(90deg,' . $primary . ',' . $green . ')!important;color:#fff!important;border-color:' . $primary . '!important;}\n';
    echo 'body.admin .panel_s,body.admin .panel-body,body.admin .content,body.admin .tw-bg-white,body.admin .bg-white{max-width:100%!important;box-sizing:border-box!important;}\n';
    echo 'body.admin .table-responsive,body.admin .dataTables_wrapper{display:block!important;width:100%!important;max-width:100%!important;overflow-x:auto!important;overflow-y:visible!important;box-sizing:border-box!important;}\n';
    echo 'body.admin table.table,body.admin table.dataTable{width:100%!important;max-width:none!important;margin-left:0!important;margin-right:0!important;}\n';
    echo 'body.admin table.staff,body.admin table.table-staff,body.admin table[data-table="staff"],body.admin .staff .table,body.admin .staff-table .table{min-width:960px!important;}\n';
    echo 'body.admin .staff .panel-body,body.admin .staff-table,body.admin .staff-profile-tabs,body.admin [class*="staff"] .table-responsive{overflow-x:auto!important;max-width:100%!important;}\n';
    echo 'body.admin .dataTables_scrollHeadInner,body.admin .dataTables_scrollHeadInner table,body.admin .dataTables_scrollBody table{width:100%!important;}\n';
    echo 'body.admin .btn,body.admin button,body.admin .dt-button{min-height:30px!important;padding:6px 12px!important;border-radius:9px!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;text-align:center!important;}\n';
    echo 'body.admin .btn-primary,body.admin .btn-info,body.admin .buttons-collection,body.admin .dt-button.buttons-collection{background:linear-gradient(90deg,' . $primary . ',' . $green . ')!important;border-color:' . $primary . '!important;color:#fff!important;}\n';
    echo 'body.admin .btn-warning{background:' . $orange . '!important;border-color:' . $orange . '!important;color:#111!important;} body.admin .btn-success{background:' . $green . '!important;border-color:' . $green . '!important;}\n';
    echo 'body.customers .navbar-nav>li>a,body.authentication .navbar-nav>li>a{line-height:1!important;vertical-align:middle!important;}\n';
    echo 'body.authentication form .bootstrap-select,body.authentication form .bootstrap-select>.dropdown-toggle,body.authentication form .btn,body.authentication form button,body.authentication form .form-control,body.authentication .g-recaptcha,body.customers .login-form .bootstrap-select,body.customers .login-form .bootstrap-select>.dropdown-toggle,body.customers .login-form .btn,body.customers .login-form button,body.customers .login-form .form-control{width:100%!important;max-width:100%!important;}\n';
    echo 'body.customers input[name="quantity"],body.customers input[type="number"][name*="qty"],body.customers input[type="number"][name*="quantity"],body.customers .product-quantity input,body.customers .quantity input{width:76px!important;max-width:76px!important;min-width:58px!important;}\n';


    echo "\n/* Smart Choice Office Theme v1.4.9 final dynamic override */\n";
    echo 'body.admin .sc-col-resizer,body.admin .sc-col-resizer-v148,body.admin .sc-table-lockbar{display:none!important;visibility:hidden!important;width:0!important;height:0!important;overflow:hidden!important;}\n';
    echo 'body.admin table.table thead th,body.admin table.dataTable thead th,body.admin .table thead>tr>th{background:#EAF7FF!important;color:#111111!important;border-color:#D6ECFA!important;font-size:12px!important;font-weight:700!important;line-height:1.25!important;white-space:nowrap!important;vertical-align:middle!important;padding:9px 8px!important;}\n';
    echo 'body.admin table.table tbody td,body.admin table.dataTable tbody td{font-size:12px!important;line-height:1.3!important;vertical-align:middle!important;padding:8px!important;white-space:normal!important;}\n';
    echo 'body.admin .table-responsive,body.admin .dataTables_wrapper{width:100%!important;max-width:100%!important;overflow-x:auto!important;background:#fff!important;border-radius:14px!important;padding:8px!important;box-sizing:border-box!important;}\n';
    echo 'body.admin #header,body.admin #header nav,body.admin #header .navbar,body.admin .navbar-default{background:linear-gradient(90deg,#0077CC,#2CA8FF,#F96302)!important;border:0!important;min-height:68px!important;box-shadow:0 8px 24px rgba(0,119,204,.18)!important;}\n';
    echo 'body.admin #header .navbar-brand,body.admin #header a.navbar-brand{height:68px!important;display:flex!important;align-items:center!important;padding-top:0!important;padding-bottom:0!important;}\n';
    echo 'body.admin #header .navbar-brand img,body.admin #header img[src*=\"uploads/company\"],body.admin #header img[src*=\"7758fd32c6529f973149654d5b900015\"]{height:64px!important;max-height:64px!important;width:auto!important;max-width:520px!important;object-fit:contain!important;}\n';
    echo 'body.admin .staff .table-responsive,body.admin .staff-table .table-responsive,body.admin [class*=\"staff\"] .table-responsive{padding-left:18px!important;overflow-x:auto!important;}\n';
    echo 'body.admin table.table tbody td img,body.admin table.dataTable tbody td img,body.admin .staff-profile-image-small,body.admin img.staff-profile-image-small,body.admin .staff-profile-image-xs,body.admin img.client-profile-image-small{width:34px!important;height:34px!important;min-width:34px!important;max-width:34px!important;object-fit:cover!important;border-radius:50%!important;margin:0 8px 0 0!important;display:inline-block!important;vertical-align:middle!important;}\n';
    echo 'body.customers .navbar,body.authentication .navbar,body.customers .navbar-default,body.authentication .navbar-default{background:linear-gradient(90deg,#2CA8FF,#0077CC,#F96302)!important;border:0!important;min-height:72px!important;box-shadow:0 8px 24px rgba(0,119,204,.18)!important;}\n';
    echo 'body.customers .navbar-brand img,body.authentication .navbar-brand img{height:66px!important;max-height:66px!important;width:auto!important;max-width:360px!important;object-fit:contain!important;}\n';
    echo 'body.customers .navbar-nav>li>a,body.authentication .navbar-nav>li>a{display:flex!important;align-items:center!important;justify-content:center!important;min-height:42px!important;padding:9px 13px!important;color:#fff!important;font-weight:700!important;line-height:1.1!important;border-radius:10px!important;white-space:nowrap!important;}\n';
    echo 'body.authentication form .bootstrap-select,body.authentication form .bootstrap-select>.dropdown-toggle,body.authentication form .btn,body.authentication form button,body.authentication form .form-control{width:100%!important;max-width:100%!important;height:42px!important;display:flex!important;align-items:center!important;justify-content:center!important;}\n';
    echo 'body.authentication .g-recaptcha,body.authentication .g-recaptcha>div{max-width:100%!important;width:100%!important;overflow:hidden!important;}\n';
    echo 'body.customers input[name=\"quantity\"],body.customers input[type=\"number\"][name*=\"qty\"],body.customers input[type=\"number\"][name*=\"quantity\"],body.customers .product-quantity input,body.customers .quantity input{width:72px!important;max-width:72px!important;min-width:54px!important;text-align:center!important;}\n';
    echo 'body.customers .knowledge-base .panel_s,body.customers [class*=\"knowledge\"] .panel_s,body.customers [class*=\"kb\"] .panel_s{border:0!important;border-radius:22px!important;background:linear-gradient(180deg,#fff,#F7FBFF)!important;box-shadow:0 20px 50px rgba(15,23,42,.13)!important;overflow:hidden!important;}\n';
    echo 'body.customers .knowledge-base .panel-heading,body.customers [class*=\"knowledge\"] .panel-heading,body.customers [class*=\"kb\"] .panel-heading{background:linear-gradient(90deg,#2CA8FF,#F96302)!important;color:#fff!important;border:0!important;font-size:18px!important;font-weight:800!important;padding:16px 20px!important;}\n';
    echo 'body.customers .knowledge-base .list-group-item,body.customers [class*=\"knowledge\"] .list-group-item,body.customers [class*=\"kb\"] .list-group-item{border:1px solid #E3F2FD!important;border-left:6px solid #2CA8FF!important;margin-bottom:10px!important;border-radius:14px!important;background:#fff!important;box-shadow:0 8px 20px rgba(15,23,42,.06)!important;padding:14px 16px!important;font-weight:700!important;transition:all .18s ease!important;}\n';
    echo 'body.customers .knowledge-base .list-group-item:nth-child(2n),body.customers [class*=\"knowledge\"] .list-group-item:nth-child(2n),body.customers [class*=\"kb\"] .list-group-item:nth-child(2n){border-left-color:#F96302!important;background:#FFF3E8!important;}\n';
    echo 'body.customers .knowledge-base .list-group-item:nth-child(3n),body.customers [class*=\"knowledge\"] .list-group-item:nth-child(3n),body.customers [class*=\"kb\"] .list-group-item:nth-child(3n){border-left-color:#00A651!important;background:#F0FFF7!important;}\n';
    echo 'body.admin .alert-success,body.admin .toast-success,body.admin #toast-container>.toast-success,body.customers .alert-success,body.customers .toast-success{background:#00A651!important;border-color:#007A3D!important;color:#fff!important;border-radius:12px!important;box-shadow:0 10px 26px rgba(0,166,81,.22)!important;}\n';
    echo 'body.admin .alert-danger,body.admin .toast-error,body.admin #toast-container>.toast-error,body.customers .alert-danger,body.customers .toast-error{background:#F96302!important;border-color:#C94E00!important;color:#fff!important;border-radius:12px!important;box-shadow:0 10px 26px rgba(249,99,2,.22)!important;}\n';

    echo "\n/* Smart Choice Office Theme v1.5.0 staff table, client navigation, and mobile stabilization */\n";
    echo 'body.admin table.table thead th,body.admin table.dataTable thead th,body.admin .table thead>tr>th{resize:none!important;overflow:visible!important;background:#EAF7FF!important;color:#111!important;border-color:#D6ECFA!important;}\n';
    echo 'body.admin table.table,body.admin table.dataTable{table-layout:auto!important;}\n';
    echo 'body.admin table.staff,body.admin table.table-staff,body.admin table[data-table="staff"],body.admin .staff table.table,body.admin .staff-table table.table{min-width:1080px!important;width:100%!important;}\n';
    echo 'body.admin .staff .table-responsive,body.admin .staff-table .table-responsive,body.admin [class*="staff"] .table-responsive{padding-left:32px!important;padding-right:16px!important;overflow-x:auto!important;}\n';
    echo 'body.admin table.staff th:nth-child(1),body.admin table.table-staff th:nth-child(1),body.admin table[data-table="staff"] th:nth-child(1),body.admin .staff table.table th:nth-child(1){min-width:245px!important;width:245px!important;}\n';
    echo 'body.admin table.staff td:nth-child(1),body.admin table.table-staff td:nth-child(1),body.admin table[data-table="staff"] td:nth-child(1),body.admin .staff table.table td:nth-child(1){min-width:245px!important;width:245px!important;overflow:visible!important;white-space:nowrap!important;}\n';
    echo 'body.admin table.staff th:nth-child(2),body.admin table.table-staff th:nth-child(2),body.admin table[data-table="staff"] th:nth-child(2),body.admin .staff table.table th:nth-child(2){max-width:170px!important;width:170px!important;}\n';
    echo 'body.admin table.staff td:nth-child(2),body.admin table.table-staff td:nth-child(2),body.admin table[data-table="staff"] td:nth-child(2),body.admin .staff table.table td:nth-child(2){max-width:170px!important;width:170px!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}\n';
    echo 'body.admin table.staff th:nth-child(3),body.admin table.table-staff th:nth-child(3),body.admin table[data-table="staff"] th:nth-child(3),body.admin .staff table.table th:nth-child(3),body.admin table.staff td:nth-child(3),body.admin table.table-staff td:nth-child(3),body.admin table[data-table="staff"] td:nth-child(3),body.admin .staff table.table td:nth-child(3){max-width:118px!important;width:118px!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}\n';
    echo 'body.admin table.staff th:nth-child(4),body.admin table.table-staff th:nth-child(4),body.admin table[data-table="staff"] th:nth-child(4),body.admin .staff table.table th:nth-child(4),body.admin table.staff td:nth-child(4),body.admin table.table-staff td:nth-child(4),body.admin table[data-table="staff"] td:nth-child(4),body.admin .staff table.table td:nth-child(4){max-width:86px!important;width:86px!important;text-align:center!important;white-space:nowrap!important;}\n';
    echo 'body.admin table.staff th:last-child,body.admin table.table-staff th:last-child,body.admin table[data-table="staff"] th:last-child,body.admin .staff table.table th:last-child,body.admin table.staff td:last-child,body.admin table.table-staff td:last-child,body.admin table[data-table="staff"] td:last-child,body.admin .staff table.table td:last-child{min-width:190px!important;width:190px!important;white-space:nowrap!important;text-align:right!important;}\n';
    echo 'body.admin table.staff td .row-options,body.admin table.table-staff td .row-options,body.admin table[data-table="staff"] td .row-options,body.admin .staff table.table td .row-options{display:inline-flex!important;gap:7px!important;align-items:center!important;white-space:nowrap!important;}\n';
    echo 'body.admin table.staff img,body.admin table.table-staff img,body.admin table[data-table="staff"] img,body.admin .staff table.table img,body.admin .staff-profile-image-small,body.admin img.staff-profile-image-small{width:42px!important;height:42px!important;min-width:42px!important;max-width:42px!important;object-fit:cover!important;border-radius:50%!important;margin:0 12px 0 0!important;vertical-align:middle!important;display:inline-block!important;overflow:visible!important;}\n';
    echo 'body.customers .navbar,body.authentication .navbar,body.customers .navbar-default,body.authentication .navbar-default{background:linear-gradient(90deg,#2CA8FF 0%,#0077CC 45%,#F96302 100%)!important;border:0!important;min-height:72px!important;}\n';
    echo 'body.customers .navbar .container,body.authentication .navbar .container{display:flex!important;align-items:center!important;min-height:72px!important;width:100%!important;max-width:100%!important;}\n';
    echo 'body.customers .navbar-header,body.authentication .navbar-header{display:flex!important;align-items:center!important;min-height:72px!important;float:none!important;margin:0!important;}\n';
    echo 'body.customers .navbar-brand,body.authentication .navbar-brand{display:flex!important;align-items:center!important;height:72px!important;padding:0 14px!important;margin:0!important;}\n';
    echo 'body.customers .navbar-collapse,body.authentication .navbar-collapse{flex:1 1 auto!important;border:0!important;box-shadow:none!important;}\n';
    echo 'body.customers .navbar-nav,body.authentication .navbar-nav{display:flex!important;align-items:center!important;gap:4px!important;float:none!important;margin:0!important;}\n';
    echo 'body.customers .navbar-nav.navbar-right,body.authentication .navbar-nav.navbar-right{margin-left:auto!important;}\n';
    echo 'body.customers .navbar-nav>li,body.authentication .navbar-nav>li{float:none!important;display:flex!important;align-items:center!important;}\n';
    echo 'body.customers .navbar-nav>li>a,body.authentication .navbar-nav>li>a{min-height:40px!important;padding:9px 12px!important;display:flex!important;align-items:center!important;justify-content:center!important;color:#fff!important;font-weight:700!important;line-height:1.15!important;}\n';
    echo '@media(min-width:768px){body.customers .navbar-toggle,body.authentication .navbar-toggle,body.customers button.navbar-toggle,body.authentication button.navbar-toggle{display:none!important;visibility:hidden!important;} body.customers .navbar-collapse.collapse,body.authentication .navbar-collapse.collapse{display:flex!important;height:auto!important;padding:0!important;overflow:visible!important;} body.customers .navbar-nav>li:last-child,body.authentication .navbar-nav>li:last-child{margin-left:auto!important;}}\n';
    echo '@media(max-width:767px){html,body{width:100%!important;max-width:100%!important;overflow-x:hidden!important;} body.customers,body.authentication{font-size:15px!important;} body.customers .navbar,body.authentication .navbar,body.customers .navbar-default,body.authentication .navbar-default{min-height:64px!important;} body.customers .navbar .container,body.authentication .navbar .container{display:block!important;min-height:64px!important;padding-left:10px!important;padding-right:10px!important;} body.customers .navbar-header,body.authentication .navbar-header{display:flex!important;align-items:center!important;justify-content:space-between!important;min-height:64px!important;width:100%!important;} body.customers .navbar-brand,body.authentication .navbar-brand{height:64px!important;max-width:calc(100% - 62px)!important;padding:0!important;} body.customers .navbar-brand img,body.authentication .navbar-brand img{height:58px!important;max-height:58px!important;max-width:100%!important;object-fit:contain!important;} body.customers .navbar-toggle,body.authentication .navbar-toggle{display:flex!important;align-items:center!important;justify-content:center!important;flex-direction:column!important;width:46px!important;height:46px!important;margin:9px 0 9px 8px!important;padding:8px!important;background:rgba(255,255,255,.95)!important;border:1px solid rgba(255,255,255,.9)!important;border-radius:12px!important;float:none!important;} body.customers .navbar-toggle .icon-bar,body.authentication .navbar-toggle .icon-bar{display:block!important;width:24px!important;height:3px!important;border-radius:3px!important;background:#0077CC!important;margin:3px 0!important;} body.customers .navbar-collapse,body.authentication .navbar-collapse{display:none!important;width:100%!important;background:#fff!important;border-radius:0 0 16px 16px!important;padding:10px!important;margin:0!important;box-shadow:0 14px 30px rgba(15,23,42,.16)!important;} body.customers .navbar-collapse.in,body.authentication .navbar-collapse.in,body.customers .navbar-collapse.collapse.in,body.authentication .navbar-collapse.collapse.in{display:block!important;height:auto!important;overflow:visible!important;} body.customers .navbar-nav,body.authentication .navbar-nav{display:block!important;width:100%!important;margin:0!important;} body.customers .navbar-nav>li,body.authentication .navbar-nav>li{display:block!important;width:100%!important;} body.customers .navbar-nav>li>a,body.authentication .navbar-nav>li>a{display:flex!important;justify-content:flex-start!important;width:100%!important;color:#111!important;background:#F5F5F5!important;margin:5px 0!important;border-left:4px solid #2CA8FF!important;border-radius:10px!important;font-size:15px!important;} body.customers .navbar-nav>li>a:hover,body.authentication .navbar-nav>li>a:hover{background:#EAF7FF!important;color:#0077CC!important;} body.customers .container,body.authentication .container,body.customers .content,body.authentication .content,body.customers .panel_s,body.authentication .panel_s{width:100%!important;max-width:100%!important;margin-left:0!important;margin-right:0!important;padding-left:10px!important;padding-right:10px!important;} body.customers table,body.authentication table{width:100%!important;font-size:13px!important;} body.customers .table-responsive,body.authentication .table-responsive{width:100%!important;overflow-x:auto!important;} }\n';
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
        if ($color == '#0077CC') {
            $colors[$key] = '#0077CC';
        }
        if ($color == '#d81b60') {
            $colors[$key] = '#00A651';
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
