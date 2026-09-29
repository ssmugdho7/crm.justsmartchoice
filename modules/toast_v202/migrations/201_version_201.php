<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_201 extends App_module_migration
{
    public function up()
    {
        $options = [
            'toast_master_sound_enable' => '1',
            'toast_master_volume' => '80',
            'toast_master_intercept_browser_alert' => '1',
            'toast_master_version' => '2.0.1',
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
        // Safe rollback: leave settings intact.
    }
}
