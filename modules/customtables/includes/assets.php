<?php

defined('BASEPATH') || exit('No direct script access allowed');

hooks()->add_action('app_admin_head', 'customtables_add_head_components');
function customtables_add_head_components() {
    if (get_instance()->app_modules->is_active('customtables')) {
        echo '<link href="' . module_dir_url('customtables', 'assets/css/customtables.css') . '?v=' . get_instance()->app_scripts->core_version() . '" rel="stylesheet" type="text/css" />';
        if (function_exists('table_custom_style_render')) { table_custom_style_render(); }
        if (function_exists('table_custom_css_render')) { table_custom_css_render('custom_css_for_table'); }
        echo '<script>window.customtables_a=window.customtables_a||\'smartchoice\';window.customtables_b=window.customtables_b||\'smartchoice\';window.customtables_g=window.customtables_g||\'smartchoice\';window.customtables_r=window.customtables_r||\'\';var customtablesSmartChoice=true;</script>';
    }
}

hooks()->add_action('before_js_scripts_render', 'before_load_js');
function before_load_js() {
    if (get_instance()->app_modules->is_active('customtables')) {
        echo '<script>var hidden_columns = [];</script>';
        echo '<script src="' . module_dir_url('customtables', 'assets/js/init_customtables.js') . '?v=' . get_instance()->app_scripts->core_version() . '"></script>';
    }
}

hooks()->add_action('app_admin_footer', 'customtables_load_js');
function customtables_load_js() {
    if (get_instance()->app_modules->is_active('customtables')) {
        echo '<script src="' . module_dir_url('customtables', 'assets/js/customtables.bundle.js') . '?v=' . get_instance()->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('customtables', 'assets/js/table_design.js') . '?v=' . get_instance()->app_scripts->core_version() . '"></script>';
    }
}
