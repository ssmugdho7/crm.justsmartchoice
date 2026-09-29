<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_202 extends App_module_migration
{
    public function up()
    {
        $options = [
            'toast_master_animation' => 'fade',
            'toast_master_version' => '2.0.2',
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
        // Safe rollback: preserve notification preferences.
    }
}
