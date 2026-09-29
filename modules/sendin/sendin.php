<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Sendinblue sms Module
Description: Send sms notification for activities using sendinblue.
Author: Techy4M
Author URI: https://codecanyon.net/user/techy4m/portfolio
Version: 2.1.3
Requires at least: 3.2.0
*/

/**
 * Module libraries path
 * e.q. modules/module_name/libraries
 * @param string $module module name
 * @param string $concat append additional string to the path
 * @return string
 */
require(__DIR__ . '/vendor/autoload.php');

define('SENDIN_MODULE_NAME', 'sendin');
define('SENDIN_SMS_TRIGGER_INVOICE_SEND_TO_CUSTOMER', 'invoice_send_to_customer');

hooks()->add_filter('sms_gateways', 'sendinblue_sms_gateways');
hooks()->add_filter('sms_triggers', 'sendin_triggers');
hooks()->add_filter('sms_gateway_available_triggers', 'sendin_triggers');
hooks()->add_action('invoice_sent', 'sendin_invoice_to_customer');

function sendinblue_sms_gateways($gateways)
{
    $gateways[] = 'sendin/sms_sendin';
    return $gateways;
}

function sendin_triggers(array $triggers): array
{
    $invoice_fields = [
        '{contact_firstname}',
        '{contact_lastname}',
        '{client_company}',
        '{client_vat_number}',
        '{client_id}',
        '{invoice_link}',
        '{invoice_number}',
        '{invoice_duedate}',
        '{invoice_date}',
        '{invoice_status}',
        '{invoice_subtotal}',
        '{invoice_total}',
    ];

    $triggers[SENDIN_SMS_TRIGGER_INVOICE_SEND_TO_CUSTOMER] = [
        'merge_fields' => $invoice_fields,
        'label' => 'Send Invoice to customer',
        'info' => 'Trigger when invoice is created/sent to customer contacts.',
    ];
    return $triggers;
}

function sendin_invoice_to_customer($id): void
{
    $CI = &get_instance();
    $CI->load->helper('sms_helper');

    $invoice = $CI->invoices_model->get($id);
    $where = ['active' => 1, 'invoice_emails' => 1];
    $contacts = $CI->clients_model->get_contacts($invoice->clientid, $where);

    foreach ($contacts as $contact) {
        $template = mail_template('invoice_overdue_notice', $invoice, $contact);
        $merge_fields = $template->get_merge_fields();
        if (is_sms_trigger_active(SENDIN_SMS_TRIGGER_INVOICE_SEND_TO_CUSTOMER)) {
            $CI->app_sms->trigger(SENDIN_SMS_TRIGGER_INVOICE_SEND_TO_CUSTOMER, $contact['phonenumber'], $merge_fields);
        }
    }
}


/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */
