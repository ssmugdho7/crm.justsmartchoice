<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$table = db_prefix() . 'sc_sales_meta';

if (!$CI->db->table_exists($table)) {
    $CI->db->query('CREATE TABLE `' . $table . '` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `rel_type` VARCHAR(30) NOT NULL,
        `rel_id` INT UNSIGNED NOT NULL,
        `down_payment_percent` DECIMAL(7,2) NOT NULL DEFAULT 0,
        `discount_category` VARCHAR(191) NULL,
        `discount_reason` VARCHAR(255) NULL,
        `payment_link` VARCHAR(500) NULL,
        `payment_stage` VARCHAR(30) NOT NULL DEFAULT "deposit",
        `installment_label` VARCHAR(191) NULL,
        `contract_total` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `amount_due_now` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `remaining_balance` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `parent_invoice_id` INT UNSIGNED NULL,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `rel_unique` (`rel_type`,`rel_id`),
        KEY `parent_invoice_id` (`parent_invoice_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
} else {
    $fields = [
        'discount_reason'   => 'VARCHAR(255) NULL AFTER `discount_category`',
        'payment_stage'     => 'VARCHAR(30) NOT NULL DEFAULT "deposit" AFTER `payment_link`',
        'installment_label' => 'VARCHAR(191) NULL AFTER `payment_stage`',
        'contract_total'    => 'DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `installment_label`',
        'amount_due_now'    => 'DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `contract_total`',
        'remaining_balance' => 'DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `amount_due_now`',
        'parent_invoice_id' => 'INT UNSIGNED NULL AFTER `remaining_balance`',
        'updated_at'        => 'DATETIME NULL AFTER `created_at`',
    ];
    foreach ($fields as $name => $definition) {
        if (!$CI->db->field_exists($name, $table)) {
            $CI->db->query('ALTER TABLE `' . $table . '` ADD `' . $name . '` ' . $definition);
        }
    }
}

// Smart Choice v1.3.0 defaults.
update_option('clients_default_theme', 'smartchoice');
if ($CI->db->table_exists(db_prefix() . 'modules')) {
    foreach (['smart_choice_core_tools', 'notes'] as $moduleName) {
        $row = $CI->db->where('module_name', $moduleName)->get(db_prefix() . 'modules')->row();
        if ($row) {
            $CI->db->where('module_name', $moduleName)->update(db_prefix() . 'modules', ['active' => 1]);
        }
    }
}
