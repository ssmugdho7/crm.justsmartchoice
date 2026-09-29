<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_106 extends App_module_migration
{
    public function up(): void
    {
        require_once module_dir_path('usi_smartchoice_seo', 'helpers/usi_smartchoice_seo_helper.php');
        usi_smartchoice_seo_ensure_schema();
        if (function_exists('update_option')) {
            update_option('usi_smartchoice_seo_primary_domain', 'https://justsmartchoice.com');
            update_option('usi_smartchoice_seo_brand_name', 'Smart Choice Contractors USA');
        }
    }

    public function down(): void
    {
    }
}
