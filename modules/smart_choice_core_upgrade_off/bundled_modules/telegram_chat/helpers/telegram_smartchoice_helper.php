<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('telegram_sc_option')) {
    function telegram_sc_option($key, $default = '') {
        $value = get_option($key);
        return ($value === '' || $value === null) ? $default : $value;
    }
}
if (!function_exists('telegram_sc_send_message')) {
    function telegram_sc_send_message($chatId, $message, $parseMode = 'HTML') {
        $token = telegram_sc_option('telegram_bot_token');
        if (empty($token) || empty($chatId) || empty($message)) { return false; }
        $ch = curl_init('https://api.telegram.org/bot' . $token . '/sendMessage');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['chat_id'=>$chatId,'text'=>$message,'parse_mode'=>$parseMode,'disable_web_page_preview'=>true]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
if (!function_exists('telegram_sc_get_updates')) {
    function telegram_sc_get_updates() {
        $token = telegram_sc_option('telegram_bot_token');
        if (empty($token)) { return []; }
        $ch = curl_init('https://api.telegram.org/bot' . $token . '/getUpdates');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $response = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($response, true);
        return is_array($json) ? $json : [];
    }
}
if (!function_exists('telegram_sc_project_chat_id')) {
    function telegram_sc_project_chat_id($projectId = null) {
        $CI =& get_instance();
        if ($projectId && $CI->db->table_exists(db_prefix().'telegram_project_groups')) {
            $row = $CI->db->where('project_id',(int)$projectId)->get(db_prefix().'telegram_project_groups')->row();
            if ($row && !empty($row->telegram_group_id)) { return $row->telegram_group_id; }
        }
        return telegram_sc_option('telegram_project_alerts_group_id', telegram_sc_option('telegram_default_chat_id'));
    }
}
if (!function_exists('telegram_sc_allowed_user')) {
    function telegram_sc_allowed_user($telegramUserId, $command = '') {
        $owner = telegram_sc_option('telegram_owner_chat_id');
        if ((string)$telegramUserId === (string)$owner) { return true; }
        $CI =& get_instance();
        if (!$CI->db->table_exists(db_prefix().'telegram_allowed_users')) { return false; }
        $row = $CI->db->where('telegram_user_id',(string)$telegramUserId)->where('active',1)->get(db_prefix().'telegram_allowed_users')->row();
        if (!$row) { return false; }
        if (empty($command) || empty($row->allowed_commands) || $row->allowed_commands === '*') { return true; }
        return in_array($command, array_map('trim', explode(',', $row->allowed_commands)), true);
    }
}
if (!function_exists('telegram_sc_create_project_folders')) {
    function telegram_sc_create_project_folders($projectId) {
        $base = FCPATH . 'uploads/projects/' . (int)$projectId . '/';
        $folders = ['01 Contract','02 Estimates','03 Invoices','04 Permits','05 Plans','06 Photos','07 Videos','08 Materials','09 Subcontractors','10 Inspections','11 Change Orders','12 Final Documents'];
        if (!is_dir($base)) { @mkdir($base, 0755, true); }
        foreach ($folders as $folder) { if (!is_dir($base.$folder)) { @mkdir($base.$folder, 0755, true); } }
        return $base;
    }
}
