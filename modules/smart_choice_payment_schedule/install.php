<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();

$schedules = db_prefix() . 'sc_payment_schedules';
if (!$CI->db->table_exists($schedules)) {
    $CI->db->query("CREATE TABLE `{$schedules}` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `document_type` VARCHAR(32) NOT NULL,
        `document_id` INT UNSIGNED NOT NULL,
        `deposit_percent` DECIMAL(7,2) NOT NULL DEFAULT 0.00,
        `payment_stage` VARCHAR(32) NOT NULL DEFAULT 'full',
        `discount_reason` VARCHAR(191) NULL,
        `auto_create_balance` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `document_unique` (`document_type`,`document_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

$installments = db_prefix() . 'sc_payment_installments';
if (!$CI->db->table_exists($installments)) {
    $CI->db->query("CREATE TABLE `{$installments}` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `source_type` VARCHAR(32) NOT NULL,
        `source_id` INT UNSIGNED NOT NULL,
        `master_invoice_id` INT UNSIGNED NOT NULL DEFAULT 0,
        `deposit_invoice_id` INT UNSIGNED NOT NULL DEFAULT 0,
        `balance_invoice_id` INT UNSIGNED NOT NULL DEFAULT 0,
        `contract_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `deposit_percent` DECIMAL(7,2) NOT NULL DEFAULT 0.00,
        `deposit_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `balance_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `created_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `source_unique` (`source_type`,`source_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

if (function_exists('add_option')) {
    add_option('smart_choice_payment_schedule_version', '1.0.0');
}
