<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_139 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_office_theme_version', '1.3.9');

        $options = [
            'smart_choice_theme_client_menu_bg' => '#0057b8',
            'smart_choice_theme_client_menu_hover_bg' => '#eaf4ff',
            'smart_choice_theme_client_menu_hover_text' => '#0057b8',
            'smart_choice_theme_client_icon_color' => '#eaf4ff',
            'smart_choice_theme_admin_arrow_color' => '#123b63',
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
