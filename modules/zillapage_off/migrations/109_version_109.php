<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_109 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        require_once(__DIR__ . '/../install.php');
        if (file_exists(__DIR__ . '/../libraries/Smart_choice_template_seeder.php')) {
            require_once(__DIR__ . '/../libraries/Smart_choice_template_seeder.php');
            zillapage_smart_choice_ensure_settings();
            zillapage_smart_choice_seed_templates(false);
        }
        update_option('zillapage_module_version', '1.1.4');
    }
}
