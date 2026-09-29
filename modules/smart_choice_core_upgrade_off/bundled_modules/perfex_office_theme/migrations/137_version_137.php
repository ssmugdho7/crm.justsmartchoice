<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_137 extends App_module_migration
{
    public function up()
    {
        $options = [
            'perfex_office_theme_customers' => '1',
            'smart_choice_theme_client_menu_bg' => '#169179',
            'smart_choice_theme_client_menu_text' => '#ffffff',
            'smart_choice_theme_client_icon_color' => '#f7941d',
            'smart_choice_theme_client_menu_hover_bg' => '#eaf4ff',
            'smart_choice_theme_client_menu_hover_text' => '#0057b8',
            'smart_choice_theme_client_menu_shadow' => '0',
            'smart_choice_theme_client_text_shadow' => 'none',
            'smart_choice_theme_light_blue_color' => '#eaf4ff',
            'smart_choice_theme_green_color' => '#169179',
            'smart_choice_theme_orange_color' => '#f47c20',
            'smart_choice_theme_primary_color' => '#0057b8',
            'smart_choice_theme_sidebar_fixed' => '1',
            'smart_choice_theme_button_radius' => '10',
            'smart_choice_theme_admin_hover_radius' => '10',
        ];

        foreach ($options as $name => $value) {
            if (get_option($name) === '') {
                add_option($name, $value);
            } else {
                update_option($name, $value);
            }
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
