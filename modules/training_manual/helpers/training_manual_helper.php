<?php

defined('BASEPATH') or exit('No direct script access allowed');

function training_manual_generate_code($s){
  return  md5(uniqid($s, true));
}

function training_manual_get_mindmap_thumb($filename = ''){
  if($filename != ''){
    return base_url(WIKI_UPLOAD_PATH.'/storage/mindmap') . '/' . $filename;
  }else{
    return base_url(TRAINING_MANUAL_ASSETS_PATH.'/builder/ui/default_thumb.png');
  }
}

function training_manual_get_mindmap_content(){
  return '{"data":{"text":"My New Mind Map"},"template":"default","theme":"fresh-blue","version":"1.3.5"}';
}

if (!function_exists('training_manual_customer_fallback_asset')) {
    /** Return a guaranteed module fallback cover selected by topic. */
    function training_manual_customer_fallback_asset($title = '')
    {
        $needle = strtolower((string) $title);
        $asset = 'default-cover.svg';
        if (strpos($needle, 'portal') !== false) {
            $asset = 'client-portal.svg';
        } elseif (strpos($needle, 'support') !== false || strpos($needle, 'ticket') !== false || strpos($needle, 'communication') !== false) {
            $asset = 'support-system.svg';
        } elseif (strpos($needle, 'estimate') !== false || strpos($needle, 'proposal') !== false || strpos($needle, 'contract') !== false || strpos($needle, 'invoice') !== false || strpos($needle, 'document') !== false || strpos($needle, 'record') !== false || strpos($needle, 'payment') !== false) {
            $asset = 'documents-payments.svg';
        } elseif (strpos($needle, 'vision') !== false || strpos($needle, 'quality') !== false || strpos($needle, 'warranty') !== false || strpos($needle, 'review') !== false) {
            $asset = 'vision-standards.svg';
        } elseif (strpos($needle, 'process') !== false || strpos($needle, 'material') !== false || strpos($needle, 'service') !== false || strpos($needle, 'expect') !== false) {
            $asset = 'service-experience.svg';
        }
        return base_url(TRAINING_MANUAL_ASSETS_PATH . '/img/customer-guides/' . $asset . '?v=6');
    }
}

if (!function_exists('training_manual_customer_image_url')) {
    /**
     * Resolve uploaded covers, module asset covers, and guaranteed topic fallbacks.
     * Broken local database paths automatically fall back instead of displaying an empty image.
     */
    function training_manual_customer_image_url($value = '', $title = '')
    {
        $fallback = training_manual_customer_fallback_asset($title);
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        $normalized = ltrim(str_replace('\\', '/', $value), '/');
        if (strpos($normalized, 'modules/training_manual/') === 0 || strpos($normalized, 'uploads/') === 0) {
            $localPath = FCPATH . $normalized;
            // Refresh bundled guide covers independently of cached database paths.
            $revision = strpos($normalized, TRAINING_MANUAL_ASSETS_PATH . '/img/customer-guides/') === 0 ? '?v=6' : '';
            return is_file($localPath) ? base_url($normalized . $revision) : $fallback;
        }

        $basename = basename($normalized);
        $localPath = FCPATH . 'uploads/training_manual/' . $basename;
        return is_file($localPath)
            ? base_url('uploads/training_manual/' . rawurlencode($basename))
            : $fallback;
    }
}
