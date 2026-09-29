<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        if (function_exists('smart_choice_links_ensure_database')) {
            smart_choice_links_ensure_database();
        }
        $defaults = [
            'smart_choice_links_panel_min_width' => '220',
            'smart_choice_links_panel_max_width' => '500',
            'smart_choice_links_padding' => '8',
            'smart_choice_links_radius' => '8',
            'smart_choice_links_animation_speed' => '180',
            'smart_choice_links_enable_command_palette' => '1',
            'smart_choice_links_enable_setup_shortcut' => '1',
        ];
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false || get_option($key) === '') {
                add_option($key, $value);
            }
        }
        update_option('smart_choice_links_version', '1.1.0');
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}
