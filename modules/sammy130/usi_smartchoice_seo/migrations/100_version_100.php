<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_100 extends App_module_migration
{
    public function up(): void
    {
        require_once module_dir_path('usi_smartchoice_seo', 'install.php');
    }

    public function down(): void
    {
        $CI = &get_instance();
        $CI->load->dbforge();
        $CI->dbforge->drop_table(db_prefix() . 'usi_seo_reports', true);
        $CI->dbforge->drop_table(db_prefix() . 'usi_seo_keywords', true);
        $CI->dbforge->drop_table(db_prefix() . 'usi_seo_pages', true);
    }
}
