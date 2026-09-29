<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_136 extends App_module_migration
{
    public function up()
    {
        $options = [
            'smart_choice_theme_sidebar_fixed' => '1',
            'smart_choice_theme_base_font_size' => '14',
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
