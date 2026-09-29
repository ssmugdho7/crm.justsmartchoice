<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$options = [
    'ideal_module_stripe_webhook_id'             => '',
    'ideal_module_stripe_webhook_signing_secret' => '',
    'ideal_module_version'                       => IDEAL_MODULE_VERSION,
    'paymentmethod_Ideal_gateway_use_crm_currency' => '1',
    'paymentmethod_Ideal_gateway_fallback_currency' => 'USD',
    'paymentmethod_Ideal_gateway_fee_enabled' => '0',
    'paymentmethod_Ideal_gateway_fee_percent' => '0',
    'paymentmethod_Ideal_gateway_fee_fixed' => '0',
    'paymentmethod_Ideal_gateway_fee_label' => 'Processing Fee',
];
foreach ($options as $name => $value) {
    if (get_option($name) === false) {
        add_option($name, $value);
    } else {
        update_option($name, $value);
    }
}

$table = db_prefix() . 'ideal_stripe_events';
if (!$CI->db->table_exists($table)) {
    $CI->db->query("CREATE TABLE `{$table}` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `stripe_event_id` VARCHAR(191) NOT NULL,
        `event_type` VARCHAR(191) NOT NULL,
        `object_id` VARCHAR(191) NULL,
        `status` VARCHAR(40) NOT NULL DEFAULT 'received',
        `payload` MEDIUMTEXT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `stripe_event_id` (`stripe_event_id`),
        KEY `event_type` (`event_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

$table = db_prefix() . 'ideal_subscription_links';
if (!$CI->db->table_exists($table)) {
    $CI->db->query("CREATE TABLE `{$table}` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(191) NOT NULL,
        `stripe_price_id` VARCHAR(191) NOT NULL,
        `customer_email` VARCHAR(191) NULL,
        `checkout_url` TEXT NULL,
        `stripe_session_id` VARCHAR(191) NULL,
        `created_by` INT UNSIGNED NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `stripe_price_id` (`stripe_price_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}
