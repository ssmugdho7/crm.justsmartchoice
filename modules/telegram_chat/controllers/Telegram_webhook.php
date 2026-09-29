<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Telegram_webhook extends App_Controller
{
    public function index()
    {
        $secret = get_option('telegram_chat_webhook_secret');
        $header = isset($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'])
            ? (string) $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN']
            : '';

        if ($secret && !hash_equals((string) $secret, $header)) {
            show_error('Unauthorized', 403);
        }

        $update = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($update)) {
            echo 'OK';
            return;
        }

        $CI = &get_instance();
        $CI->load->model('telegram_chat/telegram_model');

        $message = $update['message'] ?? $update['edited_message'] ?? null;
        if ($message) {
            $chatId = (string) ($message['chat']['id'] ?? '');
            $text = trim((string) ($message['text'] ?? ''));
            $sender = trim(($message['from']['first_name'] ?? '') . ' ' . ($message['from']['last_name'] ?? ''));

            $CI->telegram_model->log_message([
                'chat_id' => $chatId,
                'message_direction' => 'incoming',
                'module' => 'telegram',
                'action' => 'webhook_message',
                'record_id' => $message['message_id'] ?? null,
                'telegram_message_id' => $message['message_id'] ?? null,
                'sender_name' => $sender,
                'message_text' => $text ?: '[non-text message]',
                'raw_payload' => json_encode($update),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if ($text !== '') {
                $this->handle_command($chatId, $text);
            }
        }

        echo 'OK';
    }

    private function handle_command($chatId, $text)
    {
        $command = strtolower(ltrim(strtok($text, " \n"), '/'));
        $CI = &get_instance();

        switch ($command) {
            case 'start':
                $message = '<b>Smart Choice CRM Bot</b>' . "\n"
                    . 'Use /appointments, /openleads, /openprojects, /openinvoices, /overduetasks, /store, or /support.';
                break;
            case 'appointments':
                $table = db_prefix() . 'appointly_appointments';
                if (!$CI->db->table_exists($table)) {
                    $message = 'Appointments module is not available.';
                    break;
                }
                $upcoming = $CI->db->where('date >=', date('Y-m-d'))->order_by('date', 'ASC')->limit(5)->get($table)->result_array();
                if (!$upcoming) {
                    $message = 'No upcoming appointments found.';
                    break;
                }
                $lines = ['<b>Upcoming Appointments</b>'];
                foreach ($upcoming as $row) {
                    $lines[] = telegram_chat_escape(($row['date'] ?? '') . ' ' . ($row['start_hour'] ?? '') . ' — ' . ($row['subject'] ?? $row['name'] ?? 'Appointment'));
                }
                $message = implode("\n", $lines);
                break;
            case 'openleads':
                $message = 'Open leads: ' . $CI->db->count_all(db_prefix() . 'leads');
                break;
            case 'openprojects':
                $message = 'Open projects: ' . $CI->db->count_all(db_prefix() . 'projects');
                break;
            case 'openinvoices':
                $message = 'Open invoices: ' . $CI->db->where_not_in('status', [2, 5])->count_all_results(db_prefix() . 'invoices');
                break;
            case 'overduetasks':
                $message = 'Overdue tasks: ' . $CI->db->where('duedate <', date('Y-m-d'))->where('status !=', 5)->count_all_results(db_prefix() . 'tasks');
                break;
            case 'store':
                $url = get_option('telegram_chat_store_url');
                $message = $url ? '<a href="' . telegram_chat_escape($url) . '">Open Smart Choice Store</a>' : 'Store URL is not configured.';
                break;
            case 'support':
                $url = get_option('telegram_chat_support_url');
                $message = $url ? '<a href="' . telegram_chat_escape($url) . '">Open Customer Support</a>' : 'Support URL is not configured.';
                break;
            default:
                $message = 'Command received. Use /start to see available commands.';
                break;
        }

        telegram_chat_send_message($chatId, $message, 'telegram', 'bot_command');
    }
}
