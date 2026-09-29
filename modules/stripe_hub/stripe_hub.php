<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Stripe Hub
Description: Central Stripe administration hub for Smart Choice Contractors USA. Manage Stripe account status, balances, payments, customers, payment links, refunds, invoice Checkout sessions, webhook logging, permissions, and module settings from Perfex CRM.
Version: 1.5.0
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
Requires at least: 3.0.0
*/

define('STRIPE_HUB_MODULE_NAME', 'stripe_hub');
define('STRIPE_HUB_VERSION', '1.5.0');

hooks()->add_action('admin_init', 'stripe_hub_register_permissions');
hooks()->add_action('admin_init', 'stripe_hub_register_menu_items');
hooks()->add_action('app_admin_head', 'stripe_hub_admin_head');
hooks()->add_action('after_invoice_view_as_client_link', 'stripe_hub_invoice_more_menu');

register_activation_hook(STRIPE_HUB_MODULE_NAME, 'stripe_hub_activation_hook');
register_language_files(STRIPE_HUB_MODULE_NAME, ['stripe_hub']);

function stripe_hub_activation_hook()
{
    require_once __DIR__ . '/install.php';
}

function stripe_hub_register_permissions()
{
    register_staff_capabilities('stripe_hub', [
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'     => _l('permission_view') . ' (' . _l('permission_global') . ')',
            'create'   => _l('permission_create'),
            'edit'     => _l('permission_edit'),
            'delete'   => _l('permission_delete'),
        ],
    ], _l('stripe_hub'));
}

function stripe_hub_register_menu_items()
{
    $CI = &get_instance();
    if (!is_admin() && !staff_can('view', 'stripe_hub') && !staff_can('view_own', 'stripe_hub')) {
        return;
    }

    $CI->app_menu->add_sidebar_menu_item('stripe-hub', [
        'name'     => _l('stripe_hub'),
        'href'     => admin_url('stripe_hub'),
        'position' => 36,
        'icon'     => 'fa-brands fa-stripe-s',
    ]);

    $items = [
        ['slug' => 'stripe-hub-dashboard', 'name' => _l('stripe_hub_dashboard'), 'href' => admin_url('stripe_hub'), 'position' => 1, 'icon' => 'fa-solid fa-gauge-high'],
        ['slug' => 'stripe-hub-payments', 'name' => _l('stripe_hub_payments'), 'href' => admin_url('stripe_hub/payments'), 'position' => 2, 'icon' => 'fa-solid fa-credit-card'],
        ['slug' => 'stripe-hub-customers', 'name' => _l('stripe_hub_customers'), 'href' => admin_url('stripe_hub/customers'), 'position' => 3, 'icon' => 'fa-solid fa-users'],
        ['slug' => 'stripe-hub-links', 'name' => _l('stripe_hub_payment_links'), 'href' => admin_url('stripe_hub/links'), 'position' => 4, 'icon' => 'fa-solid fa-link'],
        ['slug' => 'stripe-hub-refunds', 'name' => _l('stripe_hub_refunds'), 'href' => admin_url('stripe_hub/refunds'), 'position' => 5, 'icon' => 'fa-solid fa-rotate-left'],
        ['slug' => 'stripe-hub-logs', 'name' => _l('stripe_hub_logs'), 'href' => admin_url('stripe_hub/logs'), 'position' => 6, 'icon' => 'fa-solid fa-list'],
    ];
    if (is_admin() || staff_can('edit', 'stripe_hub')) {
        $items[] = ['slug' => 'stripe-hub-settings', 'name' => _l('stripe_hub_settings'), 'href' => admin_url('stripe_hub/settings'), 'position' => 7, 'icon' => 'fa-solid fa-gear'];
    }
    foreach ($items as $item) {
        $CI->app_menu->add_sidebar_children_item('stripe-hub', $item);
    }
}


function stripe_hub_invoice_more_menu($invoice)
{
    if (!$invoice || (!is_admin() && !staff_can('create', 'stripe_hub'))) {
        return;
    }
    if (isset($invoice->total_left_to_pay) && (float)$invoice->total_left_to_pay <= 0) {
        return;
    }
    echo '<li><a href="' . admin_url('stripe_hub/invoice_checkout/' . (int)$invoice->id) . '" onclick="return confirm(\'' . e(_l('stripe_hub_invoice_checkout_confirm')) . '\');"><i class="fa-brands fa-stripe-s"></i> ' . e(_l('stripe_hub_create_invoice_checkout')) . '</a></li>';
}

function stripe_hub_admin_head()
{
    if (get_instance()->uri->segment(2) === 'stripe_hub') {
        echo '<link rel="stylesheet" href="' . module_dir_url(STRIPE_HUB_MODULE_NAME, 'assets/css/stripe_hub.css?v=' . STRIPE_HUB_VERSION) . '">';
    }
}
