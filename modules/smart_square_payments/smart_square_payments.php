<?php
/**
 * Module Name: Smart Choice Square Payments
 * Description: Square invoice payment gateway for Perfex CRM 3.4.1.
 * Version: 1.2.2
 * Requires at least: 3.4.1
 * Author: Smart Choice Contractors USA
 * Creator: Smart Choice Development
 * Company: Smart Choice Contractors USA
 */

defined('BASEPATH') or exit('No direct script access allowed');

define('SMART_SQUARE_MODULE', 'smart_square_payments');

register_language_files(SMART_SQUARE_MODULE, [SMART_SQUARE_MODULE]);
register_payment_gateway('smart_square_gateway', SMART_SQUARE_MODULE);
register_activation_hook(SMART_SQUARE_MODULE, 'smart_square_activate');
register_deactivation_hook(SMART_SQUARE_MODULE, 'smart_square_deactivate');
hooks()->add_action('admin_init', 'smart_square_permissions');
hooks()->add_action('before_render_payment_gateway_settings', 'smart_square_credential_status');

function smart_square_activate()
{
    $CI = &get_instance();
    $table = db_prefix() . 'smart_square_transactions';

    if (!$CI->db->table_exists($table)) {
        $CI->db->query('CREATE TABLE `' . $table . '` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `invoice_id` INT NOT NULL,
            `payment_id` VARCHAR(100) NOT NULL,
            `idempotency_key` VARCHAR(80) NOT NULL,
            `amount` DECIMAL(15,2) NOT NULL,
            `currency` VARCHAR(3) NOT NULL,
            `status` VARCHAR(30) NOT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `payment_id` (`payment_id`),
            UNIQUE KEY `idempotency_key` (`idempotency_key`),
            KEY `invoice_id` (`invoice_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }
}

function smart_square_permissions()
{
    register_staff_capabilities(SMART_SQUARE_MODULE, [
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'     => _l('permission_view'),
            'create'   => _l('permission_create'),
            'edit'     => _l('permission_edit'),
            'delete'   => _l('permission_delete'),
            'settings' => _l('smart_square_permission_settings'),
        ],
    ], _l('smart_square_payments'));
}

function smart_square_deactivate()
{
    // Financial records and configuration are intentionally preserved.
}

/**
 * Show credential status without exposing saved secrets in the settings HTML.
 */
function smart_square_credential_status($gateway)
{
    if (!isset($gateway['id']) || $gateway['id'] !== 'smart_square') {
        return;
    }

    $CI = &get_instance();
    foreach ([
        'access_token'          => _l('smart_square_access_token'),
        'webhook_signature_key' => _l('smart_square_webhook_signature_key'),
    ] as $key => $label) {
        $raw = get_option('paymentmethod_smart_square_' . $key);
        $value = $raw ? $CI->encryption->decrypt($raw) : false;
        $status = is_string($value) && trim($value) !== ''
            ? _l('smart_square_configured')
            : _l('smart_square_not_configured');

        echo '<p class="text-muted">' . html_escape($label) . ': <strong>' . html_escape($status) . '</strong></p>';
    }
}
