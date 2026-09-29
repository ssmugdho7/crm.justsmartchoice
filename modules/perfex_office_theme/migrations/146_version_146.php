<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_146 extends App_module_migration
{
    public function up()
    {
        $options = [
            'smart_choice_theme_primary_color' => '#0077CC',
            'smart_choice_theme_light_blue_color' => '#2CA8FF',
            'smart_choice_theme_orange_color' => '#F96302',
            'smart_choice_theme_deep_orange_color' => '#E59A00',
            'smart_choice_theme_green_color' => '#00A651',
            'smart_choice_theme_soft_background' => '#F5F5F5',
            'smart_choice_theme_trim_color' => '#0077CC',
            'smart_choice_theme_gradient_start' => '#0077CC',
            'smart_choice_theme_gradient_end' => '#00A651',
            'smart_choice_theme_nav_logo_height' => '58',
            'smart_choice_theme_topbar_height' => '66',
            'smart_choice_theme_terms_link_color' => '#0077CC',
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
