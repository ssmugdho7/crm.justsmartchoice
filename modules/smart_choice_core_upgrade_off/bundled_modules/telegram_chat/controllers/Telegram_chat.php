<?php

defined('BASEPATH') or exit('No direct script access allowed');

set_time_limit(0);

class Telegram_chat extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('telegram_model');
    }

    public function index()
    {
        $currentUserID = get_staff_user_id();
        $data['title'] = 'Telegram Settings';
        $data['userTeleInfo'] = $this->telegram_model->get($currentUserID);
        $this->load->view('telegram_chat/settings', $data);
    }

    public function messages()
    {
        $filters = [
            'q'         => $this->input->get('q', true),
            'module'    => $this->input->get('module', true),
            'direction' => $this->input->get('direction', true),
            'date_from' => $this->input->get('date_from', true),
            'date_to'   => $this->input->get('date_to', true),
        ];

        $data['title']    = 'Telegram Message Center';
        $data['filters']  = $filters;
        $data['messages'] = $this->telegram_model->get_messages($filters, 200, 0);
        $data['total']    = $this->telegram_model->count_messages($filters);

        $this->load->view('telegram_chat/messages', $data);
    }

    public function health()
    {
        $currentUserID = get_staff_user_id();
        $data['title'] = 'Telegram Health Checker';
        $data['userTeleInfo'] = $this->telegram_model->get($currentUserID);
        $data['table_exists'] = $this->db->table_exists(db_prefix() . 'telegram_chat_messages');
        $data['messages_count'] = $data['table_exists'] ? $this->db->count_all(db_prefix() . 'telegram_chat_messages') : 0;
        $this->load->view('telegram_chat/health', $data);
    }

    public function training()
    {
        $data['title'] = 'Telegram Training';
        $this->load->view('telegram_chat/training', $data);
    }

    public function addTelegramInfo()
    {
        $currentUserID = get_staff_user_id();

        $obj = [
            'chat_id'    => trim((string) $this->input->post('chat_id')),
            'bot_token'  => trim((string) $this->input->post('bot_token')),
            'user_id'    => $currentUserID,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $userInfo = $this->telegram_model->get($currentUserID);

        if (isset($userInfo) && isset($userInfo->id) && $userInfo->id) {
            unset($obj['created_at']);
            $this->telegram_model->update($obj, $userInfo->id);
        } else {
            $this->telegram_model->add($obj);
        }

        update_option('telegram_chat_enable_message_log', $this->input->post('telegram_chat_enable_message_log') ? '1' : '0');
        update_option('telegram_chat_enable_client_login_notice', $this->input->post('telegram_chat_enable_client_login_notice') ? '1' : '0');
        update_option('telegram_chat_enable_ticket_notice', $this->input->post('telegram_chat_enable_ticket_notice') ? '1' : '0');
        update_option('telegram_chat_enable_proposal_notice', $this->input->post('telegram_chat_enable_proposal_notice') ? '1' : '0');
        update_option('telegram_chat_enable_file_notice', $this->input->post('telegram_chat_enable_file_notice') ? '1' : '0');
        update_option('telegram_chat_enable_portal_message_notice', $this->input->post('telegram_chat_enable_portal_message_notice') ? '1' : '0');

        set_alert('success', _l('telegram_settings_added', _l('telegram')));
        redirect(admin_url('telegram_chat'));
    }

    public function delete_messages()
    {
        $ids = $this->input->post('ids');
        $this->telegram_model->delete_messages($ids);
        set_alert('success', 'Selected Telegram log messages deleted.');
        redirect(admin_url('telegram_chat/messages'));
    }

    public function sync_updates()
    {
        $currentUserID = get_staff_user_id();
        $info = $this->telegram_model->get($currentUserID);

        if (!$info || empty($info->bot_token)) {
            set_alert('warning', 'Telegram Bot Token is missing.');
            redirect(admin_url('telegram_chat/messages'));
        }

        $token  = trim($info->bot_token);
        $offset = (int) get_option('telegram_chat_getupdates_offset');
        $url    = 'https://api.telegram.org/bot' . rawurlencode($token) . '/getUpdates?timeout=1';

        if ($offset > 0) {
            $url .= '&offset=' . $offset;
        }

        $response = $this->telegram_http_get($url);
        $payload  = json_decode($response, true);

        if (!is_array($payload) || empty($payload['ok'])) {
            set_alert('danger', 'Telegram getUpdates failed. Check bot token and Telegram privacy/group settings.');
            redirect(admin_url('telegram_chat/messages'));
        }

        $saved = 0;
        foreach ($payload['result'] as $update) {
            $updateId = isset($update['update_id']) ? (int) $update['update_id'] : 0;
            if ($updateId > 0) {
                update_option('telegram_chat_getupdates_offset', (string) ($updateId + 1));
            }

            $message = isset($update['message']) ? $update['message'] : (isset($update['edited_message']) ? $update['edited_message'] : null);
            if (!$message) {
                continue;
            }

            $chatId = isset($message['chat']['id']) ? (string) $message['chat']['id'] : '';
            $sender = trim(
                (isset($message['from']['first_name']) ? $message['from']['first_name'] : '') . ' ' .
                (isset($message['from']['last_name']) ? $message['from']['last_name'] : '')
            );

            $text = isset($message['text']) ? $message['text'] : '[non-text message]';

            $this->telegram_model->log_message([
                'staff_id'            => $currentUserID,
                'chat_id'             => $chatId,
                'message_direction'   => 'incoming',
                'module'              => 'telegram',
                'action'              => 'incoming_message',
                'record_id'           => isset($message['message_id']) ? $message['message_id'] : null,
                'telegram_message_id' => isset($message['message_id']) ? $message['message_id'] : null,
                'sender_name'         => $sender,
                'message_text'        => $text,
                'raw_payload'         => json_encode($update),
                'created_at'          => date('Y-m-d H:i:s'),
            ]);

            $saved++;
        }

        set_alert('success', 'Telegram updates synced. New records saved: ' . $saved);
        redirect(admin_url('telegram_chat/messages'));
    }

    public function send_test()
    {
        if (!function_exists('call_telegram_webhook')) {
            set_alert('danger', 'Telegram webhook function is not available.');
            redirect(admin_url('telegram_chat/health'));
        }

        call_telegram_webhook((object) ['test' => true], 'system', 'test', 0);
        set_alert('success', 'Test notification sent. Check Telegram and Message Center.');
        redirect(admin_url('telegram_chat/health'));
    }

    private function telegram_http_get($url)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        }

        return @file_get_contents($url);
    }
}
