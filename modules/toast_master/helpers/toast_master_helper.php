<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('toast_master_styles')) {
    function toast_master_styles()
    {
        return [
            '1'  => _l('toast_master_style_classic'),
            '2'  => _l('toast_master_style_apple_glass'),
            '3'  => _l('toast_master_style_fluent'),
            '4'  => _l('toast_master_style_slack'),
            '5'  => _l('toast_master_style_teams'),
            '6'  => _l('toast_master_style_minimal'),
            '7'  => _l('toast_master_style_premium_dark'),
            '8'  => _l('toast_master_style_elegant_card'),
            '9'  => _l('toast_master_style_compact_crm'),
            '10' => _l('toast_master_style_luxury'),
            '11' => _l('toast_master_style_telegram'),
            '12' => _l('toast_master_style_whatsapp'),
        ];
    }
}

if (!function_exists('toast_master_positions')) {
    function toast_master_positions()
    {
        return [
            'top-left'      => _l('top-left'),
            'top-center'    => _l('top-center'),
            'top-right'     => _l('top-right'),
            'middle-left'   => _l('middle-left'),
            'center'        => _l('center'),
            'middle-right'  => _l('middle-right'),
            'bottom-left'   => _l('bottom-left'),
            'bottom-center' => _l('bottom-center'),
            'bottom-right'  => _l('bottom-right'),
        ];
    }
}

if (!function_exists('toast_master_sounds')) {
    function toast_master_sounds()
    {
        return [
            'none'        => _l('toast_master_sound_none'),
            'soft_click'  => _l('toast_master_sound_soft_click'),
            'pop'         => _l('toast_master_sound_pop'),
            'ding'        => _l('toast_master_sound_ding'),
            'glass'       => _l('toast_master_sound_glass'),
            'bell'        => _l('toast_master_sound_bell'),
            'message'     => _l('toast_master_sound_message'),
            'mail'        => _l('toast_master_sound_mail'),
            'success'     => _l('toast_master_sound_success'),
            'warning'     => _l('toast_master_sound_warning'),
            'error'       => _l('toast_master_sound_error'),
        ];
    }
}

if (!function_exists('toast_master_animations')) {
    function toast_master_animations()
    {
        return [
            'fade'        => _l('toast_master_animation_fade'),
            'slide_left'  => _l('toast_master_animation_slide_left'),
            'slide_right' => _l('toast_master_animation_slide_right'),
            'slide_down'  => _l('toast_master_animation_slide_down'),
            'zoom'        => _l('toast_master_animation_zoom'),
            'bounce'      => _l('toast_master_animation_bounce'),
            'flip'        => _l('toast_master_animation_flip'),
            'pop'         => _l('toast_master_animation_pop'),
        ];
    }
}

if (!function_exists('toast_master_channels')) {
    function toast_master_channels()
    {
        return [
            'toast_master_intercept_alert_float'      => _l('toast_master_channel_alert_float'),
            'toast_master_intercept_browser_alert'    => _l('toast_master_channel_browser_alert'),
            'toast_master_intercept_unsaved_warning'  => _l('toast_master_channel_unsaved_warning'),
            'toast_master_intercept_ajax'             => _l('toast_master_channel_ajax'),
            'toast_master_intercept_validation'       => _l('toast_master_channel_validation'),
            'toast_master_intercept_messages'         => _l('toast_master_channel_messages'),
            'toast_master_intercept_announcements'    => _l('toast_master_channel_announcements'),
            'toast_master_intercept_module_updates'   => _l('toast_master_channel_module_updates'),
            'toast_master_intercept_cron'             => _l('toast_master_channel_cron'),
        ];
    }
}

if (!function_exists('toast_master_get_runtime_settings')) {
    function toast_master_get_runtime_settings()
    {
        return [
            'enabled' => (int)get_option('toast_master_enable') === 1,
            'style' => (string)(get_option('toaster_style') ?: '1'),
            'position' => (string)(get_option('toaster_position') ?: 'top-right'),
            'soundEnabled' => (int)get_option('toast_master_sound_enable') === 1,
            'soundName' => (string)(get_option('toast_master_sound_name') ?: 'soft_click'),
            'volume' => max(0, min(100, (int)(get_option('toast_master_volume') ?: 55))),
            'duration' => max(1200, (int)(get_option('toast_master_duration') ?: 4500)),
            'pauseOnHover' => (int)get_option('toast_master_pause_on_hover') === 1,
            'progressBar' => (int)get_option('toast_master_progress_bar') === 1,
            'animation' => (string)(get_option('toast_master_animation') ?: 'fade'),
            'channels' => [
                'alertFloat' => (int)get_option('toast_master_intercept_alert_float') === 1,
                'browserAlert' => (int)get_option('toast_master_intercept_browser_alert') === 1,
                'unsavedWarning' => (int)get_option('toast_master_intercept_unsaved_warning') === 1,
                'ajax' => (int)get_option('toast_master_intercept_ajax') === 1,
                'validation' => (int)get_option('toast_master_intercept_validation') === 1,
                'messages' => (int)get_option('toast_master_intercept_messages') === 1,
                'announcements' => (int)get_option('toast_master_intercept_announcements') === 1,
                'moduleUpdates' => (int)get_option('toast_master_intercept_module_updates') === 1,
                'cron' => (int)get_option('toast_master_intercept_cron') === 1,
            ],
        ];
    }
}

if (!function_exists('toast_master_log')) {
    function toast_master_log($type, $message, $title = '', $source = 'crm', $url = '')
    {
        if ((int)get_option('toast_master_history_enable') !== 1) {
            return false;
        }

        $CI = &get_instance();
        if (!$CI->db->table_exists(db_prefix() . 'toast_master_history')) {
            return false;
        }

        $staffid = function_exists('get_staff_user_id') && is_staff_logged_in() ? get_staff_user_id() : null;
        $contact_id = function_exists('get_contact_user_id') && is_client_logged_in() ? get_contact_user_id() : null;

        return $CI->db->insert(db_prefix() . 'toast_master_history', [
            'staffid' => $staffid,
            'contact_id' => $contact_id,
            'area' => is_staff_logged_in() ? 'admin' : 'client',
            'source' => $source,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'datecreated' => date('Y-m-d H:i:s'),
        ]);
    }
}

if (!function_exists('hopper_verify')) {
    function hopper_verify($p_code)
    {
        return true;
    }
}
