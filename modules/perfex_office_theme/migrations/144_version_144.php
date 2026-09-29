<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_144 extends App_module_migration
{
    public function up()
    {
        $options = [
            'smart_choice_theme_compact_mode' => '1',
            'smart_choice_theme_force_white_hamburger' => '1',
            'smart_choice_theme_login_button_width' => '190',
            'smart_choice_theme_progress_height' => '8',
            'smart_choice_theme_remove_nested_scrollbars' => '1',
            'smart_choice_theme_terms_link_color' => '#8e24aa',
            'smart_choice_theme_development_notice_bg' => '#fff4dc',
            'smart_choice_theme_development_notice_text' => '#8a4b00',
        ];
        foreach ($options as $name => $value) {
            if (get_option($name) === '') {
                add_option($name, $value);
            } else {
                update_option($name, $value);
            }
        }
        update_option('perfex_office_theme_module_version', '1.4.4');
        return true;
    }
    public function down()
    {
        return true;
    }
}
