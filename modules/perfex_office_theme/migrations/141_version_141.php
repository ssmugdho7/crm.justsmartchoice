<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_141 extends App_module_migration
{
    public function up()
    {
        $updates = [
            'smart_choice_theme_gradient_start' => '#0057b8',
            'smart_choice_theme_gradient_end' => '#169179',
            'smart_choice_theme_admin_menu_hover_bg' => '#eaf4ff',
            'smart_choice_theme_admin_icon_color' => '#0057b8',
            'smart_choice_theme_client_menu_bg' => '#0057b8',
            'smart_choice_theme_client_icon_color' => '#0057b8',
            'smart_choice_theme_client_menu_hover_bg' => '#eaf4ff',
            'smart_choice_theme_client_menu_hover_text' => '#0057b8',
            'smart_choice_theme_trim_color' => '#0057b8',
            'smart_choice_theme_trim_primary' => '#0057b8',
            'smart_choice_theme_trim_secondary' => '#169179',
            'smart_choice_theme_sidebar_bar' => '#123b63',
            'smart_choice_theme_component_bar' => '#0057b8',
            'smart_choice_theme_top_bar_admin' => '#0057b8',
            'smart_choice_theme_top_bar_client' => '#0057b8',
        ];

        foreach ($updates as $name => $value) {
            update_option($name, $value);
        }

        update_option('perfex_office_theme_module_version', '1.4.1');
        return true;
    }

    public function down()
    {
        return true;
    }
}
