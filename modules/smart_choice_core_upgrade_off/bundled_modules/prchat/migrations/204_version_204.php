<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_204 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (function_exists('add_option')) {
            add_option('prchat_max_upload_size_kb', '204800');
        }

        if (function_exists('update_option')) {
            update_option('prchat_last_upload_fix_version', '2.0.4');
        }

        $uploadFolders = [
            module_dir_path('prchat', 'uploads'),
            module_dir_path('prchat', 'uploads/groups'),
            module_dir_path('prchat', 'uploads/audio'),
            module_dir_path('prchat', 'uploads/audio/staff'),
            module_dir_path('prchat', 'uploads/audio/clients'),
            FCPATH . 'uploads/media/projects',
            FCPATH . 'uploads/media/projects/chat_groups',
        ];

        foreach ($uploadFolders as $folder) {
            if (!is_dir($folder)) {
                @mkdir($folder, 0755, true);
            }
            if (is_dir($folder) && !file_exists(rtrim($folder, '/') . '/index.html')) {
                @file_put_contents(rtrim($folder, '/') . '/index.html', '');
            }
        }
    }
}
