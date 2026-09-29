<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        if (!function_exists('smart_choice_links_ensure_database')) {
            require_once(module_dir_path('smart_choice_links', 'smart_choice_links.php'));
        }

        smart_choice_links_ensure_database();

        $defaults = [
            'smart_choice_links_panel_width' => '285',
            'smart_choice_links_row_height' => '32',
            'smart_choice_links_icon_size' => '14',
            'smart_choice_links_font_size' => '12',
            'smart_choice_links_padding' => '8',
            'smart_choice_links_radius' => '9',
            'smart_choice_links_animation_speed' => '160',
            'smart_choice_links_enable_menu_search' => '0',
            'smart_choice_links_enable_command_palette' => '0',
            'smart_choice_links_version' => '1.1.1',
        ];

        foreach ($defaults as $name => $value) {
            if (get_option($name) === false) {
                add_option($name, $value);
            } else {
                update_option($name, $value);
            }
        }
    }


    public function down()
    {
        // Safe non-destructive rollback. This method is required by Perfex module migrations.
    }
}
