<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'purchasing_hub_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'purchasing_hub_items` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `item_code` VARCHAR(100) NULL,
        `item_name` VARCHAR(255) NOT NULL,
        `sku` VARCHAR(100) NULL,
        `barcode` VARCHAR(100) NULL,
        `description` TEXT NULL,
        `category` VARCHAR(150) NULL,
        `sub_category` VARCHAR(150) NULL,
        `unit_name` VARCHAR(100) NULL,
        `purchase_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `sales_rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `tax_1` VARCHAR(100) NULL,
        `tax_2` VARCHAR(100) NULL,
        `image` VARCHAR(255) NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_by` INT(11) NULL,
        `datecreated` DATETIME NOT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `item_name` (`item_name`),
        KEY `category` (`category`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'purchasing_hub_vendors')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'purchasing_hub_vendors` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `vendor_name` VARCHAR(255) NOT NULL,
        `contact_name` VARCHAR(255) NULL,
        `email` VARCHAR(150) NULL,
        `phone` VARCHAR(80) NULL,
        `trade` VARCHAR(150) NULL,
        `status` VARCHAR(80) NOT NULL DEFAULT "Active",
        `insurance_status` VARCHAR(80) NOT NULL DEFAULT "Not Verified",
        `address` TEXT NULL,
        `notes` TEXT NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_by` INT(11) NULL,
        `datecreated` DATETIME NOT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vendor_name` (`vendor_name`),
        KEY `trade` (`trade`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'purchasing_hub_orders')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'purchasing_hub_orders` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `order_number` VARCHAR(100) NULL,
        `vendor_id` INT(11) NULL,
        `project_name` VARCHAR(255) NULL,
        `order_date` DATE NULL,
        `expected_date` DATE NULL,
        `status` VARCHAR(80) NOT NULL DEFAULT "Draft",
        `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `tax_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `datecreated` DATETIME NOT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vendor_id` (`vendor_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'purchasing_hub_bills')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'purchasing_hub_bills` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `bill_number` VARCHAR(100) NULL,
        `vendor_id` INT(11) NULL,
        `project_name` VARCHAR(255) NULL,
        `bill_date` DATE NULL,
        `due_date` DATE NULL,
        `status` VARCHAR(80) NOT NULL DEFAULT "Open",
        `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `amount_paid` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `datecreated` DATETIME NOT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vendor_id` (`vendor_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'purchasing_hub_quotes')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'purchasing_hub_quotes` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `quote_number` VARCHAR(100) NULL,
        `vendor_id` INT(11) NULL,
        `project_name` VARCHAR(255) NULL,
        `quote_date` DATE NULL,
        `status` VARCHAR(80) NOT NULL DEFAULT "Received",
        `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `datecreated` DATETIME NOT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vendor_id` (`vendor_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'purchasing_hub_contracts')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'purchasing_hub_contracts` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `subject` VARCHAR(255) NOT NULL,
        `vendor_id` INT(11) NULL,
        `project_name` VARCHAR(255) NULL,
        `contract_type` VARCHAR(150) NULL,
        `status` VARCHAR(80) NOT NULL DEFAULT "Draft",
        `contract_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `datecreated` DATETIME NOT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vendor_id` (`vendor_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}


if (!$CI->db->table_exists(db_prefix() . 'purchasing_hub_custom_reports')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'purchasing_hub_custom_reports` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `report_name` VARCHAR(255) NOT NULL,
        `report_type` VARCHAR(100) NOT NULL,
        `description` TEXT NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_by` INT(11) NULL,
        `datecreated` DATETIME NOT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `report_type` (`report_type`),
        KEY `is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

$settings = [
    'purchasing_hub_default_tax_rate' => '0',
    'purchasing_hub_default_terms' => '',
    'purchasing_hub_allow_item_images' => '1',
];

foreach ($settings as $name => $value) {
    if (get_option($name) === '') {
        add_option($name, $value);
    }
}


// Smart Choice v2.1.3 safety columns for document editor, sending, shipping, and audit trail.
$smart_choice_column_map = [
    'purchasing_hub_orders' => [
        'shipping_address' => 'TEXT NULL',
        'body' => 'LONGTEXT NULL',
        'sent_to' => 'VARCHAR(191) NULL',
        'sent_at' => 'DATETIME NULL',
    ],
    'purchasing_hub_bills' => [
        'sent_to' => 'VARCHAR(191) NULL',
        'sent_at' => 'DATETIME NULL',
    ],
    'purchasing_hub_quotes' => [
        'sent_to' => 'VARCHAR(191) NULL',
        'sent_at' => 'DATETIME NULL',
    ],
    'purchasing_hub_contracts' => [
        'body' => 'LONGTEXT NULL',
        'sent_to' => 'VARCHAR(191) NULL',
        'sent_at' => 'DATETIME NULL',
    ],
];

foreach ($smart_choice_column_map as $table => $columns) {
    $full_table = db_prefix() . $table;
    if (!$CI->db->table_exists($full_table)) { continue; }
    foreach ($columns as $column => $definition) {
        if (!$CI->db->field_exists($column, $full_table)) {
            $CI->db->query('ALTER TABLE `' . $full_table . '` ADD `' . $column . '` ' . $definition);
        }
    }
}

$attachment_root = module_dir_path('purchasing_hub') . 'uploads/attachments/';
if (!is_dir($attachment_root)) { @mkdir($attachment_root, 0755, true); }


// Smart Choice v2.1.4 rescue marker for safe Perfex upgrades.
if (function_exists('update_option')) {
    if (get_option('purchasing_hub_module_version') === '') {
        add_option('purchasing_hub_module_version', '2.1.5');
    } else {
        update_option('purchasing_hub_module_version', '2.1.5');
    }
}


// Smart Choice v2.1.5 import preview, purchase order tracking, AP payment status, filters, and CRM linking columns.
$smart_choice_215_column_map = [
    'purchasing_hub_orders' => [
        'project_id' => 'INT(11) NULL',
        'client_id' => 'INT(11) NULL',
        'invoice_id' => 'INT(11) NULL',
        'client_name' => 'VARCHAR(255) NULL',
        'invoice_number' => 'VARCHAR(100) NULL',
        'footer_note' => 'TEXT NULL',
        'receipt_requested' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'receipt_confirmed' => 'TINYINT(1) NOT NULL DEFAULT 0',
        'receipt_confirmed_at' => 'DATETIME NULL',
        'receipt_confirmed_by' => 'VARCHAR(191) NULL',
    ],
    'purchasing_hub_bills' => [
        'payment_status' => 'VARCHAR(50) NOT NULL DEFAULT "Pending"',
        'paid_at' => 'DATETIME NULL',
    ],
];
foreach ($smart_choice_215_column_map as $table => $columns) {
    $full_table = db_prefix() . $table;
    if (!$CI->db->table_exists($full_table)) { continue; }
    foreach ($columns as $column => $definition) {
        if (!$CI->db->field_exists($column, $full_table)) {
            $CI->db->query('ALTER TABLE `' . $full_table . '` ADD `' . $column . '` ' . $definition);
        }
    }
}

$import_preview_root = module_dir_path('purchasing_hub') . 'uploads/import_preview/';
if (!is_dir($import_preview_root)) { @mkdir($import_preview_root, 0755, true); }

if (function_exists('update_option')) {
    if (get_option('purchasing_hub_module_version') === '') {
        add_option('purchasing_hub_module_version', '2.1.5');
    } else {
        update_option('purchasing_hub_module_version', '2.1.5');
    }
}
