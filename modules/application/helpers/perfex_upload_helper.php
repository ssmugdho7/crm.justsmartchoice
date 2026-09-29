<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Handle file uploads for Perfex CRM
 * Fixes: "You can't upload files of this type" issue
 */

if (!function_exists('handle_file_upload')) {
    function handle_file_upload($field, $path, $allowed_extensions = false)
    {
        $CI = &get_instance();
        $CI->load->library('upload');

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        // ✅ Allowed file types
        $config['upload_path']   = $path;
        $config['max_size']      = 51200; // 50MB
        $config['allowed_types'] = 'avi|bmp|crd|csv|dfx|doc|docx|dwg|gif|heic|htm|html|jpeg|jpg|layout|m4a|mov|mp3|mp4|pdf|php|png|ppt|pptx|psd|rar|skp|svg|tiff|txt|wav|webm|xls|xlsx|xml|zip';
        $config['overwrite']     = false;
        $config['remove_spaces'] = true;

        $CI->upload->initialize($config);

        if (!$CI->upload->do_upload($field)) {
            return ['error' => $CI->upload->display_errors('', '')];
        } else {
            return ['success' => $CI->upload->data()];
        }
    }
}
