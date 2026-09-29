<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_214 extends App_module_migration
{
    public function up()
    {
        $options = [
            'appointly_sc_public_logo_width' => '82',
            'appointly_sc_public_logo_height' => '30',
            'appointly_sc_show_public_logo' => '1',
            'appointly_sc_daily_popup_enabled' => '1',
            'appointly_sc_sound_enabled' => '1',
        ];

        foreach ($options as $name => $value) {
            if (get_option($name) === '') {
                add_option($name, $value);
            }
        }
    }
}
