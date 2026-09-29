<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_147 extends App_module_migration
{
    public function up()
    {
        $options = [
            'perfex_office_theme_customers' => '1',
            'smart_choice_theme_admin_enabled' => '1',
            'smart_choice_theme_primary_color' => '#0077CC',
            'smart_choice_theme_light_blue_color' => '#2CA8FF',
            'smart_choice_theme_orange_color' => '#F96302',
            'smart_choice_theme_deep_orange_color' => '#E59A00',
            'smart_choice_theme_green_color' => '#00A651',
            'smart_choice_theme_soft_background' => '#F5F5F5',
            'smart_choice_theme_trim_color' => '#0077CC',
            'smart_choice_theme_gradient_start' => '#0077CC',
            'smart_choice_theme_gradient_end' => '#00A651',
            'smart_choice_theme_admin_menu_bg' => '#ffffff',
            'smart_choice_theme_admin_menu_text' => '#111111',
            'smart_choice_theme_admin_menu_hover_bg' => '#EAF7FF',
            'smart_choice_theme_admin_menu_hover_text' => '#0077CC',
            'smart_choice_theme_admin_icon_color' => '#0077CC',
            'smart_choice_theme_client_menu_bg' => '#0077CC',
            'smart_choice_theme_client_menu_text' => '#ffffff',
            'smart_choice_theme_client_menu_hover_bg' => '#EAF7FF',
            'smart_choice_theme_client_menu_hover_text' => '#0077CC',
            'smart_choice_theme_client_icon_color' => '#0077CC',
            'smart_choice_theme_trim_primary' => '#0077CC',
            'smart_choice_theme_trim_secondary' => '#0077CC',
            'smart_choice_theme_top_bar_admin' => '#0077CC',
            'smart_choice_theme_top_bar_client' => '#0077CC',
            'smart_choice_theme_sidebar_bar' => '#005FAF',
            'smart_choice_theme_component_bar' => '#0077CC',
            'smart_choice_theme_terms_link_color' => '#0077CC',
            'smart_choice_theme_development_notice_bg' => '#FFF8E1',
            'smart_choice_theme_development_notice_text' => '#8A4B00',
            'smart_choice_theme_nav_logo_height' => '58',
            'smart_choice_theme_topbar_height' => '66',
            'smart_choice_theme_mobile_nav_height' => '64',
            'smart_choice_theme_client_login_button_height' => '40',
            'smart_choice_theme_force_white_hamburger' => '1',
            'smart_choice_theme_remove_nested_scrollbars' => '1',
            'smart_choice_theme_force_gradient_white_text' => '1',
        ];

        foreach ($options as $name => $value) {
            if (get_option($name) === '') {
                add_option($name, $value);
            } else {
                update_option($name, $value);
            }
        }
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
