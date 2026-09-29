<?php

defined('BASEPATH') or exit('No direct script access allowed');

$options = [
    'perfex_office_theme_customers' => '1',
    'smart_choice_theme_admin_enabled' => '1',
    'smart_choice_theme_sidebar_fixed' => '1',
    'smart_choice_theme_brand_palette' => 'smart_choice_orange_office',
    'smart_choice_theme_base_font_size' => '14',
    'smart_choice_theme_card_radius' => '12',
    'smart_choice_theme_button_radius' => '8',
    'smart_choice_theme_primary_color' => '#0077CC',
    'smart_choice_theme_light_blue_color' => '#2CA8FF',
    'smart_choice_theme_orange_color' => '#F96302',
    'smart_choice_theme_deep_orange_color' => '#E59A00',
    'smart_choice_theme_green_color' => '#00A651',
    'smart_choice_theme_soft_background' => '#F5F5F5',
    'smart_choice_theme_trim_color' => '#0077CC',
    'smart_choice_theme_gradient_start' => '#2CA8FF',
    'smart_choice_theme_gradient_end' => '#F96302',
    'smart_choice_theme_admin_menu_bg' => '#ffffff',
    'smart_choice_theme_admin_menu_text' => '#111111',
    'smart_choice_theme_admin_menu_hover_bg' => '#EAF7FF',
    'smart_choice_theme_admin_menu_hover_text' => '#0077CC',
    'smart_choice_theme_admin_icon_color' => '#0077CC',
    'smart_choice_theme_admin_menu_font_size' => '14',
    'smart_choice_theme_admin_menu_shadow' => '0',
    'smart_choice_theme_admin_text_shadow' => 'none',
    'smart_choice_theme_admin_hover_radius' => '8',
    'smart_choice_theme_client_menu_bg' => '#0077CC',
    'smart_choice_theme_client_menu_text' => '#ffffff',
    'smart_choice_theme_client_menu_hover_bg' => '#EAF7FF',
    'smart_choice_theme_client_menu_hover_text' => '#0077CC',
    'smart_choice_theme_client_icon_color' => '#0077CC',
    'smart_choice_theme_client_menu_font_size' => '14',
    'smart_choice_theme_client_menu_shadow' => '0',
    'smart_choice_theme_client_text_shadow' => 'none',
    'smart_choice_theme_trim_primary' => '#0077CC',
    'smart_choice_theme_trim_secondary' => '#0077CC',
    'smart_choice_theme_top_bar_admin' => '#0077CC',
    'smart_choice_theme_top_bar_client' => '#0077CC',
    'smart_choice_theme_sidebar_bar' => '#005FAF',
    'smart_choice_theme_component_bar' => '#0077CC',

    'smart_choice_theme_compact_mode' => '1',
    'smart_choice_theme_force_white_hamburger' => '1',
    'smart_choice_theme_login_button_width' => '190',
    'smart_choice_theme_progress_height' => '8',
    'smart_choice_theme_remove_nested_scrollbars' => '1',
    'smart_choice_theme_terms_link_color' => '#0077CC',
    'smart_choice_theme_development_notice_bg' => '#FFF8E1',
    'smart_choice_theme_development_notice_text' => '#8A4B00',

    'smart_choice_theme_nav_logo_height' => '64',
    'smart_choice_theme_topbar_height' => '66',
    'smart_choice_theme_hamburger_size' => '22',
    'smart_choice_theme_dropdown_max_height' => '360',
    'smart_choice_theme_select_dropdown_max_height' => '320',
    'smart_choice_theme_table_fullname_width' => '170',
    'smart_choice_theme_table_email_width' => '180',
    'smart_choice_theme_mobile_nav_height' => '72',
    'smart_choice_theme_client_login_button_height' => '36',
    'smart_choice_theme_force_gradient_white_text' => '1',
    'smart_choice_theme_saved_profile' => '',
];

foreach ($options as $name => $value) {
    if (get_option($name) === '') {
        add_option($name, $value);
    }
}
