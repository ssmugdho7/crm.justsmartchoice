<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$CI->load->dbforge();

$actions = db_prefix() . 'stripe_hub_actions';
if (!$CI->db->table_exists($actions)) {
    $CI->db->query("CREATE TABLE `{$actions}` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `staff_id` INT NOT NULL DEFAULT 0,
      `action_type` VARCHAR(50) NOT NULL,
      `stripe_object_id` VARCHAR(191) NULL,
      `invoice_id` INT NULL,
      `amount` DECIMAL(15,2) NULL,
      `currency` VARCHAR(10) NULL,
      `status` VARCHAR(50) NULL,
      `description` TEXT NULL,
      `metadata` LONGTEXT NULL,
      `datecreated` DATETIME NOT NULL,
      PRIMARY KEY (`id`),
      KEY `staff_id` (`staff_id`),
      KEY `stripe_object_id` (`stripe_object_id`),
      KEY `invoice_id` (`invoice_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

$events = db_prefix() . 'stripe_hub_events';
if (!$CI->db->table_exists($events)) {
    $CI->db->query("CREATE TABLE `{$events}` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `stripe_event_id` VARCHAR(191) NULL,
      `event_type` VARCHAR(191) NOT NULL,
      `livemode` TINYINT(1) NOT NULL DEFAULT 0,
      `payload` LONGTEXT NULL,
      `processed` TINYINT(1) NOT NULL DEFAULT 0,
      `error_message` TEXT NULL,
      `datecreated` DATETIME NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `stripe_event_id` (`stripe_event_id`),
      KEY `event_type` (`event_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

$options = [
    'stripe_hub_version' => STRIPE_HUB_VERSION,
    'stripe_hub_use_core_gateway' => '1',
    'stripe_hub_secret_key' => '',
    'stripe_hub_publishable_key' => '',
    'stripe_hub_webhook_secret' => '',
    'stripe_hub_default_currency' => 'USD',
    'stripe_hub_items_per_page' => '25',
];
foreach ($options as $name => $value) {
    if (get_option($name) === false) {
        add_option($name, $value);
    }
}
