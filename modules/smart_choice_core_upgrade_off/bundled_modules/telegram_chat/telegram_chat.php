<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Telegram Chat
Description: Quality ★★★★★.  Smart Choice Telegram notification center for CRM alerts, searchable message logs, browser review, client portal activity alerts, and health checks.
Version: 2.0.1
Requires at least: 2.3.*
Author URI: https://justsmartchoice.com/webdeveloper.php
Author: Smart Choice Contractors USA / Harold Cabrera
*/
define('TELEGRAM_CHAT_MODULE_NAME', 'telegram_chat');
require(__DIR__ .'/vendor/autoload.php');

if (!function_exists('telegram_chat_value')) {
    function telegram_chat_value($data, $key, $default = '')
    {
        if (is_array($data)) {
            return isset($data[$key]) ? $data[$key] : $default;
        }
        if (is_object($data)) {
            return isset($data->{$key}) ? $data->{$key} : $default;
        }
        return $default;
    }
}

if (!function_exists('telegram_chat_html')) {
    function telegram_chat_html($value)
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}


register_activation_hook(TELEGRAM_CHAT_MODULE_NAME, 'telegram_chat_module_activation_hook');

hooks()->add_action('admin_init', 'telegram_module_init_menu_items');



function telegram_chat_module_activation_hook()

{

    $CI = &get_instance();

    require_once(__DIR__ . '/install.php');

}

function telegram_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
        'name'       => 'Telegram',
        'permission' => 'telegram_chat',
        'url'        => 'telegram_chat',
        'position'   => 79,
    ]);

    if (has_permission('telegram_chat', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'telegram_chat',
            'name'     => 'Telegram Settings',
            'href'     => admin_url('telegram_chat'),
            'position' => 36,
        ]);

        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'telegram_chat_messages',
            'name'     => 'Telegram Message Center',
            'href'     => admin_url('telegram_chat/messages'),
            'position' => 37,
        ]);

        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'telegram_chat_health',
            'name'     => 'Telegram Health Checker',
            'href'     => admin_url('telegram_chat/health'),
            'position' => 38,
        ]);

        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'telegram_chat_training',
            'name'     => 'Telegram Help / Training',
            'href'     => admin_url('telegram_chat/training'),
            'position' => 39,
        ]);
    }
}

hooks()->add_action('admin_init', 'telegram_chat_register_permissions');
function telegram_chat_register_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view'   => _l('permission_view') . ' (Telegram)',
            'create' => _l('permission_create') . ' (Telegram)',
            'delete' => _l('permission_delete') . ' (Telegram)',
        ],
    ];

    register_staff_capabilities('telegram_chat', $capabilities, 'Telegram Chat');
}

/* Contact webhooks : Start */
// Add new contact
hooks()->add_action('contact_created', 'tele_contact_added_hook');
function tele_contact_added_hook($contactID)
{
    $CI        = &get_instance();
    $tableData['ContactData'] = $CI->clients_model->get_contact($contactID);
    $tableData['ClientData'] = $CI->clients_model->get($tableData['ContactData']->userid);

    call_telegram_webhook($tableData, 'client', 'add', $tableData['ContactData']->userid, $contactID);
}

// Update contact
hooks()->add_action('contact_updated', 'tele_contact_updated_hook');
function tele_contact_updated_hook($contactID)
{
    $CI        = &get_instance();
    $tableData['ContactData'] = $CI->clients_model->get_contact($contactID);
    $tableData['ClientData'] = $CI->clients_model->get($tableData['ContactData']->userid);

    call_telegram_webhook($tableData, 'client', 'edit', $tableData['ContactData']->userid, $contactID);
}

// Delete contact
hooks()->add_action('before_delete_contact', 'tele_contact_deleted_hook');
function tele_contact_deleted_hook($contactID)
{
    $CI        = &get_instance();
    $tableData['ContactData'] = $CI->clients_model->get_contact($contactID);
    $tableData['ClientData'] = $CI->clients_model->get($tableData['ContactData']->userid);

    call_telegram_webhook($tableData, 'client', 'delete', $tableData['ContactData']->userid, $contactID);
}
/* Contact webhooks : End */

/* Lead webhooks : Start */
// Add new lead
hooks()->add_action('lead_created', 'tele_lead_added_hook');
function tele_lead_added_hook($leadID)
{
    $CI        = &get_instance();
    $tableData = $CI->leads_model->get($leadID);
    call_telegram_webhook($tableData, 'leads', 'add', $leadID);
}

// Lead status changed
hooks()->add_action('lead_status_changed', 'tele_lead_status_changed_hook');
function tele_lead_status_changed_hook($lead)
{
    $CI        = &get_instance();
    $tableData = $CI->leads_model->get($lead['lead_id']);
    call_telegram_webhook($tableData, 'leads', 'status_change', $lead['lead_id']);
}

// Delete lead
hooks()->add_action('before_lead_deleted', 'tele_lead_deleted_hook');
function tele_lead_deleted_hook($leadID)
{
    $CI        = &get_instance();
    $tableData = $CI->leads_model->get($leadID);
    call_telegram_webhook($tableData, 'leads', 'delete', $leadID);
}
/* Lead webhooks : End */


/* Invoice webhooks : Start */
// Add new invoice
hooks()->add_action('after_invoice_added', 'tele_invoice_added_hook');
function tele_invoice_added_hook($invoiceID)
{
    $CI        = &get_instance();
    $tableData = $CI->invoices_model->get($invoiceID);
    call_telegram_webhook($tableData, 'invoice', 'add', $invoiceID);
}

// Update invoice
hooks()->add_action('invoice_updated', 'tele_invoice_updated_hook');
function tele_invoice_updated_hook($invoice)
{
    $CI        = &get_instance();
    $tableData = $CI->invoices_model->get($invoice['id']);
    call_telegram_webhook($tableData, 'invoice', 'edit', $invoice['id']);
}

// Delete invoice
hooks()->add_action('before_invoice_deleted', 'tele_invoice_deleted_hook');
function tele_invoice_deleted_hook($invoiceID)
{
    $CI        = &get_instance();
    $tableData = $CI->invoices_model->get($invoiceID);
    call_telegram_webhook($tableData, 'invoice', 'delete', $invoiceID);
}
/* Invoice webhooks : End */

/* Task webhooks : Start */
// Add new task
hooks()->add_action('after_add_task', 'tele_task_added_hook');
function tele_task_added_hook($taskId)
{
    $CI        = &get_instance();
    $tableData = $CI->tasks_model->get($taskId);
    call_telegram_webhook($tableData, 'tasks', 'add', $taskId);
}

// Update task
hooks()->add_action('after_update_task', 'tele_task_updated_hook');
function tele_task_updated_hook($taskId)
{
    $CI        = &get_instance();
    $tableData = $CI->tasks_model->get($taskId);
    call_telegram_webhook($tableData, 'tasks', 'edit', $taskId);
}


// Add the hook for task status change
hooks()->add_action('task_status_changed', 'tele_task_status_changed_hook');

// Define the hook function
function tele_task_status_changed_hook($data)
{
    $CI = &get_instance();
    $task_id = $data['task_id'];
    $new_status = $data['status'];

    // Assuming the status ID for 'started' is 1
    // You need to replace this with the actual status ID for 'started' in your setup
    $started_status_id = 4;

    // Check if the task status is 'started'
    if ($new_status == $started_status_id) {
        $tableData = $CI->tasks_model->get($task_id);
        call_telegram_webhook($tableData, 'tasks', 'start', $task_id);
    }
}

// Delete task

/* Task webhooks : End */

/* Projects webhooks : Start */
// Add new project
hooks()->add_action('after_add_project', 'tele_project_added_hook');
function tele_project_added_hook($projectId)
{
    $CI        = &get_instance();
    $tableData = $CI->projects_model->get($projectId);
    call_telegram_webhook($tableData, 'projects', 'add', $projectId);
}

// Update project
hooks()->add_action('after_update_project', 'tele_project_updated_hook');
function tele_project_updated_hook($projectId)
{
    $CI        = &get_instance();
    $tableData = $CI->projects_model->get($projectId);
    call_telegram_webhook($tableData, 'projects', 'edit', $projectId);
}

// Delete project
hooks()->add_action('before_project_deleted', 'tele_project_deleted_hook');
function tele_project_deleted_hook($projectId)
{
    $CI        = &get_instance();
    $tableData = $CI->projects_model->get($projectId);
    call_telegram_webhook($tableData, 'projects', 'delete', $projectId);
}
/* Projects webhooks : End */

/* Proposal webhooks : Start */
// Add new proposal
hooks()->add_action('proposal_created', 'tele_proposal_added_hook');
function tele_proposal_added_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_telegram_webhook($tableData, 'proposals', 'add', $proposalId);
}

// Update proposal
hooks()->add_action('after_proposal_updated', 'tele_proposal_updated_hook');
function tele_proposal_updated_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_telegram_webhook($tableData, 'proposals', 'edit', $proposalId);
}

// Delete proposal
hooks()->add_action('before_proposal_deleted', 'tele_proposal_deleted_hook');
function tele_proposal_deleted_hook($proposalId)
{
    $CI        = &get_instance();
    $tableData = $CI->proposals_model->get($proposalId);
    call_telegram_webhook($tableData, 'proposals', 'delete', $proposalId);
}
/* Proposal webhooks : End */

/* Ticket webhooks : Start */
// Add new ticket
hooks()->add_action('ticket_created', 'tele_ticket_added_hook');
function tele_ticket_added_hook($ticketId)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticketId);
    call_telegram_webhook($tableData, 'ticket', 'add', $ticketId);
}

// Update ticket
hooks()->add_action('ticket_settings_updated', 'tele_ticket_updated_hook');
function tele_ticket_updated_hook($ticket)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticket['ticket_id']);
    call_telegram_webhook($tableData, 'ticket', 'edit', $ticket['ticket_id']);
}

hooks()->add_action('after_ticket_status_changed', 'tele_ticket_status_changed_hook');
// Ticket status changed
function tele_ticket_status_changed_hook($ticket)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticket['id']);
    call_telegram_webhook($tableData, 'ticket', 'status_change', $ticket['id']);
}

// Delete ticket
hooks()->add_action('before_ticket_deleted', 'tele_ticket_deleted_hook');
function tele_ticket_deleted_hook($ticketID)
{
    $CI        = &get_instance();
    $tableData = $CI->tickets_model->get($ticketID);
    call_telegram_webhook($tableData, 'ticket', 'delete', $ticketID);
}
/* Ticket webhooks : End */

/* Payment webhooks : Start */
// Add new payment
hooks()->add_action('after_payment_added', 'tele_payment_added_hook');
function tele_payment_added_hook($paymentId)
{
    $CI        = &get_instance();
    $tableData = $CI->payments_model->get($paymentId);
    call_telegram_webhook($tableData, 'payment', 'add', $tableData->invoiceid, $paymentId);
}



/* Payment webhooks : End */

/* Staff webhooks : Start */
// Add new staff
hooks()->add_action('staff_member_created', 'tele_staff_added_hook');
function tele_staff_added_hook($staffid)
{
    $CI        = &get_instance();
    $tableData = $CI->staff_model->get($staffid);
    call_telegram_webhook($tableData, 'staff', 'add', $staffid);
}

// Update staff
hooks()->add_action('staff_member_updated', 'tele_staff_updated_hook');
function tele_staff_updated_hook($staffid)
{
    $CI        = &get_instance();
    $tableData = $CI->staff_model->get($staffid);
    call_telegram_webhook($tableData, 'staff', 'edit', $staffid);
}

// Delete staff
hooks()->add_action('before_delete_staff_member', 'tele_staff_deleted_hook');
function tele_staff_deleted_hook($staff)
{
    $CI        = &get_instance();
    $tableData = $CI->staff_model->get($staff['id']);
    call_telegram_webhook($tableData, 'staff', 'delete', $staff['id']);
}
/* Staff webhooks : End */

/* Contracts webhooks : Start */
// Add new contract
hooks()->add_action('after_contract_added', 'tele_contract_added_hook');
function tele_contract_added_hook($contractID)
{
    $CI        = &get_instance();
    $tableData = $CI->contracts_model->get($contractID);
    call_telegram_webhook($tableData, 'contract', 'add', $contractID);    
}

// Update contract
hooks()->add_action('after_contract_updated', 'tele_contract_updated_hook');
function tele_contract_updated_hook($contractID)
{
    $CI        = &get_instance();
    $tableData = $CI->contracts_model->get($contractID);
    call_telegram_webhook($tableData, 'contract', 'edit', $contractID);    
}

// Delete contract
hooks()->add_action('before_contract_deleted', 'tele_contract_deleted_hook');
function tele_contract_deleted_hook($contractID)
{
    $CI        = &get_instance();
    $tableData = $CI->contracts_model->get($contractID);
    call_telegram_webhook($tableData, 'contract', 'delete', $contractID);    
}
/* Contracts webhooks : End */



/* Client portal and extended notifications : Start */
hooks()->add_action('after_client_login', 'tele_client_login_hook');
hooks()->add_action('client_logged_in', 'tele_client_login_hook');
hooks()->add_action('after_contact_login', 'tele_client_login_hook');

function tele_client_login_hook($contact = null)
{
    if (get_option('telegram_chat_enable_client_login_notice') !== '1') {
        return;
    }

    $CI = &get_instance();
    $contactId = 0;
    if (is_array($contact) && isset($contact['contact_id'])) {
        $contactId = (int) $contact['contact_id'];
    } elseif (is_object($contact) && isset($contact->id)) {
        $contactId = (int) $contact->id;
    } elseif (function_exists('get_contact_user_id')) {
        $contactId = (int) get_contact_user_id();
    }

    $data = (object) [
        'contact_id' => $contactId,
        'ip'         => $CI->input->ip_address(),
        'user_agent' => $CI->input->user_agent(),
    ];

    call_telegram_webhook($data, 'client_portal', 'client_login', $contactId);
}

hooks()->add_action('proposal_accepted', 'tele_proposal_client_accepted_hook');
hooks()->add_action('after_proposal_accepted', 'tele_proposal_client_accepted_hook');

function tele_proposal_client_accepted_hook($proposalId)
{
    if (get_option('telegram_chat_enable_proposal_notice') !== '1') {
        return;
    }

    $CI = &get_instance();
    if (isset($CI->proposals_model)) {
        $data = $CI->proposals_model->get($proposalId);
    } else {
        $CI->load->model('proposals_model');
        $data = $CI->proposals_model->get($proposalId);
    }

    call_telegram_webhook($data, 'client_portal', 'proposal_accepted', $proposalId);
}

hooks()->add_action('before_client_uploaded_file', 'tele_client_file_uploaded_hook');
hooks()->add_action('after_client_uploaded_file', 'tele_client_file_uploaded_hook');
hooks()->add_action('client_uploaded_file', 'tele_client_file_uploaded_hook');

function tele_client_file_uploaded_hook($data = null)
{
    if (get_option('telegram_chat_enable_file_notice') !== '1') {
        return;
    }

    call_telegram_webhook((object) ['payload' => $data], 'client_portal', 'file_uploaded', 0);
}

hooks()->add_action('ticket_created', 'tele_client_ticket_created_extra_hook');

function tele_client_ticket_created_extra_hook($ticketId)
{
    if (get_option('telegram_chat_enable_ticket_notice') !== '1') {
        return;
    }

    // Main ticket notification already exists. This extra hook only logs the portal intent in Message Center.
    $CI = &get_instance();
    $CI->load->model(TELEGRAM_CHAT_MODULE_NAME . '/telegram_model');
    $CI->telegram_model->log_message([
        'staff_id' => function_exists('get_staff_user_id') ? get_staff_user_id() : null,
        'message_direction' => 'system',
        'module' => 'client_portal',
        'action' => 'ticket_created_notice_enabled',
        'record_id' => $ticketId,
        'message_text' => 'Client portal ticket notification is enabled for ticket #' . $ticketId,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}
/* Client portal and extended notifications : End */


function call_telegram_webhook($data, $webhook_for, $action, $data_id, $related_id = "")
{

	
	$CI = &get_instance();
	$CI->load->model(TELEGRAM_CHAT_MODULE_NAME . '/telegram_model');
    $currentUserID = function_exists('get_staff_user_id') ? get_staff_user_id() : 0;
    if (!$currentUserID && isset($GLOBALS['current_user']->staffid)) {
        $currentUserID = $GLOBALS['current_user']->staffid;
    }

    if (!$currentUserID) {
        $userTelegram = $CI->telegram_model->get_admin_id();
        $currentUserID = ($userTelegram && isset($userTelegram->user_id)) ? $userTelegram->user_id : 0;
    }

    $userTelegramInfo = $CI->telegram_model->get($currentUserID);
    if (!$userTelegramInfo) {
        $userTelegramInfo = $CI->telegram_model->get_admin_id();
    }

    if (!$userTelegramInfo || empty($userTelegramInfo->bot_token) || empty($userTelegramInfo->chat_id)) {
        return false;
    }

    $token = $userTelegramInfo->bot_token;
    $chat_id = $userTelegramInfo->chat_id;

    $base_url = base_url();
    $bot = new \TelegramBot\Api\BotApi($token);

    if ($webhook_for == 'system' && $action == 'test') {
        $messageText = '<b>Smart Choice Telegram Test</b>' . "\n" .
            '<i>Status:</i> Working' . "\n" .
            '<i>CRM:</i> ' . htmlspecialchars($base_url);
        return telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id);
    }

    if ($webhook_for == 'client_portal') {
        $label = 'Client Portal Activity';
        if ($action == 'client_login') {
            $label = 'Client logged in to the portal';
        } elseif ($action == 'proposal_accepted') {
            $label = 'Client accepted a proposal';
        } elseif ($action == 'file_uploaded') {
            $label = 'Client uploaded a file';
        } elseif ($action == 'portal_message') {
            $label = 'Client sent a portal message';
        }

        $messageText = '<b>CRM Message:</b>' . "\n" .
            '<i>Module:</i> Client Portal' . "\n" .
            '<i>Action:</i> ' . telegram_chat_html($action) . "\n" .
            '<u>Details</u>' . "\n" .
            '<b>' . htmlspecialchars($label) . '</b>' . "\n" .
            '<b>' . htmlspecialchars($base_url . 'admin') . '</b>';

        return telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id);
    }


		if($webhook_for == 'client' && $action=='add'){ 
        $company=$data['ClientData']->company; 
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			New Contact added for company <b>'.telegram_chat_html($company).'</b>
			';
			
		$messageText .= '<b>'.$base_url. 'clients/client/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		
		///////  Contacts ////////////
		
		if($webhook_for == 'client' && $action=='edit'){ 
        $company=$data['ClientData']->company; 
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			Contact updated for company <b>'.telegram_chat_html($company).'</b>
			';
			
		$messageText .= '<b>'.$base_url. 'clients/client/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		if($webhook_for == 'client' && $action=='delete'){ 
        $company=$data['ClientData']->company; 
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			Contact deleted for company <b>'.telegram_chat_html($company).'</b>
			';
			
		$messageText .= '<b>'.$base_url. 'clients/client/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		///////  Contacts Ends ////////////
		
		
			///////  Leads ////////////
		
		if($webhook_for == 'leads' && $action=='add'){ 
        
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			New Lead added
			<i>Status:</i> ' . telegram_chat_html(telegram_chat_value($data, 'status_name')).'
			<i>Source:</i> ' . telegram_chat_html(telegram_chat_value($data, 'source_name')).'
			';
			
		$messageText .= '<b>'.$base_url. 'admin/leads/index/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		if($webhook_for == 'leads' && $action=='status_change'){ 
        
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			Lead has been updated
			<i>Status:</i> ' . telegram_chat_html(telegram_chat_value($data, 'status_name')).'
			<i>Source:</i> ' . telegram_chat_html(telegram_chat_value($data, 'source_name')).'
			';
			
		$messageText .= '<b>'.$base_url. 'admin/leads/index/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		if($webhook_for == 'leads' && $action=='delete'){ 
        
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Lead has been deleted</b>
			<i>Status:</i> ' . telegram_chat_html(telegram_chat_value($data, 'status_name')).'
			<i>Source:</i> ' . telegram_chat_html(telegram_chat_value($data, 'source_name')).'
			';
			
		$messageText .= '<b>'.$base_url. 'admin/leads/index/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		///////  Leads ENDS ////////////
		
		
		///// INVOICES //////////////
		
		if($webhook_for == 'invoice' && $action=='add'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>New Invoice has been created</b>
			<i>Company:</i> ' . htmlspecialchars($data->client->company).'
			<i>Date:</i> ' . htmlspecialchars($data->date).'
			';
			
		$messageText .= '<b>'.$base_url. 'admin/invoices/invoice/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		if($webhook_for == 'invoice' && $action=='edit'){ 
        

		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Invoice has been updated</b>
			<i>Company:</i> ' . htmlspecialchars($data->client->company).'
			<i>Date:</i> ' . htmlspecialchars($data->date).'
			';
			
		$messageText .= '<b>'.$base_url. 'admin/invoices/invoice/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		if($webhook_for == 'invoice' && $action=='delete'){ 
        

		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Invoice has been deleted</b>
			<i>Company:</i> ' . htmlspecialchars($data->client->company).'
			<i>Date:</i> ' . htmlspecialchars($data->date).'
			';
			
		$messageText .= '<b>'.$base_url. 'admin/invoices/invoice/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		
		///// INVOICES END //////////////
		
		
	   ///// TASKS //////////////
		
		if($webhook_for == 'tasks' && $action=='add'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>New Task has been created</b>
			<i>Due Date:</i> ' . htmlspecialchars($data->duedate).'
			<i>Date:</i> ' . htmlspecialchars($data->dateadded).'
			
			';
			
		$messageText .= '<b>'.$base_url. 'admin/tasks/view/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		if($webhook_for == 'tasks' && $action=='edit'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Task has been updated</b>
			<i>Due Date:</i> ' . htmlspecialchars($data->duedate).'
			<i>Date:</i> ' . htmlspecialchars($data->dateadded).'
			
			';
			
		$messageText .= '<b>'.$base_url. 'admin/tasks/view/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		if($webhook_for == 'tasks' && $action=='start'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Task has moved to In Progress</b>
			';
			
		$messageText .= '<b>'.$base_url. 'admin/tasks/view/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		
		///// TASKS END//////////////
		
        ///// PAYMENTS //////////////
		
		if($webhook_for == 'payment' && $action=='add'){ 
        
		
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Invoice Payment has been done</b>
			<i>Invoice ID:</i> ' . htmlspecialchars($data->invoiceid).'
			
			';
			
		$messageText .= '<b>'.$base_url. 'admin/invoices/invoice/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
   
       ///// PAYMENTS END//////////////

       ///// Staff //////////////
	   
	   if($webhook_for == 'staff' && $action=='add'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>New staff member is added</b>
			';
			
		$messageText .= '<b>'.$base_url. 'admin/staff/member/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		
		if($webhook_for == 'staff' && $action=='edit'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Staff member account is updated</b>
			';
			
		$messageText .= '<b>'.$base_url. 'admin/staff/member/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}

        if($webhook_for == 'staff' && $action=='delete'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Staff member account is deleted</b>
			';
			
		$messageText .= '<b>'.$base_url. 'admin/staff/member/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
	   
	   
	   
	   ///// STAFF ENDS  //////////////

       
	   
	     ///// CONTRACTS //////////////
	   
	   if($webhook_for == 'contract' && $action=='add'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>New Contract is created</b>
			';
			
		$messageText .= '<b>'.$base_url. 'contract/' . telegram_chat_html($data_id).'/'.htmlspecialchars($data->hash).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		
		if($webhook_for == 'contract' && $action=='edit'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Contract is updated</b>
			';
			
		$messageText .= '<b>'.$base_url. 'contract/' . telegram_chat_html($data_id).'/'.htmlspecialchars($data->hash).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}

        if($webhook_for == 'contract' && $action=='delete'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Contract is deleted</b>
			';
			
		$messageText .= '<b>'.$base_url. 'contract/' . telegram_chat_html($data_id).'/'.htmlspecialchars($data->hash).'</b>';
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
	   
	   
	   
	   ///// CONTRACTS ENDS  //////////////
	   
	   
	   
	     ///// PROJECTS //////////////
	   
	   if($webhook_for == 'projects' && $action=='add'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>New Project is created</b>
			';
			
		$messageText .= '<b>'.$base_url. 'projects/view/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		
		if($webhook_for == 'projects' && $action=='edit'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Project details are updated</b>
			';
			
		$messageText .= '<b>'.$base_url. 'projects/view/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}

        if($webhook_for == 'projects' && $action=='delete'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Project is deleted</b>
			';
			
		$messageText .= '<b>'.$base_url. 'projects/view/' . telegram_chat_html($data_id).'</b>';
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
	   
	   
	   
	   ///// PROJECTS ENDS  //////////////
	   
	   
	   
	        ///// PROPOSAL //////////////
	   
	   if($webhook_for == 'proposals' && $action=='add'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>New Proposal is created</b>
			';
			
		$messageText .= '<b>'.$base_url. 'proposal/' . telegram_chat_html($data_id).'/'.htmlspecialchars($data->hash).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		
		if($webhook_for == 'proposals' && $action=='edit'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Proposal details are updated</b>
			';
			
		$messageText .= '<b>'.$base_url. 'proposal/' . telegram_chat_html($data_id).'/'.htmlspecialchars($data->hash).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}

        if($webhook_for == 'proposals' && $action=='delete'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Proposal is deleted</b>
			';
			
		$messageText .= '<b>'.$base_url. 'proposal/' . telegram_chat_html($data_id).'/'.htmlspecialchars($data->hash).'</b>';
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
	   
	   
	   
	   ///// PROPOSAL ENDS  //////////////
	   
	   
	   
	   
	     ///// TICKETS //////////////
	   
	   if($webhook_for == 'ticket' && $action=='add'){ 
        
		//echo '<pre>';
		//print_r($data);
		//exit;
		 $CI        = &get_instance();
		$user_id = $data->userid;
		$client = $CI->clients_model->get($user_id);
		
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Company</b> ' . htmlspecialchars($client->company).'
			<b>New Ticket is created</b>
			
			';
			
		$messageText .= '<b>'.$base_url. 'admin/tickets/ticket/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
		
		
		if($webhook_for == 'ticket' && $action=='edit'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Ticket details are updated</b>
			';
			
		$messageText .= '<b>'.$base_url. 'admin/tickets/ticket/' . telegram_chat_html($data_id).'</b>';
		
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}

        if($webhook_for == 'ticket' && $action=='delete'){ 
        
		/*echo '<pre>';
		print_r($data);
		exit;*/
		$messageText = '
			<b>CRM Message:</b>
			<i>Module:</i> ' . telegram_chat_html($webhook_for).'
			<i>Action:</i> ' . telegram_chat_html($action).'
			<u>Details</u>
			<b>Ticket is deleted</b>
			';
			
		$messageText .= '<b>'.$base_url. 'admin/tickets/ticket/' . telegram_chat_html($data_id).'</b>';
		telegram_chat_send_and_log($bot, $chat_id, $messageText, 'HTML', $webhook_for, $action, $data_id, $related_id); 
		}
	   
	   
	   
	   ///// TICKETS ENDS  //////////////
}


function telegram_chat_clean_message($message)
{
    $message = (string) $message;
    $message = preg_replace('/<br\s*\/?>/i', "\n", $message);
    $message = strip_tags($message);
    $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
    return trim($message);
}

function telegram_chat_send_and_log($bot, $chat_id, $messageText, $parseMode, $module = '', $action = '', $record_id = '', $related_id = '')
{
    $telegramMessageId = null;
    $rawResponse = null;

    try {
        $rawResponse = $bot->sendMessage($chat_id, $messageText, $parseMode);

        if (is_object($rawResponse) && method_exists($rawResponse, 'getMessageId')) {
            $telegramMessageId = $rawResponse->getMessageId();
        } elseif (is_array($rawResponse) && isset($rawResponse['message_id'])) {
            $telegramMessageId = $rawResponse['message_id'];
        }
    } catch (Exception $e) {
        $rawResponse = 'Telegram send error: ' . $e->getMessage();
    } catch (Throwable $e) {
        $rawResponse = 'Telegram send error: ' . $e->getMessage();
    }

    if (get_option('telegram_chat_enable_message_log') === '1') {
        $CI = &get_instance();
        $CI->load->model(TELEGRAM_CHAT_MODULE_NAME . '/telegram_model');

        $staffId = null;
        if (function_exists('get_staff_user_id')) {
            $staffId = get_staff_user_id();
        } elseif (isset($GLOBALS['current_user']->staffid)) {
            $staffId = $GLOBALS['current_user']->staffid;
        }

        $CI->telegram_model->log_message([
            'staff_id'             => $staffId,
            'chat_id'              => $chat_id,
            'message_direction'    => 'outgoing',
            'module'               => $module,
            'action'               => $action,
            'record_id'            => $record_id,
            'related_id'           => $related_id,
            'telegram_message_id'  => $telegramMessageId,
            'sender_name'          => 'CRM Notification',
            'message_text'         => telegram_chat_clean_message($messageText),
            'raw_payload'          => is_scalar($rawResponse) ? (string) $rawResponse : json_encode($rawResponse),
            'created_at'           => date('Y-m-d H:i:s'),
        ]);
    }

    return $rawResponse;
}
