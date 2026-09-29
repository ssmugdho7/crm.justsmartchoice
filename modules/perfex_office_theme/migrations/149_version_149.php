<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_149 extends App_module_migration
{
    public function up()
    {
        $defaults = [
            'smart_choice_theme_primary_color' => '#0077CC',
            'smart_choice_theme_light_blue_color' => '#2CA8FF',
            'smart_choice_theme_orange_color' => '#F96302',
            'smart_choice_theme_deep_orange_color' => '#F5B400',
            'smart_choice_theme_green_color' => '#00A651',
            'smart_choice_theme_soft_background' => '#F5F5F5',
            'smart_choice_theme_trim_color' => '#0077CC',
            'smart_choice_theme_gradient_start' => '#2CA8FF',
            'smart_choice_theme_gradient_end' => '#F96302',
            'smart_choice_theme_client_menu_bg' => '#0077CC',
            'smart_choice_theme_client_menu_text' => '#FFFFFF',
            'smart_choice_theme_client_menu_hover_bg' => '#EAF7FF',
            'smart_choice_theme_client_menu_hover_text' => '#0077CC',
            'smart_choice_theme_top_bar_admin' => '#0077CC',
            'smart_choice_theme_component_bar' => '#EAF7FF',
            'smart_choice_theme_nav_logo_height' => '64',
            'smart_choice_theme_topbar_height' => '68',
            'smart_choice_theme_mobile_nav_height' => '72',
            'smart_choice_theme_table_fullname_width' => '210',
            'smart_choice_theme_table_email_width' => '190',
        ];

        foreach ($defaults as $name => $value) {
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
