<?php

defined('BASEPATH') || exit('No direct script access allowed');
ini_set('memory_limit', '-1');

/*
    Module Name: WhatsBot
    Description: Elevate your customer relationship management and streamline your communication strategy with the power of WhatsApp. NULLCAVE.club
    Version: 3.0.0
    Requires at least: 3.0.*
    Module URI: https://codecanyon.net/item/whatsbot-whatsapp-marketing-bot-chat-module-for-perfex-crm/53052338
    Author: <a href="https://codecanyon.net/user/corbitaltech" target="_blank">Corbital Technologies<a/>
*/

define('WHATSBOT_MODULE', 'whatsbot');

/*
* Register language files, must be registered if the module is using languages
*/
register_language_files(WHATSBOT_MODULE, [WHATSBOT_MODULE]);

define('WHATSBOT_MODULE_UPLOAD_FOLDER', 'uploads/' . WHATSBOT_MODULE);
define('WHATSBOT_MODULE_UPLOAD_URL', base_url() . WHATSBOT_MODULE_UPLOAD_FOLDER . '/');

/*
 * Register activation module hook
 */
register_activation_hook(WHATSBOT_MODULE, 'whatsbot_module_activation_hook');
function whatsbot_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';

    $create_paths = [
        WHATSBOT_MODULE_UPLOAD_FOLDER,
        WHATSBOT_MODULE_UPLOAD_FOLDER . '/campaign',
        WHATSBOT_MODULE_UPLOAD_FOLDER . '/template',
        WHATSBOT_MODULE_UPLOAD_FOLDER . '/bot_files',
        WHATSBOT_MODULE_UPLOAD_FOLDER . '/csv'
    ];

    array_map('_maybe_create_upload_path', $create_paths);
}

register_deactivation_hook(WHATSBOT_MODULE, 'whatsbot_module_deactivation_hook');
function whatsbot_module_deactivation_hook()
{
    $my_files_list = [
        VIEWPATH . 'themes/perfex/views/my_single_ticket.php',
        VIEWPATH . 'themes/perfex/template_parts/projects/project_flow_response.php',
    ];

    foreach ($my_files_list as $actual_path) {
        if (file_exists($actual_path)) {
            @unlink($actual_path);
        }
    }
}

require_once __DIR__ . '/vendor/autoload.php';

get_instance()->load->helper(WHATSBOT_MODULE . '/whatsbot');

require_once __DIR__ . '/install.php';
get_instance()->config->load(WHATSBOT_MODULE . '/config');

require_once __DIR__ . '/includes/assets.php';
require_once __DIR__ . '/includes/staff_permissions.php';
require_once __DIR__ . '/includes/sidebar_menu_links.php';

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
hooks()->add_filter('module_whatsbot_action_links', function ($actions) {
    $actions[] = '<a href="https://docs.corbitaltech.dev/products/whatsbot/index.html" class="text-danger" target="_blank">' . _l('help') . '</a>';

    return $actions;
});

// add new created lead in campaign that is selected all leads
hooks()->add_action('lead_created', function ($id) {
    $campaigns = get_instance()->db->get_where(db_prefix() . 'wtc_campaigns', ['select_all' => '1', 'rel_type' => 'leads'])->result_array();
    foreach ($campaigns as $campaign) {
        if (0 == $campaign['is_sent']) {
            $template = wb_get_whatsapp_template($campaign['template_id']);
            get_instance()->db->insert(db_prefix() . 'wtc_campaign_data', [
                'campaign_id' => $campaign['id'],
                'rel_id' => $id,
                'rel_type' => 'leads',
                'header_message' => $template['header_data_text'],
                'body_message' => $template['body_data'],
                'footer_message' => $template['footer_data'],
                'status' => 1,
            ]);
        }
    }
});

// delete campaign lead when lead deleted
hooks()->add_action('after_lead_deleted', function ($id) {
    get_instance()->db->delete(db_prefix() . 'wtc_campaign_data', ['rel_id' => $id, 'rel_type' => 'leads']);
});

// delete campaign contacts when contact deleted
hooks()->add_action('contact_deleted', function ($id, $result) {
    get_instance()->db->delete(db_prefix() . 'wtc_campaign_data', ['rel_id' => $id, 'rel_type' => 'contacts']);
}, 0, 2);

hooks()->add_filter('before_settings_updated', function ($data) {
    $data['settings']['whatsapp_auto_lead_settings'] = $data['settings']['whatsapp_auto_lead_settings'] ?? '0';
    $data['settings']['enable_webhooks'] = $data['settings']['enable_webhooks'] ?? '0';
    $data['settings']['enable_supportagent'] = $data['settings']['enable_supportagent'] ?? '0';
    $data['settings']['enable_wtc_notification_sound'] = $data['settings']['enable_wtc_notification_sound'] ?? '0';
    $data['settings']['enable_wb_openai'] = $data['settings']['enable_wb_openai'] ?? '0';
    $data['settings']['enable_clear_chat_history'] = $data['settings']['enable_clear_chat_history'] ?? '0';
    $data['settings']['enable_ai_assistant'] = $data['settings']['enable_ai_assistant'] ?? '0';

    if (isset($data['settings']['wb_open_ai_key']) && (get_option('wb_open_ai_key') != $data['settings']['wb_open_ai_key'])) {
        get_instance()->load->model(WHATSBOT_MODULE . '/whatsbot_model');
        $response = get_instance()->whatsbot_model->connectAi($data['settings']['wb_open_ai_key']);
        if (!$response['status']) {
            set_alert('danger', $response['message']);
            return;
        }
        set_alert('success', _l('settings_updated'));
        return;
    }

    return $data;
});

// custom hook for whatsapp auto lead create if not available
hooks()->add_filter('ctl_auto_lead_creation', function ($contact_number, $name) {
    if (1 == get_option('whatsapp_auto_lead_settings')) {
        $lead_data = [
            'phonenumber' => $contact_number,
            'name' => $name,
            'status' => get_option('whatsapp_auto_leads_status') ?? 1,
            'source' => get_option('whatsapp_auto_leads_source') ?? 1,
            'assigned' => get_option('whatsapp_auto_leads_assigned') ?? 1,
            'dateadded' => date('Y-m-d H:i:s'),
            'description' => '',
            'address' => '',
            'email' => '',
        ];
        get_instance()->load->model('leads_model');

        return get_instance()->leads_model->add($lead_data);
    }

    return false;
}, 10, 2);

// add new created contact in campaign that is select all contacts
hooks()->add_action('contact_created', function ($id) {
    $campaigns = get_instance()->db->get_where(db_prefix() . 'wtc_campaigns', ['select_all' => '1', 'rel_type' => 'contacts'])->result_array();
    foreach ($campaigns as $campaign) {
        if (0 == $campaign['is_sent']) {
            $template = wb_get_whatsapp_template($campaign['template_id']);
            get_instance()->db->insert(db_prefix() . 'wtc_campaign_data', [
                'campaign_id' => $campaign['id'],
                'rel_id' => $id,
                'rel_type' => 'contacts',
                'header_message' => $template['header_data_text'],
                'body_message' => $template['body_data'],
                'footer_message' => $template['footer_data'],
                'status' => 1,
            ]);
        }
    }
});

hooks()->add_action('after_cron_run', 'send_campaign');
function send_campaign() {
    $scheduledData = get_instance()->db
        ->select(db_prefix() . 'wtc_campaigns.*, ' . db_prefix() . 'wtc_templates.*, ' . db_prefix() . 'wtc_campaign_data.*, ' . db_prefix() . 'wtc_campaign_data.id as campaign_data_id')
        ->join(db_prefix() . 'wtc_campaigns', db_prefix() . 'wtc_campaigns.id = ' . db_prefix() . 'wtc_campaign_data.campaign_id', 'left')
        ->join(db_prefix() . 'wtc_templates', db_prefix() . 'wtc_campaigns.template_id = ' . db_prefix() . 'wtc_templates.id', 'left')
        ->where(db_prefix() . 'wtc_campaigns.scheduled_send_time <= NOW()')
        ->where(db_prefix() . 'wtc_campaigns.pause_campaign', 0)
        ->where(db_prefix() . 'wtc_campaign_data.status', 1)
        ->where(db_prefix() . 'wtc_campaigns.is_bot', 0)
        ->get(db_prefix() . 'wtc_campaign_data')->result_array();

    if (!empty($scheduledData)) {
        get_instance()->load->model(WHATSBOT_MODULE . '/whatsbot_model');
        get_instance()->whatsbot_model->send_campaign($scheduledData);
    }

    $directory = FCPATH . 'uploads/whatsbot/csv';
    if (is_dir($directory)) {
        $files = get_filenames($directory);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'html') {
                $filePath = $directory . '/' . $file;
                @unlink($filePath);
            }
        }
    }
}

// add widgets
hooks()->add_filter('get_dashboard_widgets', function ($widgets) {
    $new_widgets = [];
    $new_widgets[] = [
        'path' => WHATSBOT_MODULE . '/widgets/whatsapp-widget',
        'container' => 'top-12',
    ];

    return array_merge($new_widgets, $widgets);
});

if (!is_dir(WHATSBOT_MODULE_UPLOAD_FOLDER)) {
    if (!mkdir(WHATSBOT_MODULE_UPLOAD_FOLDER, 0755, true)) {
        exit('Failed to create directory: ' . WHATSBOT_MODULE_UPLOAD_FOLDER);
    }
    $fp = fopen(WHATSBOT_MODULE_UPLOAD_FOLDER . '/index.html', 'w');
    fclose($fp);
}

hooks()->add_filter('get_upload_path_by_type', 'add_whatsbot_files_upload_path', 0, 2);
function add_whatsbot_files_upload_path($path, $type) {
    switch ($type) {
        case 'bot_files':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/bot_files/';
            break;
        case 'campaign':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/campaign/';
            break;
        case 'template':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/template/';
            break;
        case 'csv':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/csv/';
            break;
        case 'flow':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/flow/';
            break;
        case 'personal_assistant':
            $path = WHATSBOT_MODULE_UPLOAD_FOLDER . '/personal_assistant/';
            break;
        default:
            $path = $path;
            break;
    }
    return $path;
}

hooks()->add_action('before_cron_run', function ($manually) {
    if (get_option('enable_clear_chat_history') == '1') {
        $days = get_option('wb_auto_clear_time');
        $time_string = "-$days days";
        $date = date('Y-m-d H:i:s', strtotime($time_string));
        $data = get_instance()->db->get_where(db_prefix() . 'wtc_interaction_messages', ['time_sent < ' => $date])->result_array();
        if ($data) {
            get_instance()->db->delete(db_prefix() . 'wtc_interaction_messages', ['time_sent < ' => $date]);
        }
    }
});

hooks()->add_action('after_ticket_status_changed', function ($data) {
    get_instance()->load->model(['whatsbot/bots_model', 'whatsbot/whatsbot_model']);

    $ticket = get_instance()->tickets_model->get($data['id']);
    $client = get_instance()->clients_model->get($ticket->userid);
    $contact = get_instance()->clients_model->get_contact($ticket->contactid);
    $ticket->relation_type = 'ticket';
    $ticket->relation_id = $data['id'];

    get_instance()->whatsbot_model->send_flow("after_ticket_status_changed", $data, $client, $contact, $ticket);
});

hooks()->add_action('project_status_changed', function ($data) {
    get_instance()->load->model(['whatsbot/bots_model', 'whatsbot/whatsbot_model']);

    $project = get_instance()->projects_model->get($data['project_id']);
    $client = get_instance()->clients_model->get($project->clientid);
    $contact = get_instance()->clients_model->get_contact(get_primary_contact_user_id($project->clientid));
    $project->relation_type = 'project';
    $project->relation_id = $data['project_id'];

    get_instance()->whatsbot_model->send_flow("project_status_changed", $data, $client, $contact, $project);
});

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */
