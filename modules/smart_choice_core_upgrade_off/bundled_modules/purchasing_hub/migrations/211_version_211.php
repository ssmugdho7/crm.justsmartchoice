<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_211 extends App_module_migration
{
    public function up()
    {
        require_once(module_dir_path('purchasing_hub') . 'install.php');
        $CI = &get_instance();
        update_option('purchasing_hub_last_modified', '2026-06-30');
        update_option('purchasing_hub_quality_rating', '5');
        $dirs = ['uploads/import_item_error/','uploads/import_vendor_error/','uploads/file_sample/'];
        foreach ($dirs as $dir) {
            $path = module_dir_path('purchasing_hub') . $dir;
            if (!is_dir($path)) { @mkdir($path, 0755, true); }
        }
    }
}
