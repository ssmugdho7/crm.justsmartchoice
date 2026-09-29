<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_205 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        if (get_option('appointly_smart_choice_version') === false) {
            add_option('appointly_smart_choice_version', '2.1.2');
        } else {
            update_option('appointly_smart_choice_version', '2.1.2');
        }
        $defaults = [
            'appointly_sc_show_public_logo' => '1',
            'appointly_sc_public_logo_width' => '110',
            'appointly_sc_public_logo_height' => '38',
            'appointly_sc_compact_ui_enabled' => '1',
            'appointly_sc_single_scroll_enabled' => '1',
        ];
        foreach ($defaults as $name => $value) {
            if (get_option($name) === false) { add_option($name, $value); }
        }
    }
}
