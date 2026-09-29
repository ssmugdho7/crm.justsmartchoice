<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

add_option('domain_manager_purchase_code', 'xxxxxx');
add_option('domain_manager_purchase_is_valid', 1);

// Check and create 'threedstudio' table
if (!$CI->db->table_exists(db_prefix() . 'domain_manager')) {


     $sql_query = "CREATE TABLE IF NOT EXISTS `" . db_prefix() . "domain_manager` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `domain_name` VARCHAR(255) NOT NULL,  -- Domain name
        `registrar` VARCHAR(255) DEFAULT NULL,
        `purchase_date` DATE DEFAULT NULL,
        `expiry_date` DATE DEFAULT NULL,
        `status` VARCHAR(255) NOT NULL DEFAULT 'active',
        `dns_hosting` VARCHAR(255) NOT NULL DEFAULT 'enabled',
        `registration_status` VARCHAR(255) NOT NULL DEFAULT 'active',
        `client_id` INT(11) DEFAULT NULL,  -- Links to clients table
        `project_id` INT(11) DEFAULT NULL, -- Links to projects table
        `created_by` INT(11) DEFAULT NULL, -- Links to projects table
        `description` TEXT DEFAULT NULL,  -- Additional notes
        `deleted` TINYINT(1) NOT NULL DEFAULT '0',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    );";
    $CI->db->query($sql_query);

}
