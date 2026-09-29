<?php
/**
 * Replace media URL
 *
 * @param  int $template
 *
 * @return array
 */
if (!function_exists('perfexPopupReplaceVarContentStyle')) {

    function perfexPopupReplaceVarContentStyle($item=""){
        // Image URL: ##image_url##
        $results = array();
        $image_url = base_url(PERFEX_POPUP_UPLOAD_PATH.'/content_media')."/";
        $temp = $item;
        if (is_object($item)) {
            if (isset($item->html)) {
                $temp->html = str_replace('##image_url##', $image_url, $item->html);
            }
            if (isset($item->css)) {
                $temp->css = str_replace('##image_url##', $image_url, $item->css);
            }

            if (isset($item->html_components)) {
                $temp->html_components = str_replace('##image_url##', $image_url, $item->html_components);
            }
            if (isset($item->css_styles)) {
                $temp->css_styles = str_replace('##image_url##', $image_url, $item->css_styles);
            }

            if (isset($item->thank_you_html)) {
                $temp->thank_you_html = str_replace('##image_url##', $image_url, $item->thank_you_html);
            }
            if (isset($item->thank_you_css)) {
                $temp->thank_you_css = str_replace('##image_url##', $image_url, $item->thank_you_css);
            }
            if (isset($item->thank_you_html_components)) {
                $temp->thank_you_html_components = str_replace('##image_url##', $image_url, $item->thank_you_html_components);
            }
            if (isset($item->thank_you_css_styles)) {
                $temp->thank_you_css_styles = str_replace('##image_url##', $image_url, $item->thank_you_css_styles);
            }
            
            
        }
        elseif(is_array($item)){
            if (isset($item['html'])) {
                $temp['html'] = str_replace($image_url, '##image_url##', $item['html']);
            }
            if (isset($item['css'])) {
                $temp['css'] = str_replace($image_url, '##image_url##', $item['css']);
            }
            if (isset($item['html_components'])) {
                $temp['html_components'] = str_replace($image_url, '##image_url##', $item['html_components']);
            }
            if (isset($item['css_styles'])) {
                $temp['css_styles'] = str_replace($image_url, '##image_url##', $item['css_styles']);
            }
            if (isset($item['thank_you_html'])) {
                $temp['thank_you_html'] = str_replace($image_url, '##image_url##', $item['thank_you_html']);
            }
            if (isset($item['thank_you_css'])) {
                $temp['thank_you_css'] = str_replace($image_url, '##image_url##', $item['thank_you_css']);
            }
            if (isset($item['thank_you_html_components'])) {
                $temp['thank_you_html_components'] = str_replace($image_url, '##image_url##', $item['thank_you_html_components']);
            }
            if (isset($item['thank_you_css_styles'])) {
                $temp['thank_you_css_styles'] = str_replace($image_url, '##image_url##', $item['thank_you_css_styles']);
            }
        }
        else{
            if (isset($item)) {
                $temp = str_replace('##image_url##', $image_url, $item);
            }
        }
        return $temp;
    }
}
if (!function_exists('convertLinkToVarContentStyle')) {

    function convertLinkToVarContentStyle($item=""){
        // Image URL: ##image_url##
        $results = array();
        $image_url = base_url(PERFEX_POPUP_UPLOAD_PATH.'/content_media')."/";
        $temp = $item;
        
        if (is_object($item)) {
            if (isset($item->html)) {
                $temp->html = str_replace($image_url, '##image_url##', $item->html);
            }
            if (isset($item->css)) {
                $temp->css = str_replace($image_url, '##image_url##', $item->css);
            }
            if (isset($item->html_components)) {
                $temp->html_components = str_replace($image_url, '##image_url##', $item->html_components);
            }
            if (isset($item->css_styles)) {
                $temp->css_styles = str_replace($image_url, '##image_url##', $item->css_styles);
            }
            if (isset($item->thank_you_html)) {
                $temp->thank_you_html = str_replace($image_url, '##image_url##', $item->thank_you_html);
            }
            if (isset($item->thank_you_css)) {
                $temp->thank_you_css = str_replace($image_url, '##image_url##', $item->thank_you_css);
            }
            if (isset($item->thank_you_html_components)) {
                $temp->thank_you_html_components = str_replace($image_url, '##image_url##', $item->thank_you_html_components);
            }
            if (isset($item->thank_you_css_styles)) {
                $temp->thank_you_css_styles = str_replace($image_url, '##image_url##', $item->thank_you_css_styles);
            }
        }elseif(is_array($item)){
            if (isset($item['html'])) {
                $temp['html'] = str_replace($image_url, '##image_url##', $item['html']);
            }
            if (isset($item['css'])) {
                $temp['css'] = str_replace($image_url, '##image_url##', $item['css']);
            }
            if (isset($item['html_components'])) {
                $temp['html_components'] = str_replace($image_url, '##image_url##', $item['html_components']);
            }
            if (isset($item['css_styles'])) {
                $temp['css_styles'] = str_replace($image_url, '##image_url##', $item['css_styles']);
            }
            if (isset($item['thank_you_html'])) {
                $temp['thank_you_html'] = str_replace($image_url, '##image_url##', $item['thank_you_html']);
            }
            if (isset($item['thank_you_css'])) {
                $temp['thank_you_css'] = str_replace($image_url, '##image_url##', $item['thank_you_css']);
            }
            if (isset($item['thank_you_html_components'])) {
                $temp['thank_you_html_components'] = str_replace($image_url, '##image_url##', $item['thank_you_html_components']);
            }
            if (isset($item['thank_you_css_styles'])) {
                $temp['thank_you_css_styles'] = str_replace($image_url, '##image_url##', $item['thank_you_css_styles']);
            }
        }
        else{
            if (isset($item)) {
                $temp = str_replace($image_url, '##image_url##', $item);
            }
        }
        return $temp;
    }
}

if ( ! function_exists('guidV4'))
{
    /**
     * guidV4 ()
     * -------------------------------------------------------------------
     *
     * @return string
     */
    function guidV4()
    {
        // Microsoft guid {xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx}
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        $data = openssl_random_pseudo_bytes(16);

        // set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);

        // set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
} 

if (!function_exists('popup_string_generate')) {

    function popup_string_generate($length){
        
        $characters = str_split('abcdefghijklmnopqrstuvwxyz0123456789');
        $content = '';

        for ($i = 1; $i <= $length; $i++) {
            $content .= $characters[array_rand($characters, 1)];
        }

        return $content;

    }
}

if (!function_exists('getAllImagesContentMedia')) {

    function getAllImagesContentMedia(){
        $dir = FCPATH.PERFEX_POPUP_UPLOAD_PATH."/content_media";
    
        $url_content_media = base_url(PERFEX_POPUP_UPLOAD_PATH.'/content_media');

        $accept = array('jpg', 'svg', 'jpeg', 'png','gif');

        $files = array();    
        foreach (scandir($dir) as $file) {
            $ext = pathinfo($dir . '/' . $file, PATHINFO_EXTENSION);
            if(in_array($ext, $accept)){
                $files[$url_content_media.'/'.$file] = filemtime($dir . '/' . $file);
            }
            
        }

        arsort($files);

        $files = array_keys($files);
        

        return ($files) ? $files : false;

    }
}


if (!function_exists('handle_delete_file_perfex_popup')) {

    function handle_delete_file_perfex_popup($file_path = '')
    {
        // delete file 
        if ($file_path) {
            if (file_exists($file_path)) {
                unlink($file_path);
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('perfex_recurse_copy')) {

    function perfex_recurse_copy($src, $dst) { 
  
        // open the source directory
        $dir = opendir($src); 
      
        // Make the destination directory if not exist
        @mkdir($dst); 
      
        // Loop through the files in source directory
        while( $file = readdir($dir) ) { 
      
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) 
                { 
                    // Recursively calling custom copy function
                    // for sub directory 
                    perfex_recurse_copy($src . '/' . $file, $dst . '/' . $file); 
      
                } 
                else { 
                    copy($src . '/' . $file, $dst . '/' . $file); 
                } 
            } 
        } 
      
        closedir($dir);
    } 
}

if (!function_exists('handle_thumb_template_upload')) {

    function handle_thumb_template_upload($file_old = '')
    {
        
        if (isset($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['name'] != '') {
            // Get the temp file path
            $tmpFilePath = $_FILES['thumbnail']['tmp_name'];
            // Make sure we have a filepath
            $path  = FCPATH.PERFEX_POPUP_UPLOAD_PATH."/popup_thumb_templates/";

            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                // Getting file extension
                $path_parts = pathinfo($_FILES['thumbnail']['name']);
                $extension  = $path_parts['extension'];
                $extension  = strtolower($extension);
                // Setup our new file path
                
                $filename    = time(). '.' . $extension;
                $newFilePath = $path . $filename;

                _maybe_create_upload_path($path);
                // Upload the file into the company uploads dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {

                    // delete file old
                    if ($file_old) {
                        $file_path = FCPATH.PERFEX_POPUP_UPLOAD_PATH."/popup_thumb_templates";
                        $path_old = $path.$file_old;
                        if (file_exists($path_old)) {
                            unlink($path_old);
                        }
                    }

                    return $filename;
                }
            }
        }

        return false;
    }
}

if (!function_exists('handle_thumb_block_upload')) {

    function handle_thumb_block_upload($file_old = '')
    {
        
        if (isset($_FILES['thumb']['name']) && $_FILES['thumb']['name'] != '') {
            // Get the temp file path
            $tmpFilePath = $_FILES['thumb']['tmp_name'];
            // Make sure we have a filepath
            $path  = FCPATH.PERFEX_POPUP_ASSETS_PATH."/images/thumb_blocks/";

            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                // Getting file extension
                $path_parts = pathinfo($_FILES['thumb']['name']);
                $extension  = $path_parts['extension'];
                $extension  = strtolower($extension);
                // Setup our new file path
                
                $filename    = time(). '.' . $extension;
                $newFilePath = $path . $filename;

                _maybe_create_upload_path($path);
                // Upload the file into the company uploads dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {

                    // delete file old
                    if ($file_old) {
                        $file_path = FCPATH.PERFEX_POPUP_ASSETS_PATH."/images/thumb_blocks";
                        $path_old = $path.$file_old;
                        if (file_exists($path_old)) {
                            unlink($path_old);
                        }
                    }

                    return $filename;
                }
            }
        }

        return false;
    }
}

if (!function_exists('handle_favicon_perfex_popup_upload')) {

    function handle_favicon_perfex_popup_upload($file_old = '')
    {
        
        if (isset($_FILES['favicon']['name']) && $_FILES['favicon']['name'] != '') {
            // Get the temp file path
            $tmpFilePath = $_FILES['favicon']['tmp_name'];
            // Make sure we have a filepath
            $path  = FCPATH.PERFEX_POPUP_ASSETS_PATH."/images/uploads/";

            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                // Getting file extension
                $path_parts = pathinfo($_FILES['favicon']['name']);
                $extension  = $path_parts['extension'];
                $extension  = strtolower($extension);
                // Setup our new file path
                
                $filename    = 'favicon-' .time(). '.' . $extension;
                $newFilePath = $path . $filename;

                _maybe_create_upload_path($path);
                // Upload the file into the company uploads dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {

                    // delete file old
                    if ($file_old) {
                        $file_path = FCPATH.PERFEX_POPUP_UPLOAD_PATH."/content_media";
                        $path_old = $path.$file_old;
                        if (file_exists($path_old)) {
                            unlink($path_old);
                        }
                    }

                    return $filename;
                }
            }
        }

        return false;
    }
}

if (!function_exists('handle_social_image_perfex_popup_upload')) {

    function handle_social_image_perfex_popup_upload($file_old = '')
    {
        
        if (isset($_FILES['social_image']['name']) && $_FILES['social_image']['name'] != '') {
            // Get the temp file path
            $tmpFilePath = $_FILES['social_image']['tmp_name'];
            // Make sure we have a filepath
            $path  = FCPATH.PERFEX_POPUP_ASSETS_PATH."/images/uploads/";

            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                // Getting file extension
                $path_parts = pathinfo($_FILES['social_image']['name']);
                $extension  = $path_parts['extension'];
                $extension  = strtolower($extension);
                // Setup our new file path
                
                $filename    = 'socialimage-' .time(). '.' . $extension;
                $newFilePath = $path . $filename;

                _maybe_create_upload_path($path);
                // Upload the file into the company uploads dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {

                    // delete file old
                    if ($file_old) {
                        $file_path = FCPATH.PERFEX_POPUP_UPLOAD_PATH."/content_media";
                        $path_old = $path.$file_old;
                        if (file_exists($path_old)) {
                            unlink($path_old);
                        }
                    }
                    
                    return $filename;
                }
            }
        }

        return false;
    }
}

if(!function_exists("getDeviceTracking")){

    function getDeviceTracking($tracking){
        
        if($tracking->isMobile()){

            return "Mobile";
        }
        elseif($tracking->isTablet()){

            return "Tablet";
        }
        elseif($tracking->isDesktop()){
            
            return "Desktop";
        }
        else{
            return "Unknown";
        }
    }
}

if(!function_exists("getFieldFormData")){

    function getFieldFormData($stdClass, $field){
        if(isset($stdClass->$field)){
            return $stdClass->$field;
        }
        return "";
    }
}

if(!function_exists('getRedirectPaymentAfterSuccess'))
{
    function getRedirectPaymentAfterSuccess($page)
    {
        $redirect_url_success = '';
        if ($page->type_payment_submit == 'url') {
            $redirect_url_success = $page->redirect_url_payment; 
        } else {
            $redirect_url_success = site_url('sp/thankyou/' . $page->code);
        }
        return $redirect_url_success;
    }
}

if(!function_exists('dd'))
{
    function dd( $result )
    {
        echo '<pre>'; print_r($result); die();
    }
}