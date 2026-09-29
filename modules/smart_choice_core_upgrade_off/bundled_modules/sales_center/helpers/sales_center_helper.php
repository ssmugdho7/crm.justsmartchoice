<?php

defined('BASEPATH') or exit('No direct script access allowed');

function sales_center_statuses()
{
    return [
        'active'   => _l('sales_center_status_active'),
        'inactive' => _l('sales_center_status_inactive'),
        'pending'  => _l('sales_center_status_pending'),
        'blocked'  => _l('sales_center_status_blocked'),
    ];
}

function sales_center_contract_statuses()
{
    return [
        'draft'     => _l('sales_center_contract_status_draft'),
        'sent'      => _l('sales_center_contract_status_sent'),
        'signed'    => _l('sales_center_contract_status_signed'),
        'completed' => _l('sales_center_contract_status_completed'),
        'cancelled' => _l('sales_center_contract_status_cancelled'),
        'expired'   => _l('sales_center_contract_status_expired'),
    ];
}

function sales_center_file_url($file)
{
    return base_url('uploads/sales_center/' . $file['rel_type'] . '/' . $file['rel_id'] . '/' . $file['file_name']);
}

function sales_center_badge($text, $color = '#169179')
{
    return '<span class="label" style="background:' . html_escape($color) . ';">' . html_escape($text) . '</span>';
}


function sales_center_clean_label($text)
{
    $text = str_replace(['sales_center_', '_'], ['', ' '], (string) $text);
    $text = trim(preg_replace('/\s+/', ' ', $text));
    return ucwords($text);
}

function sales_center_normalize_url($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $url)) {
        return $url;
    }
    return 'https://' . $url;
}

function sales_center_signature_img($signature, $alt = 'Signature')
{
    if (empty($signature)) {
        return '<span class="text-muted">Not Signed</span>';
    }
    if (strpos($signature, 'data:image') === 0) {
        return '<img src="' . html_escape($signature) . '" alt="' . html_escape($alt) . '" class="smartsource-signature-image">';
    }
    return '<img src="' . base_url(html_escape($signature)) . '" alt="' . html_escape($alt) . '" class="smartsource-signature-image">';
}


function sales_center_portal_register_url()
{
    return site_url('sales_center/sales_center_portal/register');
}

function sales_center_portal_profile_url($token)
{
    return site_url('sales_center/sales_center_portal/profile/' . $token);
}


function sales_center_portal_success_url($token)
{
    $token = trim((string) $token);
    if ($token === '') {
        return sales_center_portal_register_url() . '?saved=1';
    }
    return sales_center_portal_profile_url($token) . '?saved=1';
}

function sales_center_admin_submenu($active = '')
{
    $items = [
        'salespersons' => ['label' => 'Salespersons', 'url' => admin_url('sales_center')],
        'contracts' => ['label' => 'Salesperson Contracts', 'url' => admin_url('sales_center/contracts')],
        'templates' => ['label' => 'Salesperson Templates', 'url' => admin_url('sales_center/templates')],
        'portal' => ['label' => 'Salesperson Portal', 'url' => admin_url('sales_center/portal_links')],
        'documents' => ['label' => 'Sales Documents', 'url' => admin_url('sales_center/documents')],
        'settings' => ['label' => 'Settings', 'url' => admin_url('sales_center/settings')],
        'help' => ['label' => 'Help Guide', 'url' => admin_url('sales_center/help')],
    ];

    $html = '<div class="smartsource-module-nav">';
    foreach ($items as $key => $item) {
        $class = $active === $key ? 'active' : '';
        $html .= '<a class="' . $class . '" href="' . $item['url'] . '">' . $item['label'] . '</a>';
    }
    $html .= '</div>';
    return $html;
}

function sales_center_email_layout($subject, $message)
{
    $company = function_exists('get_option') && get_option('companyname') ? get_option('companyname') : 'Smart Choice Contractors USA';
    $logo = function_exists('get_option') && get_option('company_logo') ? base_url('uploads/company/' . get_option('company_logo')) : base_url('uploads/company/logo.png');
    $website = 'https://justsmartchoice.com';

    return '<div style="margin:0;padding:0;background:#f4f7f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">'
        . '<div style="max-width:720px;margin:0 auto;padding:24px;">'
        . '<div style="background:linear-gradient(135deg,#111827,#169179);padding:22px;border-radius:14px 14px 0 0;text-align:center;border-bottom:5px solid #ef8c25;">'
        . '<img src="' . html_escape($logo) . '" alt="' . html_escape($company) . '" style="max-height:72px;max-width:260px;background:#ffffff;border-radius:10px;padding:8px;">'
        . '<h2 style="color:#ffffff;margin:14px 0 0;font-size:22px;">' . html_escape($subject) . '</h2>'
        . '</div>'
        . '<div style="background:#ffffff;padding:28px;border:1px solid #e5e7eb;border-top:0;line-height:1.7;font-size:15px;">' . $message . '</div>'
        . '<div style="background:#111827;color:#ffffff;text-align:center;padding:18px;border-radius:0 0 14px 14px;font-size:13px;">'
        . '<strong>' . html_escape($company) . '</strong><br>'
        . '<a href="' . $website . '" style="color:#ef8c25;text-decoration:none;">' . $website . '</a><br>'
        . 'Smart Choice CRM Notification'
        . '</div></div></div>';
}

function sales_center_send_basic_email($to, $subject, $message)
{
    $CI = &get_instance();
    $to = trim((string) $to);
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $subject = trim((string) $subject);
    if ($subject === '') {
        $subject = 'SmartSource Salespersons Notification';
    }

    $message = sales_center_email_layout($subject, (string) $message);

    try {
        $CI->load->library('email');
        $CI->email->clear(true);

        if (method_exists($CI->email, 'set_mailtype')) {
            $CI->email->set_mailtype('html');
        }

        $from = function_exists('get_option') ? get_option('smtp_email') : '';
        if (empty($from) && function_exists('get_option')) {
            $from = get_option('company_email');
        }
        if (empty($from) || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $from = 'admin@justsmartchoice.com';
        }

        $company = function_exists('get_option') && get_option('companyname') ? get_option('companyname') : 'Smart Choice Contractors USA';
        $CI->email->from($from, $company);
        $CI->email->to($to);
        $CI->email->subject($subject);
        $CI->email->message($message);

        /*
         * Perfex App_Email queue can throw a TypeError in some PHP 8.5 environments
         * when PHPMailer recipient arrays are empty/null. Passing true skips queue storage
         * and sends through the configured CRM mailer directly.
         */
        return (bool) $CI->email->send(true);
    } catch (Throwable $e) {
        if (function_exists('log_activity')) {
            log_activity('SmartSource Email Error: ' . $e->getMessage());
        }
        return false;
    }
}

