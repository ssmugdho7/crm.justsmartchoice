<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Telegram Chat
Description: Smart Choice Telegram CRM notification center, appointment monitoring, customer messaging, bot management, online links, message logs, and CRM command tools.
Version: 2.0.2
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/
*/

define('TELEGRAM_CHAT_MODULE_NAME', 'telegram_chat');
define('TELEGRAM_CHAT_VERSION', '2.0.2');

register_activation_hook(TELEGRAM_CHAT_MODULE_NAME, 'telegram_chat_module_activation_hook');
register_deactivation_hook(TELEGRAM_CHAT_MODULE_NAME, 'telegram_chat_module_deactivation_hook');
register_language_files(TELEGRAM_CHAT_MODULE_NAME, [TELEGRAM_CHAT_MODULE_NAME]);

hooks()->add_action('admin_init', 'telegram_chat_register_permissions');
hooks()->add_action('admin_init', 'telegram_chat_register_menu_items');
hooks()->add_action('app_admin_head', 'telegram_chat_load_admin_assets');
hooks()->add_action('after_cron_run', 'telegram_chat_monitor_appointments');
hooks()->add_action('after_cron_run', 'telegram_chat_process_scheduled_messages');

function telegram_chat_module_activation_hook()
{
    require_once __DIR__ . '/install.php';
    update_option('telegram_chat_enabled', '1');
}

function telegram_chat_module_deactivation_hook()
{
    update_option('telegram_chat_enabled', '0');
}

function telegram_chat_register_permissions()
{
    register_staff_capabilities('telegram_chat', [
        'capabilities' => [
            'view_own'    => _l('telegram_permission_view_own'),
            'view'        => _l('telegram_permission_view_global'),
            'create'      => _l('permission_create'),
            'edit'        => _l('permission_edit'),
            'delete'      => _l('permission_delete'),
        ],
    ], _l('telegram_chat'));
}

function telegram_chat_can_view()
{
    return is_admin()
        || has_permission('telegram_chat', '', 'view')
        || has_permission('telegram_chat', '', 'view_own');
}

function telegram_chat_register_menu_items()
{
    if (!telegram_chat_can_view()) {
        return;
    }

    $CI = &get_instance();

    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug'     => 'telegram-chat-settings',
        'name'     => _l('telegram_chat_settings'),
        'href'     => admin_url('telegram_chat'),
        'icon'     => 'fa-brands fa-telegram',
        'position' => 35,
    ]);

    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug'     => 'telegram-chat-messages',
        'name'     => _l('telegram_message_center'),
        'href'     => admin_url('telegram_chat/messages'),
        'icon'     => 'fa-solid fa-comments',
        'position' => 36,
    ]);

    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug'     => 'telegram-chat-appointments',
        'name'     => _l('telegram_appointments_monitor'),
        'href'     => admin_url('telegram_chat/appointments'),
        'icon'     => 'fa-solid fa-calendar-check',
        'position' => 37,
    ]);

    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug'     => 'telegram-chat-online-links',
        'name'     => _l('telegram_online_links'),
        'href'     => admin_url('telegram_chat/online_links'),
        'icon'     => 'fa-solid fa-arrow-up-right-from-square',
        'position' => 38,
    ]);

    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug'     => 'telegram-chat-bot-profile',
        'name'     => _l('telegram_bot_profile'),
        'href'     => admin_url('telegram_chat/bot_profile'),
        'icon'     => 'fa-solid fa-robot',
        'position' => 39,
    ]);

    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug'     => 'telegram-chat-health',
        'name'     => _l('telegram_health_check'),
        'href'     => admin_url('telegram_chat/health'),
        'icon'     => 'fa-solid fa-heart-pulse',
        'position' => 40,
    ]);

    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug'     => 'telegram-chat-training',
        'name'     => _l('telegram_help_training'),
        'href'     => admin_url('telegram_chat/training'),
        'icon'     => 'fa-solid fa-graduation-cap',
        'position' => 41,
    ]);

    $CI->app->add_quick_actions_link([
        'name'       => _l('telegram_chat'),
        'permission' => 'telegram_chat',
        'url'        => 'telegram_chat',
        'position'   => 79,
    ]);
}

function telegram_chat_load_admin_assets()
{
    if (!telegram_chat_can_view()) {
        return;
    }

    echo '<link rel="stylesheet" href="' . module_dir_url(TELEGRAM_CHAT_MODULE_NAME, 'assets/css/telegram_smartchoice.css?v=' . TELEGRAM_CHAT_VERSION) . '">';
}

function telegram_chat_get_primary_account()
{
    $CI = &get_instance();
    $CI->load->model(TELEGRAM_CHAT_MODULE_NAME . '/telegram_model');
    $staffId = get_staff_user_id();
    $row = $staffId ? $CI->telegram_model->get($staffId) : null;
    return $row ?: $CI->telegram_model->get_admin_id();
}

function telegram_chat_api_request($method, array $data = [])
{
    $account = telegram_chat_get_primary_account();
    if (!$account || empty($account->bot_token)) {
        return ['ok' => false, 'description' => 'Telegram bot token is not configured.'];
    }

    $url = 'https://api.telegram.org/bot' . trim($account->bot_token) . '/' . $method;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        if ($response === false) {
            return ['ok' => false, 'description' => $error ?: 'Telegram request failed.'];
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($data),
                'timeout' => 25,
            ],
        ]);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return ['ok' => false, 'description' => 'Telegram request failed.'];
        }
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : ['ok' => false, 'description' => 'Invalid Telegram response.'];
}

function telegram_chat_send_message($chatId, $message, $module = 'system', $action = 'message', $recordId = null, array $buttons = [])
{
    if (!$chatId || !$message) {
        return false;
    }

    $payload = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];

    if ($buttons) {
        $payload['reply_markup'] = json_encode(['inline_keyboard' => $buttons]);
    }

    $result = telegram_chat_api_request('sendMessage', $payload);

    $CI = &get_instance();
    $CI->load->model(TELEGRAM_CHAT_MODULE_NAME . '/telegram_model');
    $CI->telegram_model->log_message([
        'staff_id' => get_staff_user_id() ?: null,
        'chat_id' => (string) $chatId,
        'message_direction' => 'outgoing',
        'module' => $module,
        'action' => $action,
        'record_id' => $recordId,
        'telegram_message_id' => !empty($result['result']['message_id']) ? (string) $result['result']['message_id'] : null,
        'sender_name' => get_staff_full_name() ?: 'Smart Choice CRM',
        'message_text' => strip_tags($message),
        'raw_payload' => json_encode($result),
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    return !empty($result['ok']);
}

function telegram_chat_default_chat_id()
{
    $account = telegram_chat_get_primary_account();
    return $account && !empty($account->chat_id) ? $account->chat_id : get_option('telegram_chat_default_chat_id');
}

function telegram_chat_monitor_appointments()
{
    if (get_option('telegram_chat_enable_appointment_monitor') !== '1') {
        return;
    }

    $CI = &get_instance();
    $table = db_prefix() . 'appointly_appointments';
    if (!$CI->db->table_exists($table)) {
        return;
    }

    $CI->load->model(TELEGRAM_CHAT_MODULE_NAME . '/telegram_model');
    $rows = $CI->db->order_by('id', 'ASC')->get($table)->result_array();

    foreach ($rows as $appointment) {
        $id = (int) ($appointment['id'] ?? 0);
        if (!$id) {
            continue;
        }

        $state = [
            'status' => $appointment['status'] ?? '',
            'date' => $appointment['date'] ?? '',
            'start_hour' => $appointment['start_hour'] ?? '',
            'subject' => $appointment['subject'] ?? '',
            'name' => $appointment['name'] ?? '',
            'email' => $appointment['email'] ?? '',
            'phone' => $appointment['phone'] ?? '',
            'cancel_notes' => $appointment['cancel_notes'] ?? '',
            'reschedule_status' => $appointment['reschedule_status'] ?? '',
        ];

        $hash = sha1(json_encode($state));
        $tracked = $CI->telegram_model->get_appointment_tracking($id);

        if (!$tracked) {
            $CI->telegram_model->save_appointment_tracking($id, $hash, $state['status']);
            if (get_option('telegram_chat_notify_new_appointments') === '1') {
                telegram_chat_send_appointment_notice($appointment, 'created');
            }
            continue;
        }

        if ($tracked->payload_hash !== $hash) {
            $oldStatus = $tracked->last_status;
            $CI->telegram_model->save_appointment_tracking($id, $hash, $state['status']);
            if (get_option('telegram_chat_notify_appointment_changes') === '1') {
                telegram_chat_send_appointment_notice($appointment, $oldStatus !== $state['status'] ? 'status_changed' : 'updated', $oldStatus);
            }
        }
    }

    update_option('telegram_chat_last_appointment_scan', date('Y-m-d H:i:s'));
}

function telegram_chat_send_appointment_notice(array $appointment, $event, $oldStatus = '')
{
    $chatId = telegram_chat_default_chat_id();
    if (!$chatId) {
        return false;
    }

    $id = (int) ($appointment['id'] ?? 0);
    $subject = $appointment['subject'] ?? _l('telegram_appointment');
    $name = $appointment['name'] ?? '';
    $status = $appointment['status'] ?? '';
    $date = trim(($appointment['date'] ?? '') . ' ' . ($appointment['start_hour'] ?? ''));
    $eventLabel = $event === 'created' ? _l('telegram_new_appointment') : ($event === 'status_changed' ? _l('telegram_appointment_status_changed') : _l('telegram_appointment_updated'));

    $message = '<b>' . telegram_chat_escape($eventLabel) . '</b>' . "\n"
        . '<b>' . telegram_chat_escape($subject) . '</b>' . "\n"
        . _l('telegram_customer') . ': ' . telegram_chat_escape($name ?: ($appointment['email'] ?? '')) . "\n"
        . _l('telegram_date_time') . ': ' . telegram_chat_escape($date) . "\n"
        . _l('telegram_status') . ': ' . telegram_chat_escape($status);

    if ($oldStatus && $oldStatus !== $status) {
        $message .= "\n" . _l('telegram_previous_status') . ': ' . telegram_chat_escape($oldStatus);
    }

    if (!empty($appointment['phone'])) {
        $message .= "\n" . _l('telegram_phone') . ': ' . telegram_chat_escape($appointment['phone']);
    }

    $buttons = [[
        ['text' => _l('telegram_open_appointment'), 'url' => admin_url('appointly/appointments')],
    ]];

    return telegram_chat_send_message($chatId, $message, 'appointly', $event, $id, $buttons);
}

function telegram_chat_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function telegram_chat_process_scheduled_messages()
{
    $CI = &get_instance();
    $CI->load->model(TELEGRAM_CHAT_MODULE_NAME . '/telegram_model');
    foreach ($CI->telegram_model->get_due_scheduled_messages() as $row) {
        $sent = telegram_chat_send_message($row['chat_id'], $row['message_text'], 'telegram', 'scheduled_message', $row['id']);
        $CI->telegram_model->mark_scheduled_message($row['id'], $sent);
    }
}

/* Native CRM notifications retained and standardized. */
hooks()->add_action('lead_created', function ($leadId) {
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $lead = $CI->leads_model->get($leadId);
    telegram_chat_send_message(telegram_chat_default_chat_id(), '<b>' . _l('telegram_new_lead') . '</b>' . "\n" . telegram_chat_escape($lead->name ?? ''), 'leads', 'created', $leadId);
});

hooks()->add_action('after_invoice_added', function ($invoiceId) {
    telegram_chat_send_message(telegram_chat_default_chat_id(), '<b>' . _l('telegram_new_invoice') . '</b> #' . (int) $invoiceId, 'invoices', 'created', $invoiceId);
});

hooks()->add_action('after_payment_added', function ($paymentId) {
    telegram_chat_send_message(telegram_chat_default_chat_id(), '<b>' . _l('telegram_payment_received') . '</b> #' . (int) $paymentId, 'payments', 'received', $paymentId);
});

hooks()->add_action('ticket_created', function ($ticketId) {
    if (get_option('telegram_chat_enable_ticket_notice') === '1') {
        telegram_chat_send_message(telegram_chat_default_chat_id(), '<b>' . _l('telegram_new_ticket') . '</b> #' . (int) $ticketId, 'tickets', 'created', $ticketId);
    }
});

/* Optional direct hooks for Appointly-compatible events. */
foreach (['appointly_appointment_created', 'appointment_created'] as $hookName) {
    hooks()->add_action($hookName, function ($appointmentId) {
        telegram_chat_monitor_appointments();
    });
}
foreach (['appointly_appointment_updated', 'appointment_updated', 'appointly_status_changed', 'appointment_status_changed'] as $hookName) {
    hooks()->add_action($hookName, function ($appointmentId) {
        telegram_chat_monitor_appointments();
    });
}
