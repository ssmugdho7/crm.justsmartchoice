<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Telegram_chat extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('telegram_model');

        if (!is_admin() && !has_permission('telegram_chat', '', 'view') && !has_permission('telegram_chat', '', 'view_own')) {
            access_denied('Telegram Chat');
        }
    }

    public function index()
    {
        $data['title'] = _l('telegram_chat_settings');
        $data['userTeleInfo'] = $this->telegram_model->get(get_staff_user_id());
        $this->load->view('settings', $data);
    }

    public function save_settings()
    {
        if (!is_admin() && !has_permission('telegram_chat', '', 'edit')) {
            access_denied('Telegram Chat');
        }

        $staffId = get_staff_user_id();
        $row = $this->telegram_model->get($staffId);
        $record = [
            'user_id' => $staffId,
            'bot_token' => trim((string) $this->input->post('bot_token')),
            'chat_id' => trim((string) $this->input->post('chat_id')),
            'bot_username' => ltrim(trim((string) $this->input->post('bot_username')), '@'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($row) {
            $this->telegram_model->update($record, $row->id);
        } else {
            $record['created_at'] = date('Y-m-d H:i:s');
            $this->telegram_model->add($record);
        }

        $options = [
            'telegram_chat_enable_message_log',
            'telegram_chat_enable_client_login_notice',
            'telegram_chat_enable_ticket_notice',
            'telegram_chat_enable_proposal_notice',
            'telegram_chat_enable_file_notice',
            'telegram_chat_enable_portal_message_notice',
            'telegram_chat_enable_appointment_monitor',
            'telegram_chat_notify_new_appointments',
            'telegram_chat_notify_appointment_changes',
        ];

        foreach ($options as $option) {
            update_option($option, $this->input->post($option) ? '1' : '0');
        }

        foreach ([
            'telegram_chat_default_chat_id',
            'telegram_chat_store_url',
            'telegram_chat_mini_app_url',
            'telegram_chat_support_url',
            'telegram_chat_privacy_url',
            'telegram_chat_terms_url',
        ] as $option) {
            update_option($option, trim((string) $this->input->post($option)));
        }

        set_alert('success', _l('telegram_settings_saved'));
        redirect(admin_url('telegram_chat'));
    }

    public function messages()
    {
        $filters = [
            'q' => $this->input->get('q', true),
            'module' => $this->input->get('module', true),
            'direction' => $this->input->get('direction', true),
            'date_from' => $this->input->get('date_from', true),
            'date_to' => $this->input->get('date_to', true),
        ];

        $data['title'] = _l('telegram_message_center');
        $data['filters'] = $filters;
        $data['messages'] = $this->telegram_model->get_messages($filters);
        $data['total'] = $this->telegram_model->count_messages($filters);
        $this->load->view('messages', $data);
    }

    public function appointments()
    {
        $table = db_prefix() . 'appointly_appointments';
        $data['title'] = _l('telegram_appointments_monitor');
        $data['appointly_available'] = $this->db->table_exists($table);
        $data['appointments'] = $data['appointly_available']
            ? $this->db->order_by('id', 'DESC')->limit(200)->get($table)->result_array()
            : [];
        $data['last_scan'] = get_option('telegram_chat_last_appointment_scan');
        $this->load->view('appointments', $data);
    }

    public function scan_appointments()
    {
        if (!is_admin() && !has_permission('telegram_chat', '', 'edit')) {
            access_denied('Telegram Chat');
        }

        telegram_chat_monitor_appointments();
        set_alert('success', _l('telegram_appointment_scan_completed'));
        redirect(admin_url('telegram_chat/appointments'));
    }

    public function online_links()
    {
        $data['title'] = _l('telegram_online_links');
        $data['account'] = telegram_chat_get_primary_account();
        $data['webhook_url'] = site_url('telegram_chat/telegram_webhook');
        $this->load->view('online_links', $data);
    }

    public function bot_profile()
    {
        $data['title'] = _l('telegram_bot_profile');
        $data['account'] = telegram_chat_get_primary_account();
        $data['bot_info'] = telegram_chat_api_request('getMe');
        $data['webhook_info'] = telegram_chat_api_request('getWebhookInfo');
        $this->load->view('bot_profile', $data);
    }

    public function save_bot_profile()
    {
        if (!is_admin() && !has_permission('telegram_chat', '', 'edit')) {
            access_denied('Telegram Chat');
        }

        $calls = [
            ['setMyName', ['name' => trim((string) $this->input->post('bot_name'))]],
            ['setMyDescription', ['description' => trim((string) $this->input->post('bot_description'))]],
            ['setMyShortDescription', ['short_description' => trim((string) $this->input->post('bot_short_description'))]],
        ];

        $commands = [
            ['command' => 'start', 'description' => 'Open Smart Choice CRM bot'],
            ['command' => 'appointments', 'description' => 'Show appointment information'],
            ['command' => 'openleads', 'description' => 'Show open leads count'],
            ['command' => 'openprojects', 'description' => 'Show open projects count'],
            ['command' => 'openinvoices', 'description' => 'Show open invoices count'],
            ['command' => 'overduetasks', 'description' => 'Show overdue tasks count'],
            ['command' => 'support', 'description' => 'Open customer support'],
            ['command' => 'store', 'description' => 'Open Smart Choice store or services'],
        ];
        $calls[] = ['setMyCommands', ['commands' => json_encode($commands)]];

        $errors = [];
        foreach ($calls as [$method, $payload]) {
            $result = telegram_chat_api_request($method, $payload);
            if (empty($result['ok'])) {
                $errors[] = $method . ': ' . ($result['description'] ?? 'Failed');
            }
        }

        if ($errors) {
            set_alert('warning', implode(' | ', $errors));
        } else {
            set_alert('success', _l('telegram_bot_profile_saved'));
        }

        redirect(admin_url('telegram_chat/bot_profile'));
    }

    public function install_webhook()
    {
        if (!is_admin()) {
            access_denied('Telegram Chat');
        }

        $secret = get_option('telegram_chat_webhook_secret');
        $result = telegram_chat_api_request('setWebhook', [
            'url' => site_url('telegram_chat/telegram_webhook'),
            'secret_token' => $secret,
            'allowed_updates' => json_encode(['message', 'edited_message', 'callback_query', 'pre_checkout_query']),
        ]);

        set_alert(!empty($result['ok']) ? 'success' : 'danger', !empty($result['ok']) ? _l('telegram_webhook_installed') : ($result['description'] ?? _l('telegram_webhook_failed')));
        redirect(admin_url('telegram_chat/bot_profile'));
    }

    public function delete_webhook()
    {
        if (!is_admin()) {
            access_denied('Telegram Chat');
        }

        $result = telegram_chat_api_request('deleteWebhook', ['drop_pending_updates' => false]);
        set_alert(!empty($result['ok']) ? 'success' : 'danger', !empty($result['ok']) ? _l('telegram_webhook_removed') : ($result['description'] ?? _l('telegram_webhook_failed')));
        redirect(admin_url('telegram_chat/bot_profile'));
    }

    public function health()
    {
        $account = telegram_chat_get_primary_account();
        $data['title'] = _l('telegram_health_check');
        $data['account'] = $account;
        $data['checks'] = [
            _l('telegram_bot_token') => !empty($account->bot_token),
            _l('telegram_chat_id') => !empty($account->chat_id),
            _l('telegram_message_log_table') => $this->db->table_exists(db_prefix() . 'telegram_chat_messages'),
            _l('telegram_appointment_tracking_table') => $this->db->table_exists(db_prefix() . 'telegram_appointment_tracking'),
            _l('telegram_scheduled_messages_table') => $this->db->table_exists(db_prefix() . 'telegram_scheduled_messages'),
            _l('telegram_appointly_integration') => $this->db->table_exists(db_prefix() . 'appointly_appointments'),
        ];
        $data['messages_count'] = $this->db->table_exists(db_prefix() . 'telegram_chat_messages')
            ? $this->db->count_all(db_prefix() . 'telegram_chat_messages') : 0;
        $data['bot_info'] = $account && !empty($account->bot_token) ? telegram_chat_api_request('getMe') : ['ok' => false];
        $this->load->view('health', $data);
    }

    public function send_test()
    {
        $sent = telegram_chat_send_message(
            telegram_chat_default_chat_id(),
            '<b>Smart Choice Telegram Test</b>' . "\n" . _l('telegram_test_successful'),
            'system',
            'test'
        );

        set_alert($sent ? 'success' : 'danger', $sent ? _l('telegram_test_sent') : _l('telegram_test_failed'));
        redirect(admin_url('telegram_chat/health'));
    }

    public function sync_updates()
    {
        $account = telegram_chat_get_primary_account();
        if (!$account || empty($account->bot_token)) {
            set_alert('warning', _l('telegram_bot_token_missing'));
            redirect(admin_url('telegram_chat/messages'));
        }

        $offset = (int) get_option('telegram_chat_getupdates_offset');
        $payload = ['timeout' => 1];
        if ($offset > 0) {
            $payload['offset'] = $offset;
        }

        $result = telegram_chat_api_request('getUpdates', $payload);
        if (empty($result['ok'])) {
            set_alert('danger', $result['description'] ?? _l('telegram_sync_failed'));
            redirect(admin_url('telegram_chat/messages'));
        }

        $saved = 0;
        foreach ($result['result'] as $update) {
            if (!empty($update['update_id'])) {
                update_option('telegram_chat_getupdates_offset', (string) ((int) $update['update_id'] + 1));
            }
            $message = $update['message'] ?? $update['edited_message'] ?? null;
            if (!$message) {
                continue;
            }
            $this->telegram_model->log_message([
                'staff_id' => get_staff_user_id(),
                'chat_id' => (string) ($message['chat']['id'] ?? ''),
                'message_direction' => 'incoming',
                'module' => 'telegram',
                'action' => 'incoming_message',
                'record_id' => $message['message_id'] ?? null,
                'telegram_message_id' => $message['message_id'] ?? null,
                'sender_name' => trim(($message['from']['first_name'] ?? '') . ' ' . ($message['from']['last_name'] ?? '')),
                'message_text' => $message['text'] ?? '[non-text message]',
                'raw_payload' => json_encode($update),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $saved++;
        }

        set_alert('success', _l('telegram_updates_synced') . ': ' . $saved);
        redirect(admin_url('telegram_chat/messages'));
    }

    public function delete_messages()
    {
        if (!is_admin() && !has_permission('telegram_chat', '', 'delete')) {
            access_denied('Telegram Chat');
        }

        $this->telegram_model->delete_messages($this->input->post('ids'));
        set_alert('success', _l('telegram_messages_deleted'));
        redirect(admin_url('telegram_chat/messages'));
    }

    public function training()
    {
        $data['title'] = _l('telegram_help_training');
        $this->load->view('training', $data);
    }
}
