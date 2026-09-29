<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_190 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Safe/idempotent customer shopping migration. This file exists so Perfex can complete module upgrade steps for v1.9.0.
        // It avoids foreign keys to prevent MySQL error 150 on hosts with mixed engines/collations.

        if (!$CI->db->table_exists(db_prefix() . 'product_categories')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'product_categories` (
                `p_category_id` INT NOT NULL AUTO_INCREMENT,
                `p_category_name` VARCHAR(100) NOT NULL,
                `p_category_description` TEXT NULL,
                PRIMARY KEY (`p_category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        if (!$CI->db->table_exists(db_prefix() . 'product_master')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'product_master` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `product_name` VARCHAR(200) NOT NULL,
                `product_description` TEXT NULL,
                `product_category_id` INT NOT NULL DEFAULT 0,
                `rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `taxes` VARCHAR(255) NULL,
                `quantity_number` INT NOT NULL DEFAULT 0,
                `is_digital` TINYINT(1) NOT NULL DEFAULT 0,
                `product_image` VARCHAR(200) NULL DEFAULT NULL,
                `recurring` INT NOT NULL DEFAULT 0,
                `recurring_type` VARCHAR(10) NULL,
                `custom_recurring` TINYINT(1) NOT NULL DEFAULT 0,
                `cycles` INT NOT NULL DEFAULT 0,
                `is_variation` TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `product_category_id` (`product_category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        } else {
            $fields = [
                'product_image' => 'ALTER TABLE `' . db_prefix() . 'product_master` ADD `product_image` VARCHAR(200) NULL DEFAULT NULL',
                'taxes' => 'ALTER TABLE `' . db_prefix() . 'product_master` ADD `taxes` VARCHAR(255) NULL AFTER `rate`',
                'is_digital' => 'ALTER TABLE `' . db_prefix() . 'product_master` ADD `is_digital` TINYINT(1) NOT NULL DEFAULT 0 AFTER `quantity_number`',
                'recurring' => 'ALTER TABLE `' . db_prefix() . 'product_master` ADD `recurring` INT NOT NULL DEFAULT 0 AFTER `product_image`',
                'recurring_type' => 'ALTER TABLE `' . db_prefix() . 'product_master` ADD `recurring_type` VARCHAR(10) NULL AFTER `recurring`',
                'custom_recurring' => 'ALTER TABLE `' . db_prefix() . 'product_master` ADD `custom_recurring` TINYINT(1) NOT NULL DEFAULT 0 AFTER `recurring_type`',
                'cycles' => 'ALTER TABLE `' . db_prefix() . 'product_master` ADD `cycles` INT NOT NULL DEFAULT 0 AFTER `custom_recurring`',
                'is_variation' => 'ALTER TABLE `' . db_prefix() . 'product_master` ADD `is_variation` TINYINT(1) NOT NULL DEFAULT 0',
            ];
            foreach ($fields as $field => $sql) {
                if (!$CI->db->field_exists($field, db_prefix() . 'product_master')) {
                    $CI->db->query($sql);
                }
            }
            if ($CI->db->field_exists('product_description', db_prefix() . 'product_master')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'product_master` MODIFY `product_description` TEXT NULL');
            }
        }

        if (!$CI->db->table_exists(db_prefix() . 'order_master')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'order_master` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `invoice_id` INT NOT NULL DEFAULT 0,
                `clientid` INT NOT NULL DEFAULT 0,
                `datecreated` DATETIME NULL,
                `order_date` DATE NULL,
                `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `status` INT NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                KEY `invoice_id` (`invoice_id`),
                KEY `clientid` (`clientid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        if (!$CI->db->table_exists(db_prefix() . 'order_items')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'order_items` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `order_id` INT NOT NULL DEFAULT 0,
                `product_id` INT NOT NULL DEFAULT 0,
                `product_variation_id` INT NULL,
                `qty` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                PRIMARY KEY (`id`),
                KEY `order_id` (`order_id`),
                KEY `product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        } elseif (!$CI->db->field_exists('product_variation_id', db_prefix() . 'order_items')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'order_items` ADD `product_variation_id` INT NULL AFTER `product_id`');
        }

        if ($CI->db->table_exists(db_prefix() . 'invoices')) {
            if (!$CI->db->field_exists('coupon_id', db_prefix() . 'invoices')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'invoices` ADD `coupon_id` INT NULL AFTER `currency`');
            }
            if (!$CI->db->field_exists('coupon_discount', db_prefix() . 'invoices')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'invoices` ADD `coupon_discount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `total_tax`');
            }
        }
    }

    public function down()
    {
        // No destructive rollback.
    }
}
