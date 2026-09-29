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
    'smart_choice_theme_primary_color' => '#0b5cab',
    'smart_choice_theme_light_blue_color' => '#2ea8ff',
    'smart_choice_theme_orange_color' => '#f7941d',
    'smart_choice_theme_deep_orange_color' => '#d96f00',
    'smart_choice_theme_green_color' => '#169179',
    'smart_choice_theme_soft_background' => '#f8fafc',
    'smart_choice_theme_trim_color' => '#0057b8',
    'smart_choice_theme_gradient_start' => '#0057b8',
    'smart_choice_theme_gradient_end' => '#169179',
    'smart_choice_theme_admin_menu_bg' => '#ffffff',
    'smart_choice_theme_admin_menu_text' => '#263238',
    'smart_choice_theme_admin_menu_hover_bg' => '#eaf4ff',
    'smart_choice_theme_admin_menu_hover_text' => '#0b5cab',
    'smart_choice_theme_admin_icon_color' => '#0057b8',
    'smart_choice_theme_admin_menu_font_size' => '14',
    'smart_choice_theme_admin_menu_shadow' => '0',
    'smart_choice_theme_admin_text_shadow' => 'none',
    'smart_choice_theme_admin_hover_radius' => '8',
    'smart_choice_theme_client_menu_bg' => '#0057b8',
    'smart_choice_theme_client_menu_text' => '#ffffff',
    'smart_choice_theme_client_menu_hover_bg' => '#eaf4ff',
    'smart_choice_theme_client_menu_hover_text' => '#0057b8',
    'smart_choice_theme_client_icon_color' => '#0057b8',
    'smart_choice_theme_client_menu_font_size' => '14',
    'smart_choice_theme_client_menu_shadow' => '0',
    'smart_choice_theme_client_text_shadow' => 'none',
    'smart_choice_theme_trim_primary' => '#0057b8',
    'smart_choice_theme_trim_secondary' => '#0b5cab',
    'smart_choice_theme_top_bar_admin' => '#0b5cab',
    'smart_choice_theme_top_bar_client' => '#0057b8',
    'smart_choice_theme_sidebar_bar' => '#123b63',
    'smart_choice_theme_component_bar' => '#0057b8',
];

foreach ($options as $name => $value) {
    if (get_option($name) === '') {
        add_option($name, $value);
    }
}
