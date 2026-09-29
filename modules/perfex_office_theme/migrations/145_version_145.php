<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_145 extends App_module_migration
{
    public function up()
    {
        $options = [
            'smart_choice_theme_nav_logo_height' => '46',
            'smart_choice_theme_topbar_height' => '62',
            'smart_choice_theme_hamburger_size' => '22',
            'smart_choice_theme_dropdown_max_height' => '360',
            'smart_choice_theme_select_dropdown_max_height' => '320',
            'smart_choice_theme_table_fullname_width' => '170',
            'smart_choice_theme_table_email_width' => '180',
            'smart_choice_theme_mobile_nav_height' => '64',
            'smart_choice_theme_client_login_button_height' => '36',
            'smart_choice_theme_force_gradient_white_text' => '1',
            'smart_choice_theme_saved_profile' => '',
        ];

        foreach ($options as $name => $value) {
            if (get_option($name) === '') {
                add_option($name, $value);
            }
        }
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}
