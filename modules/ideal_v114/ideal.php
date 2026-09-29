<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Stripe Payments
Description: Stripe payments, iDEAL, cards, webhooks, payment health, and subscription checkout integration for Perfex CRM.
Version: 1.1.4
Requires at least: 3.4.*
Author: Smart Choice Contractors USA
Author URI: https://justsmartchoice.com/
*/

const IDEAL_MODULE_NAME       = 'ideal';
const IDEAL_MODULE_GATEWAY_ID = 'Ideal_gateway';
const IDEAL_MODULE_VERSION    = '1.1.4';

register_language_files(IDEAL_MODULE_NAME, [IDEAL_MODULE_NAME]);
register_payment_gateway(IDEAL_MODULE_GATEWAY_ID, IDEAL_MODULE_NAME);
register_activation_hook(IDEAL_MODULE_NAME, 'ideal_module_activation');
register_deactivation_hook(IDEAL_MODULE_NAME, 'ideal_module_deactivation');
register_uninstall_hook(IDEAL_MODULE_NAME, 'ideal_module_uninstall');

hooks()->add_action('admin_init', 'ideal_module_admin_init');
hooks()->add_action('before_render_payment_gateway_settings', 'ideal_module_webhook_check');
hooks()->add_action('before_update_system_options', 'ideal_prevent_invalid_activation');
hooks()->add_action('app_admin_head', 'ideal_admin_assets');

register_staff_capabilities('ideal', [
    'view_own' => _l('view_own'),
    'view'     => _l('view_global'),
    'create'   => _l('create'),
    'edit'     => _l('edit'),
    'delete'   => _l('delete'),
], _l('ideal_permission_group'));

function ideal_module_activation(): void
{
    require_once __DIR__ . '/install.php';
}

function ideal_module_deactivation(): void
{
    // Intentionally preserve settings and transaction history.
}

function ideal_module_uninstall(): void
{
    // Payment records and Stripe identifiers are preserved for accounting integrity.
    // Controlled cleanup can be performed from the module settings page.
}

function ideal_module_admin_init(): void
{
    $CI = &get_instance();

    if (is_admin() || staff_can('view', 'ideal') || staff_can('view_own', 'ideal')) {
        $CI->app_menu->add_setup_menu_item('smart-choice-stripe', [
            'name'     => _l('ideal_menu'),
            'href'     => admin_url('ideal_admin'),
            'position' => 71,
            'icon'     => 'fa-brands fa-stripe',
        ]);
    }
}

function ideal_admin_assets(): void
{
    $CI = &get_instance();
    $uri = $CI->uri->uri_string();
    if (str_contains($uri, 'admin/ideal_admin') || str_contains($uri, 'settings')) {
        echo '<link rel="stylesheet" href="' . module_dir_url(IDEAL_MODULE_NAME, 'assets/css/ideal_admin.css') . '?v=114">';
    }
}

function ideal_module_webhook_events(): array
{
    return [
        \Stripe\Event::TYPE_CHECKOUT_SESSION_COMPLETED,
        \Stripe\Event::TYPE_PAYMENT_INTENT_SUCCEEDED,
        \Stripe\Event::TYPE_PAYMENT_INTENT_PAYMENT_FAILED,
        'invoice.paid',
        'invoice.payment_failed',
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
    ];
}

function ideal_module_webhook_check(array $gateway): void
{
    if (($gateway['id'] ?? '') !== IDEAL_MODULE_GATEWAY_ID) {
        return;
    }

    /** @var Ideal_gateway $idealGateway */
    $idealGateway = $gateway['instance'];
    if (!$idealGateway->hasSecretKey() || (string) ($gateway['active'] ?? '0') !== '1') {
        return;
    }

    try {
        $webhook = $idealGateway->getCurrentWebhookObject();
        if (!$webhook) {
            echo '<div class="alert alert-warning">' . _l('ideal_webhook_missing') . '<br><b>'
                . html_escape($idealGateway->getWebhookEndPoint()) . '</b><br><a class="btn btn-default btn-sm mtop10" href="'
                . site_url('ideal/create_webhook') . '">' . _l('ideal_create_webhook') . '</a></div>';
        }
    } catch (Throwable $e) {
        echo '<div class="alert alert-warning">' . html_escape($e->getMessage()) . '</div>';
    }
}

function ideal_prevent_invalid_activation($systemOptions): void
{
    $settings = $systemOptions['settings'] ?? null;
    if (!is_array($settings) || !array_key_exists('paymentmethod_Ideal_gateway_active', $settings)) {
        return;
    }

    $active = (string) $settings['paymentmethod_Ideal_gateway_active'] === '1';
    $publishable = trim((string) ($settings['paymentmethod_Ideal_gateway_api_publishable_key'] ?? ''));
    $secret = trim((string) ($settings['paymentmethod_Ideal_gateway_api_secret_key'] ?? ''));

    if ($active && ($publishable === '' || $secret === '')) {
        $CI = &get_instance();
        $CI->load->library('ideal/Ideal_gateway', null, 'ideal_gateway');
        $CI->ideal_gateway->markAsInactive();
        set_alert('danger', _l('ideal_gateway_cannot_be_activated_keys_not_configured'));
        redirect(admin_url('settings?group=payment_gateways&tab=online_payments_Ideal_gateway_tab'));
    }
}
