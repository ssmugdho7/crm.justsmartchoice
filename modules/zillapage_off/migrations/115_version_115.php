<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_115 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        require_once(module_dir_path('zillapage') . 'libraries/Smart_choice_template_seeder.php');
        zillapage_smart_choice_ensure_settings();
        zillapage_smart_choice_seed_templates(true);
        update_option('zillapage_module_version', '1.1.5');
        update_option('zillapage_smart_choice_templates_version', '1.1.5');
        update_option('zillapage_landing_media_url', 'https://justsmartchoice.com/images/landing-pages/');
    }
}
